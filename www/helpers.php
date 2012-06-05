<?php

ini_set("display_errors", 1);
error_reporting(E_ALL | E_STRICT);

$s3_root = "https://s3.amazonaws.com/zipio_photos";
$www_root = "http://localhost";
//$www_root = "http://" . $_SERVER["HTTP_HOST"];


function rand_string($length) {
    $chars = "abcdefghijklmnopqrstuvwxyz";
    return substr(str_shuffle($chars),0,$length);
}

function generate_usercode($length = 8, $include_numbers = false) {
    $vowels = array("a", "e", "i", "o", "u");
    $cons = array("b", "c", "d", "g", "h", "j", "k", "l", "m", "n", "p", "r",
                    "s", "t", "u", "v", "w", "tr", "cr", "br", "fr", "th", "dr",
                          "ch", "ph", "wr", "st", "sp", "sw", "pr", "sl", "cl");

    $num_vowels = count($vowels);
    $num_cons = count($cons);

    $pre = $post = $password = '';

    if ($include_numbers) {
        while ((($length/(strlen($pre) + strlen($post)+1)) > 2)) {
            if (rand(0,1) === 0) {
                $pre .= chr(rand(48,57));
            } else {
                $post .= chr(rand(48,57));
            }
        }
    }

    $string_length = $length - (strlen($pre) + strlen($post));

    while (strlen($password) < $string_length) {
        $password .= $cons[rand(0, $num_cons - 1)] . $vowels[rand(0, $num_vowels - 1)];
    }
    return $pre.substr($password, 0, $string_length ) . $post;
}



function debug($string, $color = "black") {
    print("<span style='color:$color;'>$string</span>" . "\n<br>");
}


function send_email($to, $from, $subject, $html) {

    if ($from == "") {
        $from = "Zipio <founders@zipio.com>";
    }

    $request = new HttpRequest('https://api.mailgun.net/v2/zipio.com/messages', HttpRequest::METH_POST);
    $auth = base64_encode('api:key-68imhgvpoa-6uw3cl8728kcs9brvlmr9');
    $request->setHeaders(array('Authorization' => 'Basic '.$auth));
    $request->setPostFields(array('from' => $from, 'to' => $to, 'subject' => $subject, 'html' => $html));
    $request->send();

    return $request;
}


function encrypt_json($arr) {

    $public_key = openssl_get_publickey(file_get_contents('/usr/local/zipio/public.key'));


    $json = json_encode($arr);
    $res = openssl_public_encrypt($json, $encrypted_text, $public_key);
    return base64_encode($encrypted_text);
}

function decrypt_json($encrypted_json) {

    $private_key = openssl_get_privatekey(file_get_contents('/usr/local/zipio/private.key'));

    $res = openssl_private_decrypt(base64_decode($encrypted_json), $decrypted_text, $private_key);
    return json_decode($decrypted_text, true);
}

function generate_token($id, $created) {
    return sha1($id . $created);
}

function generate_token_from_id($id, $table) {

    global $con;

    $query = "SELECT created FROM $table WHERE id='$id' LIMIT 1";
    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());

    if ($row = mysql_fetch_assoc($result)) {
        $created = $row["created"];
        $token = generate_token($id, $created);
    } else {
        return 0;
    }
    return $token;
}

function check_token($id, $token, $table) {

    $correct_token = generate_token_from_id($id, $table);

    if ($token == $correct_token) {
        return 1;
    }

    return 0;
}


function is_friend($user_id, $potential_friend_id) {

    global $con;

    $query = "SELECT * FROM Friends WHERE user_id='$user_id' AND friend_id=$potential_friend_id";
    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());

    if (mysql_num_rows($result) == 1) {
        return 1;
    }

    return 0;
}

function has_write_permission($user_id, $album_id) {

    global $con;

    $query = "SELECT * FROM Permissions WHERE user_id='$user_id' AND album_id=$album_id LIMIT 1";
    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());

    if (mysql_num_rows($result) == 1) {
        return 1;
    }

    return 0;
}

function create_album($user_id, $handle) {

    global $con;

    $query = "INSERT INTO Albums (
                  user_id,
                  handle
              ) VALUES (
                  '$user_id',
                  '$handle'
              ) ON DUPLICATE KEY UPDATE id=id";
    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . $query . " - " . mysql_error());

    $album_id = mysql_insert_id();
    return $album_id;
}


function add_photo($owner_user_id, $target_album_id, $target_album_owner_id, $visible = 1, $path_to_photo) {

    // $owner_user_id: the user who sends the email with the photo attached
    // $target_album_id: the album this photo will be added to

    global $con;
    global $s3;

    $visible_string = "";
    if (!$visible) $visible_string = "<b>invisible</b>";
    debug("Adding $visible_string photo (owner $owner_user_id) to album $target_album_id");

    $s3_url = $owner_user_id . $target_album_id . sha1(rand_string(20));
    $bucket_name = "zipio_photos";



    if ($s3->putObjectFile($path_to_photo, $bucket_name, $s3_url, S3::ACL_PUBLIC_READ)) {

        $query = "INSERT INTO Photos (
                    user_id,
                    s3_url
                  ) VALUES (
                    '$owner_user_id',
                    '$s3_url'
                  ) ON DUPLICATE KEY UPDATE id=id";
        $result = mysql_query($query, $con);
        if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . $query . " - " . mysql_error());
        $photo_id = mysql_insert_id();

        $query = "INSERT INTO AlbumPhotos (
                    photo_id,
                    photo_owner_id,
                    album_id,
                    album_owner_id,
                    visible
                  ) VALUES (
                    '$photo_id',
                    '$owner_user_id',
                    '$target_album_id',
                    '$target_album_owner_id',
                    '$visible'
                  ) ON DUPLICATE KEY UPDATE id=id";
        $result = mysql_query($query, $con);
        if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . $query . " - " . mysql_error());

        return $photo_id;

    } else {
        echo "Failed to copy file.\n";
        return 0;
    }

}




function album_exists($handle, $user_id) {

    global $con;

    // Check if the album exists for the given user
    $query = "SELECT id FROM Albums WHERE handle_hash=UNHEX(SHA1('" . $handle . "')) AND user_id='$user_id' LIMIT 1";
    debug($handle);
    debug($query);
    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());

    if ($row = mysql_fetch_assoc($result)) {
        $album_id = $row["id"];
        return $album_id;
    }

    // Check if the user exists at all!
    $query = "SELECT * FROM Users WHERE id='$user_id'";
    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());

    if (mysql_num_rows($result) == 1) {
        // The user exists
        return 0;
    } else {
        // The user doesn't exist
        return -1;
    }
}


function get_user_id_from_userstring($userstring) {

    global $con;

    $query = "SELECT id FROM Users WHERE username='$userstring' OR usercode='$userstring' LIMIT 1";
    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());

    $user_id = 0;

    if ($row = mysql_fetch_assoc($result)) {
        $user_id = $row["id"];
    }

    return $user_id;
}


function get_user_info($user_id) {

    global $con;

    $query = "SELECT * FROM Users WHERE id='$user_id' LIMIT 1";
    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());

    if ($row = mysql_fetch_assoc($result)) {
        $row["token"] = generate_token($row["id"], $row["created"]);
        return $row;
    } else {
        return 0;
    }
}

function get_album_info($album_id) {

    global $con;

    $query = "SELECT * FROM Albums WHERE id='$album_id' LIMIT 1";
    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());

    if ($row = mysql_fetch_assoc($result)) {
        $row["token"] = generate_token($row["id"], $row["created"]);
        return $row;
    } else {
        return 0;
    }
}

function get_photos_info($album_id) {

    global $con;

    $query = "SELECT photo_id FROM AlbumPhotos WHERE album_id='$album_id'";
    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());

    $photos = array();

    $photos_array = array();
    while ($row = mysql_fetch_assoc($result)) {
        $photo = get_photo_info($row["photo_id"], $album_id);
        array_push($photos_array, $photo);
    }
    return $photos_array;

}

// The album_is is required because a photo may have different properties (for
// example, visibility) depending on which album it lives in
function get_photo_info($photo_id, $album_id) {

    global $con;

    $query = "SELECT * FROM Photos WHERE id='$photo_id' LIMIT 1";
    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());

    if ($row = mysql_fetch_assoc($result)) {
        $inner_query = "SELECT * FROM AlbumPhotos WHERE photo_id='$photo_id' AND album_id='$album_id' LIMIT 1";
        $inner_result = mysql_query($inner_query, $con);
        if (!$inner_result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());
            if ($inner_row = mysql_fetch_assoc($inner_result)) {
                $row["visible"] = $inner_row["visible"];
            }

        return $row;
    } else {
        return 0;
    }
}


function update_record($id, $table_name, $column_names, $values) {
    if (count($column_names) != count($values)) {
        debug("ERROR: Mismatch in number of columns and values", "red");
    }
}


// Make all of user_id's photos in $album_id visible
function make_visible($user_id, $album_id) {
    // Get a list of photos owned by $user_id in album with id $album_id


}

function update_data($table, $id, $key_values) {

    global $con;

    $update_string = "";

    foreach ($key_values as $key=>$value) {
        $update_string .= " " . $key . "='" . mysql_real_escape_string($value) . "',";
    }

    $update_string = rtrim($update_string, ",");
    $query = "UPDATE $table SET $update_string WHERE id=$id LIMIT 1";
    debug($query);
    $result = mysql_query($query, $con);
    if (!$result) {
        return 0;
    } else {
        return 1;
    }
}








?>

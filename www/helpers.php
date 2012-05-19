<?php
ini_set("display_errors", 1);
error_reporting(E_ALL | E_STRICT);

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
        $from = "Zipio <founders@zipiyo.com>";
    }

    $request = new HttpRequest('https://api.mailgun.net/v2/zipiyo.com/messages', HttpRequest::METH_POST);
    $auth = base64_encode('api:key-68imhgvpoa-6uw3cl8728kcs9brvlmr9');
    $request->setHeaders(array('Authorization' => 'Basic '.$auth));
    $request->setPostFields(array('from' => $from, 'to' => $to, 'subject' => $subject, 'html' => $html));
    $request->send();

    return $request;
}

function generate_token($id, $created) {
    return sha1($id . $created);
}

function generate_token_from_id($id, $type) {

    global $con;

    if ($type == "USER") {
        $query = "SELECT created FROM Users WHERE id='$id' LIMIT 1";
    } else if ($type == "ALBUM") {
        $query = "SELECT created FROM Albums WHERE id='$id' LIMIT 1";
    }

    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query: ' . mysql_error());

    if ($row = mysql_fetch_assoc($result)) {
        $created = $row["created"];
        $token = generate_token($id, $created);
    } else {
        return 0;
    }

    print("token for $id $type:" . $token . "\n<br>");
    return $token;
}

function check_token($id, $token, $type) {

    $correct_token = generate_token_from_id($id, $type);

    if ($token == $correct_token) {
        return 1;
    }

    return 0;
}


function are_friends($user_id_1, $user_id_2) {

    global $con;

    $query = "SELECT * FROM Friends WHERE user_id='$user_id_1' AND friend_id=$user_id_2";
    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query: ' . mysql_error());

    if (mysql_num_rows($result) == 1) {
        return 1;
    }

    return 0;
}

function has_write_permission($user_id, $album_id) {

    global $con;

    $query = "SELECT * FROM Permissions WHERE user_id='$user_id' AND album_id=$album_id LIMIT 1";
    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query: ' . mysql_error());

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
    if (!$result) die('Invalid query: ' . $query . " - " . mysql_error());

    $album_id = mysql_insert_id();
    return $album_id;
}


function add_photo($owner_user_id, $target_album_id, $visible = 1, $path_to_photo) {

    // $owner_user_id: the user who sends the email with the photo attached
    // $target_album_id: the album this photo will be added to

    global $con;
    global $s3;

    $visible_string = "";
    if (!$visible) $visible_string = "<b>invisible</b>";
    debug("Adding $visible_string photo (owner $owner_user_id) to album $target_album_id");

    $bucket_name = "zipio_photos";
    $s3_file_name = $owner_user_id . "-" . $target_album_id . "-" . uniqid(rand_string(5));

    // Put our file (also with public read access)
    if ($s3->putObjectFile($path_to_photo, $bucket_name, $s3_file_name, S3::ACL_PUBLIC_READ)) {
            // debug("S3::putObjectFile(): File copied to {$bucket_name}/". $s3_file_name . PHP_EOL, "green");

            // Get object info
            $info = $s3->getObjectInfo($bucket_name, $s3_file_name);
            // echo "S3::getObjectInfo(): Info for {$bucket_name}/". $s3_file_name.': '. print_r($info, 1);
    } else {
            echo "Failed to copy file.\n";
    }


    $query = "INSERT INTO Photos (
                user_id,
                s3_url
              ) VALUES (
                '$owner_user_id',
                '$s3_file_name'
              ) ON DUPLICATE KEY UPDATE id=id";
    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query: ' . $query . " - " . mysql_error());

    $photo_id = mysql_insert_id();

    $query = "INSERT INTO AlbumPhotos (
                photo_id,
                album_id,
                visible
              ) VALUES (
                '$photo_id',
                '$target_album_id',
                '$visible'
              ) ON DUPLICATE KEY UPDATE id=id";
    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query: ' . $query . " - " . mysql_error());

    return $photo_id;
}




function album_exists($handle, $user_id) {

    global $con;

    // Check if the album exists for the given user
    $query = "SELECT id FROM Albums WHERE handle='$handle' AND user_id='$user_id' LIMIT 1";
    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query: ' . mysql_error());

    if ($row = mysql_fetch_assoc($result)) {
        $album_id = $row["id"];
        return $album_id;
    }

    // Check if the user exists at all!
    $query = "SELECT * FROM Users WHERE id='$user_id'";
    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query: ' . mysql_error());

    if (mysql_num_rows($result) == 1) {
        // The user exists
        return 0;
    } else {
        // The user doesn't exist
        return -1;
    }
}


function get_user_id_from_userstring($username) {

    global $con;

    $query = "SELECT id FROM Users WHERE username='$username' LIMIT 1";
    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query: ' . mysql_error());

    $user_id = -1;

    if ($row = mysql_fetch_assoc($result)) {
        $user_id = $row["id"];
    }

    return $user_id;
}


function get_user_info($user_id) {

    global $con;

    $query = "SELECT * FROM Users WHERE id='$user_id' LIMIT 1";
    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query: ' . mysql_error());

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
    if (!$result) die('Invalid query: ' . mysql_error());

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
    if (!$result) die('Invalid query: ' . mysql_error());

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
    if (!$result) die('Invalid query: ' . mysql_error());

    if ($row = mysql_fetch_assoc($result)) {
        $inner_query = "SELECT * FROM AlbumPhotos WHERE photo_id='$photo_id' AND album_id='$album_id' LIMIT 1";
        $inner_result = mysql_query($inner_query, $con);
        if (!$inner_result) die('Invalid query: ' . mysql_error());
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










?>

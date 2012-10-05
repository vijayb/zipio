<?php

ini_set("display_errors", 1);
error_reporting(E_ALL | E_STRICT);

require("constants.php");

define('CACHE_PATH', 'opticrop-cache/');

function goto_homepage($args) {
    if (!isset($args)) $args = "";
    header("Location: $g_www_root/index.php$args");
}

function login_user($user_id) {
    session_regenerate_id();
    $_SESSION["user_id"] = $user_id;
    $_SESSION["user_info"] = get_user_info($user_id);
    $_SESSION["user_info"]["token"] = calculate_token_from_id($user_id, "Users");

    // Now set a login cookie

    // setcookie("user_id", $_SESSION["user_id"], time() + 100000, "/");
    // setcookie("user_token", $_SESSION["user_info"]["token"], time() + 100000, "/");
}

function is_logged_in() {
    if (isset($_SESSION['user_id'])) {
        return $_SESSION['user_id'];
    } else {
        return 0;
    }
}

function check_request_and_cookie_for_login($args) {

    if (isset($args["request"])) {
        $request = decrypt_json($args["request"]);
        if (isset($request["user_id"])) {
            login_user($request["user_id"]);
            $url = strtok($_SERVER['REQUEST_URI'], '?');
            header("Location: $url");
            return;
        }
    }

    /*

    Temporarily disabling cookie-based login because it was acting weird...

    if (!is_logged_in() && isset($_COOKIE["user_id"]) && isset($_COOKIE["user_token"])) {

        if (check_token($_COOKIE["user_id"], $_COOKIE["user_token"], "Users")) {
            login_user($_COOKIE["user_id"]);
            $url = strtok($_SERVER['REQUEST_URI'], '?');
            header("Location: $url");
            return;
        }
    }
    */
}

function rand_string($length) {
    $chars = "abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz";
    return substr(str_shuffle($chars), 0, $length);
}

function generate_username($email) {
    global $con;
    $parts = explode('@', $email);
    $username = preg_replace("/[^A-Za-z0-9]/", "", $parts[0]);

    for ($i = 0; $i < 5; $i++) {
        $suggested_username = $username . rand_number_string($i);
        $query = "SELECT id FROM Users WHERE username_hash=UNHEX(SHA1('$suggested_username'))";
        $result = mysql_query($query, $con);
        if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . $query . " - " . mysql_error());
        if (mysql_num_rows($result) == 0) {
            return $suggested_username;
        }
    }
}

function rand_number_string($length) {
    if ($length <= 0) {
        return "";
    }

    return rand(pow(10, $length - 1), pow(10, $length) - 1);
}



function create_collaborator($collaborator_id, $album_id) {

    global $con;

    $query ="INSERT INTO Collaborators (
                collaborator_id,
                album_id
              ) VALUES (
                '$collaborator_id',
                '$album_id'
              ) ON DUPLICATE KEY UPDATE id=id";
    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . $query . " - " . mysql_error());
    $id = mysql_insert_id();
    return $id;
}

function debug($string, $color = "black") {
    print("<span style='color:$color;'>$string</span>" . "\n<br>");
}

function send_email($to, $from, $subject, $html) {

    global $g_founders_email_address;

    if ($from == "") {
        $from = $g_founders_email_address;
    }

    $html = "<span style='color:black;' id='" . time() . "'>" . $html . "</span><br><br><span style='font-size:10px; color:#aaaaaa;'>Email confirmation number: " . time() . "</span>";

    $request = new HttpRequest('https://api.mailgun.net/v2/zipio.com/messages', HttpRequest::METH_POST);
    $auth = base64_encode('api:key-68imhgvpoa-6uw3cl8728kcs9brvlmr9');
    $request->setHeaders(array('Authorization' => 'Basic '.$auth));
    $request->setPostFields(array('from'=>$from, 'to'=>$to, 'subject'=>$subject, 'html'=>$html));
    $request->send();

    return $request;
}

function encrypt_json($arr) {
    $json = json_encode($arr);
    $encrypted_text = openssl_encrypt($json, "aes-128-cbc", "cheapass", true, "1234567812345678");
    return base64_encode($encrypted_text);
}

function decrypt_json($encrypted_json) {
    $decrypted_text = openssl_decrypt(base64_decode($encrypted_json), "aes-128-cbc", "cheapass", true, "1234567812345678");
    return json_decode($decrypted_text, true);
}

function calculate_token($id, $created) {
    return sha1($id . "_" . $created);
}

function calculate_token_from_id($id, $table) {
    global $con;

    $query = "SELECT created FROM " . $table . " WHERE id='$id' LIMIT 1";
    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());

    if ($row = mysql_fetch_assoc($result)) {
        $created = $row["created"];
        $token = calculate_token($id, $created);
    } else {
        return 0;
    }
    return $token;
}

function check_token($id, $token, $table) {
    $correct_token = calculate_token_from_id($id, $table);
    if ($token == $correct_token) {
        return 1;
    }
    return 0;
}

function is_collaborator($user_id, $album_id) {

    global $con;

    $query = "SELECT * FROM Collaborators WHERE collaborator_id='$user_id' AND album_id=$album_id";
    $result = mysql_query($query, $con);
    if (!$result)
        die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());

    if (mysql_num_rows($result) == 1) {
        return 1;
    }

    return 0;
}

function create_user($name, $username, $password_hash, $email) {

    global $con;

    $query = "INSERT INTO Users (
                name,
                email,
                username,
                password_hash,
                last_notified
              ) VALUES (
                '$name',
                '$email',
                '$username',
                '$password_hash',
                NOW()
              ) ON DUPLICATE KEY UPDATE id=id";
    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . $query . " - " . mysql_error());

    $user_id = mysql_insert_id();

    return $user_id;
}


function create_album($user_id, $handle) {

    global $con;

    $handle = strtolower($handle);

    $query = "INSERT INTO Albums (
                  user_id,
                  handle,
                  read_permissions
              ) VALUES (
                  '$user_id',
                  '$handle',
                  2
              ) ON DUPLICATE KEY UPDATE id=id";
    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query in ' . __FUNCTION__ .
                      ': ' . $query . " - " . mysql_error());

    $album_id = mysql_insert_id();
    return $album_id;
}

function add_albumphoto($owner_user_id, $target_album_id, $target_album_owner_id,
                        $visible, $path_to_photo, $caption, &$s3_url_parameter) {

    // $owner_user_id: the user who sends the email with the photo attached
    // $target_album_id: the album this photo will be added to

    global $con;
    global $s3;
    global $g_s3_bucket_name;
    global $g_s3_folder_name;

    $visible_string = "";
    if (!$visible) $visible_string = "<b>invisible</b>";

    $s3_base_image_url = $owner_user_id ."_". $target_album_id . "_" . sha1(rand_string(20));
    $s3_url_parameter = $s3_base_image_url;

    $big_size = 1600;
    $cropped_size = 300;

    $failed = 0;

    $image = new imagick($path_to_photo);
    $image->setImageFormat('jpeg');
    $image->setImageCompression(Imagick::COMPRESSION_JPEG);
    $image->setImageCompressionQuality(85);

    $exif = exif_read_data($path_to_photo);

    $lat = "";
    $lng = "";

    if (extractLatLong($exif, $lat, $lng)) {
        print_r("Got lat/lng!!!".$lat.":".$lng);
    } else {
        print_r("failed to get lat /lng :(");
    }

    if (isset($exif['Orientation'])) {
        switch($exif['Orientation']) {
            case 6:
            $image->rotateImage(new ImagickPixel('none'), 90);
            break;
            case 8:
            $image->rotateImage(new ImagickPixel('none'), -90);
            break;
        }
    }

    $image->stripImage();

    $big_image = clone $image;

    if ($image->getImageWidth() > $big_size || $image->getImageHeight() > $big_size) {
        $big_image->scaleImage($big_size, $big_size, true);
    }

    $s3_big_image_name = $s3_base_image_url . "_big";
    $big_image_path = $path_to_photo . "_big";
    $big_image->writeImage($big_image_path);

    if (!$s3->putObjectFile($big_image_path,
                            $g_s3_bucket_name,
                            "$g_s3_folder_name/" . $s3_big_image_name,
                            S3::ACL_PUBLIC_READ)) {
        $failed = 1;
    }






    $cropped_image = clone $image;

    if ($image->getImageWidth() > $image->getImageHeight()) {
        $cropped_image->scaleImage(0, $cropped_size);
    } else {
        $cropped_image->scaleImage($cropped_size, 0);
    }

    $s3_cropped_image_name = $s3_base_image_url . "_cropped";
    $cropped_image_path = $path_to_photo."_cropped";
    opticrop($cropped_image, $cropped_size, $cropped_size, $cropped_image_path);

    if (!$s3->putObjectFile($cropped_image_path,
                            $g_s3_bucket_name,
                            "$g_s3_folder_name/" . $s3_cropped_image_name,
                            S3::ACL_PUBLIC_READ)) {
        $failed = 1;
    }


    unlink($big_image_path);
    unlink($cropped_image_path);

    $image_properties = $big_image->identifyImage();


    if (!$failed) {
        $lat_lng_key = "";
        $lat_lng_val = "";
        if ($lat != "" && $lng != "") {
            $lat_lng_key = "latitude,longitude,";
            $lat_lng_val = $lat.",".$lng.",";
        }
        $query = "INSERT INTO Photos (
                    user_id,
                    s3_url,
                    width,
                    height,
                    $lat_lng_key
                    mime_type
                  ) VALUES (
                    '$owner_user_id',
                    '$s3_base_image_url',
                    " . $big_image->getImageWidth() . ",
                    " . $big_image->getImageHeight() . ",
                    $lat_lng_val
                    'image/jpeg'
                  ) ON DUPLICATE KEY UPDATE id=id";
        $result = mysql_query($query, $con);
        if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' .
                          $query . " - " . mysql_error());
        $photo_id = mysql_insert_id();

        if (strlen($caption) > 0 && strlen($caption) < 200) {
            $query = "INSERT INTO AlbumPhotos (
                        photo_id,
                        photo_owner_id,
                        album_id,
                        album_owner_id,
                        caption,
                        visible
                      ) VALUES (
                        '$photo_id',
                        '$owner_user_id',
                        '$target_album_id',
                        '$target_album_owner_id',
                        '".mysql_real_escape_string($caption)."',
                        '$visible'
                      ) ON DUPLICATE KEY UPDATE id=id";
        } else {
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
        }
        $result = mysql_query($query, $con);
        if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' .
                          $query . " - " . mysql_error());
        $albumphoto_id = mysql_insert_id();
        add_event($owner_user_id, ACTION_ADD_ALBUMPHOTO, $target_album_id, $albumphoto_id, NULL);
        return $albumphoto_id;

    } else {
        echo "Failed to copy file.\n";
        return 0;
    }

}






function album_exists($handle, $user_id_or_username) {

    global $con;
    $handle = strtolower($handle);

    // Check if argument is a username
    $user_id = get_user_id_from_username($user_id_or_username);

    if ($user_id == 0) {
        // No, the argument is not a username; it's an ID
        $user_id = $user_id_or_username;
    }

    // Check if the album exists for the given user
    $query = "SELECT id FROM Albums WHERE handle_hash=UNHEX(SHA1('" . $handle . "')) AND user_id='$user_id' LIMIT 1";
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
        return -1;
    }
}


function get_user_id_from_username($username) {

    global $con;

    $query = "SELECT id FROM Users WHERE username='$username' LIMIT 1";
    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());

    $user_id = 0;

    if ($row = mysql_fetch_assoc($result)) {
        $user_id = $row["id"];
    }

    return $user_id;
}


function get_username_from_user_id($user_id) {

    global $con;

    $query = "SELECT username FROM Users WHERE id='$user_id' LIMIT 1";
    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());

    $username = "";

    if ($row = mysql_fetch_assoc($result)) {
        $username = $row["username"];
    }

    return $username;

}

function get_user_info($user_id) {

    global $con;

    $query = "SELECT * FROM Users WHERE id='$user_id' LIMIT 1";
    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());

    if ($row = mysql_fetch_assoc($result)) {
        $row["token"] = calculate_token($row["id"], $row["created"]);
        return $row;
    } else {
        return 0;
    }
}


function get_comment_info($comment_id) {

    global $con;

    $query = "SELECT * FROM Comments WHERE id='$comment_id' LIMIT 1";
    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());

    if ($row = mysql_fetch_assoc($result)) {
        return $row;
    } else {
        return 0;
    }
}

function get_user_info_from_email($email) {

    global $con;

    $query = "SELECT * FROM Users WHERE email_hash=UNHEX(SHA1('$email'))";
    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());

    if ($row = mysql_fetch_assoc($result)) {
        $row["token"] = calculate_token($row["id"], $row["created"]);
        return $row;
    } else {
        return 0;
    }
}


function normalize_email($email) {

    $email = strtolower($email);
    $parts = explode("@", $email);

    if ($parts[1] == "gmail.com") {
        $before_plus = strstr($parts[0], '+', TRUE);
        $before_at = $before_plus ? $before_plus : $parts[0];
        $before_at = str_replace('.', '', $before_at);
        $normalized_email = $before_at . '@' . $parts[1];
        print($normalized_email);
    } else {

    }


}

function get_album_owner($album_id) {
    global $con;

    $query = "SELECT user_id FROM Albums WHERE id='$album_id'";

    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());

    $albums_array = array();
    if ($row = mysql_fetch_assoc($result)) {
        return $row["user_id"];
    }

    die('Error in ' . __FUNCTION__);
}



function get_album_info($album_id) {

    global $con;

    $query = "SELECT * FROM Albums WHERE id='$album_id' LIMIT 1";
    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());

    if ($row = mysql_fetch_assoc($result)) {

        $inner_query = "SELECT id FROM AlbumPhotos WHERE album_id='$album_id' AND visible='1' ORDER BY AlbumPhotos.created DESC";
        $inner_result = mysql_query($inner_query, $con);
        if (!$inner_result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());

        $row["albumphoto_ids"] = array();

        while ($inner_row = mysql_fetch_assoc($inner_result)) {
            array_push($row["albumphoto_ids"], $inner_row["id"]);
        }

        $row["token"] = calculate_token($row["id"], $row["created"]);
        return $row;
    } else {
        return 0;
    }
}

function get_albums_info_where_owner($user_id) {

    global $con;

    $query = "SELECT * FROM Albums WHERE user_id='$user_id'";

    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());

    $albums_array = array();
    while ($row = mysql_fetch_assoc($result)) {
        $album = get_album_info($row["id"]);
        array_push($albums_array, $album);
    }
    return $albums_array;
}


function is_follower($user_id, $album_id) {
    global $con;

    $query = "SELECT * FROM AlbumFollowers WHERE user_id='$user_id' AND album_id=$album_id";
    $result = mysql_query($query, $con);
    if (!$result)
        die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());

    if (mysql_num_rows($result) == 1) {
        return 1;
    }

    return 0;

}

function get_albums_info_where_collaborator($collaborator_id) {

    global $con;

    $query = "SELECT * FROM Collaborators WHERE collaborator_id='$collaborator_id'";

    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());

    $albums_array = array();
    while ($row = mysql_fetch_assoc($result)) {
        $album = get_album_info($row["album_id"]);
        array_push($albums_array, $album);
    }
    return $albums_array;
}


function get_albumphotos_info($album_id) {
    global $con;

    $query = "SELECT id
              FROM AlbumPhotos
              WHERE album_id='$album_id'
              AND visible='1'
              ORDER BY AlbumPhotos.created DESC
              ";
    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());

    $albumphotos_array = array();
    while ($row = mysql_fetch_assoc($result)) {
        $albumphoto = get_albumphoto_info($row["id"]);
        array_push($albumphotos_array, $albumphoto);
    }
    return $albumphotos_array;
}

function get_albumphoto_info($albumphoto_id) {
    global $con;

    $query = "SELECT
                AlbumPhotos.id,
                photo_id,
                photo_owner_id,
                album_id,
                album_owner_id,
                visible,
                filtered,
                caption,
                s3_url,
                latitude,
                longitude,
                AlbumPhotos.created
              FROM AlbumPhotos
              LEFT JOIN Photos
              ON AlbumPhotos.photo_id=Photos.id
              WHERE AlbumPhotos.id='$albumphoto_id'
              LIMIT 1";
    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());

    if ($row = mysql_fetch_assoc($result)) {
        $row["token"] = calculate_token($row["id"], $row["created"]);
        $row["num_comments"] = get_comment_count($albumphoto_id);
        $row["num_likes"] = get_albumphoto_likes_count($albumphoto_id);
        return $row;
    } else {
        return 0;
    }
}

function get_comment_count($albumphoto_id) {
   global $con;

   $query = "SELECT COUNT(id)
             AS count
             FROM Comments
             WHERE albumphoto_id='$albumphoto_id'";
    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());

    if ($row = mysql_fetch_assoc($result)) {
        return $row["count"];
    }

    return 0;

}

function get_albumphoto_likes_count($albumphoto_id) {
   global $con;

   $query = "SELECT COUNT(id)
             AS count
             FROM AlbumPhotoLikes
             WHERE albumphoto_id='$albumphoto_id'";
    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());

    if ($row = mysql_fetch_assoc($result)) {
        return $row["count"];
    }

    return 0;
}

function get_albumphoto_likes_info($user_id, $album_id) {
    global $con;

    $query = "SELECT albumphoto_id, photo_owner_id
              FROM AlbumPhotoLikes
              WHERE album_id='$album_id' AND liker_id='$user_id'";
    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());

    $albumphoto_likes_info = array();
    while ($row = mysql_fetch_assoc($result)) {
        $albumphoto_likes_info[$row["albumphoto_id"]] = $row["photo_owner_id"];
    }

    return $albumphoto_likes_info;
}



function get_friends($user_id) {
   global $con;

   $query = "SELECT friend_id
             FROM Friends
             WHERE user_id='$user_id'";
    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());

    $friends_array = array();
    while ($row = mysql_fetch_assoc($result)) {
        $friend = get_user_info($row["friend_id"]);
        array_push($friends_array, $friend);
    }

    return $friends_array;

}









function get_collaborators_info($album_id) {

   global $con;

   $query = "SELECT collaborator_id
             FROM Collaborators
             WHERE album_id='$album_id'";
    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());

    $collaborators_array = array();
    while ($row = mysql_fetch_assoc($result)) {
        $collaborator = get_user_info($row["collaborator_id"]);
        array_push($collaborators_array, $collaborator);
    }

    // Now, add the owner to $collaborators_array since it's useful in the rest
    // of the code to consider an owner as also a collaborator
    $album_owner_id = get_album_owner($album_id);
    $album_owner_info = get_user_info($album_owner_id);

    array_push($collaborators_array, $album_owner_info);

    return $collaborators_array;

}

function update_data($table, $id, $key_values) {
    global $con;

    $update_string = "";

    foreach ($key_values as $key=>$value) {
        $update_string .= " " . $key . "='" . mysql_real_escape_string($value) . "',";
    }
    $update_string = rtrim($update_string, ",");


    $query = "UPDATE $table SET $update_string WHERE id=$id LIMIT 1";
    $result = mysql_query($query, $con);

    if (!$result) {
        return 0;
    } else {
        return 1;
    }
}



function create_album_followers($album_owner_id, $album_id) {
    global $con;

    $query = "SELECT user_id FROM Friends WHERE friend_id='$album_owner_id'";
    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());

    $album_string = "";
    while ($row = mysql_fetch_assoc($result)) {
        $album_string .= "(" . $row["user_id"] . "," . $album_id . "," . $album_owner_id . "),";
    }
    $album_string = rtrim($album_string, ",");


    $insert_query = "INSERT INTO AlbumFollowers (
                user_id,
                album_id,
                album_owner_id
              ) VALUES $album_string ON DUPLICATE KEY UPDATE id=id";
    print_r($insert_query);
    $insert_result = mysql_query($insert_query, $con);
    if (!$insert_result) die('Invalid query in ' . __FUNCTION__ . ': ' . $insert_query . " - " . mysql_error());
}







function add_friend($user_id, $friend_id) {
    global $con;

    $query ="INSERT INTO Friends (
                user_id,
                friend_id
              ) VALUES (
                '$user_id',
                '$friend_id'
              ) ON DUPLICATE KEY UPDATE id=id";
    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . $query . " - " . mysql_error());
    $id = mysql_insert_id();

    if (mysql_affected_rows() == 1) {

        $albums_info = get_albums_info_where_owner($friend_id);

        $album_string = "";
        foreach ($albums_info as $album) {
            $album_string .= "(" . $user_id . "," . $album["id"] . "," . $album["user_id"] . "),";
        }
        $album_string = rtrim($album_string, ",");

        $query = "INSERT INTO AlbumFollowers (
                    user_id,
                    album_id,
                    album_owner_id
                  ) VALUES $album_string ON DUPLICATE KEY UPDATE id=id";
        $result = mysql_query($query, $con);
        if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . $query . " - " . mysql_error());
    }

    return $id;
}






function add_event($actor_id, $action_type, $album_id, $albumphoto_id, $comment_id, $album_owner_id, $albumphoto_owner_id, $commenter_id) {
    global $con;

    if (isset($comment_id)) {
        $object_id = $comment_id;
    } else if (isset($albumphoto_id)) {
        $object_id = $albumphoto_id;
    } else if (isset($album_id)) {
        $object_id = $album_id;
    } else {
        print_r("ERROR no valid object was defined");
        exit();
    }

    $keys = "actor_id, action_type, object_id";
    $values = "$actor_id, $action_type, $object_id";

    if (isset($comment_id)) {
        $keys .= ",comment_id";
        $values .= ",$comment_id";
    }
    if (isset($albumphoto_id)) {
        $keys .= ",albumphoto_id";
        $values .= ",$albumphoto_id";
    }
    if (isset($album_id)) {
        $keys .= ",album_id";
        $values .= ",$album_id";
    }
    if (isset($commenter_id)) {
        $keys .= ",commenter_id";
        $values .= ",$commenter_id";
    }
    if (isset($albumphoto_owner_id)) {
        $keys .= ",albumphoto_owner_id";
        $values .= ",$albumphoto_owner_id";
    }
    if (isset($album_owner_id)) {
        $keys .= ",album_owner_id";
        $values .= ",$album_owner_id";
    }

    $query = "INSERT INTO Events ($keys) values ($values) ON DUPLICATE KEY UPDATE id=id";

    $result = mysql_query($query, $con);

    if (!$result) {
        die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());
    }
}


// If $only_new == 1, then only those alerts that the user has NOT seen will be
// returned (as determined by the $last_notified stamp).

function get_events_array($user_id, $only_new) {
    global $con;

    // Get all album_ids for which user_id should be notified of updates

    $album_ids_followed = array();


    // First, all albums where user_id is a collaborator

    $query = "SELECT album_id FROM Collaborators where collaborator_id='$user_id'";
    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());
    while ($row = mysql_fetch_assoc($result)) {
        $album_ids_followed[$row["album_id"]] = 1;
    }

    // Next, get all the albums owned by user_id
    // UGH - this is a pain. Collaborators doesn't contain the album owner so we have to get it separately
    $query = "SELECT id FROM Albums where user_id='$user_id'";
    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());
    while ($row = mysql_fetch_assoc($result)) {
        $album_ids_followed[$row["id"]] = 1;
    }

    // Third, get all the albums which you are following

    $query = "SELECT album_id FROM AlbumFollowers where user_id='$user_id'";
    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());
    while ($row = mysql_fetch_assoc($result)) {
        $album_ids_followed[$row["album_id"]] = 1;
    }

    $album_clause = "(";
    foreach ($album_ids_followed as $album_id => $val) {
        $album_clause .= "album_id=$album_id or ";
    }
    $album_clause = substr_replace($album_clause,"",-4);
    $album_clause .= ")";

    $query = "SELECT last_notified FROM Users WHERE id='$user_id'";
    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());

    if ($row = mysql_fetch_assoc($result)) {
        $last_notified = $row["last_notified"];
    } else {
        exit();
    }

    if ($only_new) {
        $query = "SELECT * FROM Events WHERE actor_id != '$user_id' and $album_clause and created > '$last_notified' ORDER BY created DESC LIMIT 10";
    } else {
        $query = "SELECT * FROM Events WHERE actor_id != '$user_id' and $album_clause ORDER BY created DESC LIMIT 10";
    }

    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());

    $events_array = array();
    while ($event = mysql_fetch_assoc($result)) {
        array_push($events_array, $event);
    }

    return $events_array;
}

function update_last_notified($user_id) {
    global $con;

    $query = "SELECT last_notified FROM Users WHERE id='$user_id'";
    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());

    if ($row = mysql_fetch_assoc($result)) {
        $last_notified = $row["last_notified"];
    } else {
        exit();
    }


    $query = "UPDATE Users SET last_notified=NOW() WHERE id='$user_id'";
    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());
}

// Input is an imagemagick image. $w/$h are the dimensions of the desired
// cropped image, and $out is where the cropped image will be written.
// Note that the dimensions of $image must be greater than or equal to
// $w x $h in both dimensions.

function opticrop($img, $w, $h, $out) {

    // source dimensions
    $w0 = $img->getImageWidth();
    $h0 = $img->getImageHeight();
    if ($w > $w0 || $h > $h0)
        die("Target dimensions must be smaller or equal to source dimensions.");

    // parameters for the edge-maximizing crop algorithm
    $r = 1;         // radius of edge filter
    $nk = 9;        // scale count: number of crop sizes to try
    $gamma = 0.2;   // edge normalization parameter -- see documentation
    $ar = $w/$h;    // target aspect ratio (AR)
    $ar0 = $w0/$h0;    // target aspect ratio (AR)

    //    dprint("$image: $w0 x $h0 => $w x $h");
    $imgcp = clone $img;

    // compute center of edginess
    $img->edgeImage($r);
    $img->modulateImage(100,0,100); // grayscale
    $img->blackThresholdImage("#0f0f0f");
    $img->writeImage($out);
    // use gd for random pixel access
    $im = ImageCreateFromJpeg($out);
    $xcenter = 0;
    $ycenter = 0;
    $sum = 0;
    $n = 100000;
    for ($k=0; $k<$n; $k++) {
        $i = mt_rand(0,$w0-1);
        $j = mt_rand(0,$h0-1);
        $val = imagecolorat($im, $i, $j) & 0xFF;
        $sum += $val;
        $xcenter += ($i+1)*$val;
        $ycenter += ($j+1)*$val;
    }
    $xcenter /= $sum;
    $ycenter /= $sum;

    // crop source img to target AR
    if ($w0/$h0 > $ar) {
        // source AR wider than target
        // crop width to target AR
        $wcrop0 = round($ar*$h0);
        $hcrop0 = $h0;
    }
    else {
        // crop height to target AR
        $wcrop0 = $w0;
        $hcrop0 = round($w0/$ar);
    }

    // crop parameters for all scales and translations
    $params = array();

    // crop at different scales
    $hgap = $hcrop0 - $h;
    $hinc = ($nk == 1) ? 0 : $hgap / ($nk - 1);
    $wgap = $wcrop0 - $w;
    $winc = ($nk == 1) ? 0 : $wgap / ($nk - 1);

    // find window with highest normalized edginess
    $n = 10000;
    $maxbetanorm = 0;

    $maxparam = array('w'=>0, 'h'=>0, 'x'=>0, 'y'=>0);
    for ($k = 0; $k < $nk; $k++) {
        $hcrop = round($hcrop0 - $k*$hinc);
        $wcrop = round($wcrop0 - $k*$winc);
        $xcrop = $xcenter - $wcrop / 2;
        $ycrop = $ycenter - $hcrop / 2;
        //dprint("crop: $wcrop, $hcrop, $xcrop, $ycrop");

        if ($xcrop < 0) $xcrop = 0;
        if ($xcrop+$wcrop > $w0) $xcrop = $w0-$wcrop;
        if ($ycrop < 0) $ycrop = 0;
        if ($ycrop+$hcrop > $h0) $ycrop = $h0-$hcrop;

        $beta = 0;
        for ($c=0; $c<$n; $c++) {
            $i = mt_rand(0,$wcrop-1);
            $j = mt_rand(0,$hcrop-1);
            $beta += imagecolorat($im, $xcrop+$i, $ycrop+$j) & 0xFF;
        }
        $area = $wcrop * $hcrop;
        $betanorm = $beta / ($n*pow($area, $gamma-1));
        //dprint("beta: $beta; betan: $betanorm");

        //dprint("image$k.jpg:<br/>\n<img src=\"$currfile\"/>");
        // best image found, save it
        if ($betanorm > $maxbetanorm) {
            $maxbetanorm = $betanorm;
            $maxparam['w'] = $wcrop;
            $maxparam['h'] = $hcrop;
            $maxparam['x'] = $xcrop;
            $maxparam['y'] = $ycrop;
            //$maxfile = $currfile;
        }
    }

    // return image
    $imgcp->cropImage($maxparam['w'],$maxparam['h'],
                      $maxparam['x'],$maxparam['y']);
    $imgcp->scaleImage($w,$h);
    $imgcp->writeImage($out);

    chmod($out, 0777);
    $img->destroy();
    $imgcp->destroy();
    return 0;
}



function extractLatLong($exif, &$lat, &$lng) {
    if (isset($exif["GPSLongitude"]) &&
        isset($exif["GPSLongitudeRef"]) &&
        isset($exif["GPSLatitude"]) &&
        isset($exif["GPSLatitudeRef"]))
    {
        $lat = getGps($exif["GPSLatitude"], $exif['GPSLatitudeRef']);
        $lng = getGps($exif["GPSLongitude"], $exif['GPSLongitudeRef']);
        return 1;
    }

    return 0;
}

function getGps($exifCoord, $hemi) {
    $degrees = count($exifCoord) > 0 ? gps2Num($exifCoord[0]) : 0;
    $minutes = count($exifCoord) > 1 ? gps2Num($exifCoord[1]) : 0;
    $seconds = count($exifCoord) > 2 ? gps2Num($exifCoord[2]) : 0;

    $flip = ($hemi == 'W' or $hemi == 'S') ? -1 : 1;

    return $flip * ($degrees + $minutes / 60 + $seconds / 3600);
}

function gps2Num($coordPart) {
    $parts = explode('/', $coordPart);

    if (count($parts) <= 0)
        return 0;

    if (count($parts) == 1)
        return $parts[0];

    return floatval($parts[0]) / floatval($parts[1]);
}


?>

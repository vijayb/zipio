<?php

ini_set("display_errors", 1);
error_reporting(E_ALL | E_STRICT);

require("constants.php");
require("db.php");
require("helpers.php");


if (!isset($_POST["comment_id"]) ||
    !isset($_POST["albumphoto_id"]) ||
    !isset($_POST["album_id"]) ||
    !isset($_POST["commenter_id"]) ||
    !isset($_POST["liker_id"]) ||
    !isset($_POST["liker_username"]) ||
    !isset($_POST["token"]) ||
    !isset($_POST["old_like_value"]) ||
    !isset($_POST["albumphoto_owner_id"]) ||
    !isset($_POST["album_handle"]) ||
    !isset($_POST["albumphoto_s3"])
    ) {
    print("0");
    exit();
} else {
    $comment_id = $_POST["comment_id"];
    $albumphoto_id = $_POST["albumphoto_id"];
    $album_id = $_POST["album_id"];
    $commenter_id = $_POST["commenter_id"];
    $liker_id = $_POST["liker_id"];
    $liker_username = $_POST["liker_username"];
    $token = $_POST["token"];

    $old_like_value = $_POST["old_like_value"];
    $albumphoto_owner_id = $_POST["albumphoto_owner_id"];
    $album_handle = $_POST["album_handle"];
    $albumphoto_s3 = $_POST["albumphoto_s3"];
}

if (!check_token($liker_id, $token, "Users")) {
    print("0");
    exit();
}

if ($old_like_value == "0") {
    $query ="INSERT INTO CommentLikes (
              comment_id,
              albumphoto_id,
              album_id,
              commenter_id,
              liker_id
            ) VALUES (
              '$comment_id',
              '$albumphoto_id',
              '$album_id',
              '$commenter_id',
              '$liker_id'
            ) ON DUPLICATE KEY UPDATE id=id";
    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . $query . " - " . mysql_error());
} else {
    $query ="DELETE from CommentLikes where comment_id='$comment_id' and liker_id='$liker_id'";
    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . $query . " - " . mysql_error());
    print("1");
    exit();
}

// Now, let's email the albumphoto owner and album owner about the like

/*
$albumphoto_owner_info = get_user_info($albumphoto_owner_id);
$album_owner_info = get_user_info(get_album_owner($album_id));

$users_to_be_emailed = array();



if ($albumphoto_owner_id != $liker_id) {
    array_push($users_to_be_emailed, $albumphoto_owner_info);
}

if ($album_owner_info["id"] != $liker_id && $albumphoto_owner_id != $album_owner_info["id"]) {
    array_push($users_to_be_emailed, $album_owner_info);
}


for ($i = 0; $i < count($users_to_be_emailed); $i++) {

    $display_album_ra = array();
    $display_album_ra["user_id"] = $users_to_be_emailed[$i]["id"];
    $display_album_ra["timestamp"] = time();
    $display_album_pretty_link = $g_www_root . "/" . $album_owner_info["username"] . "/" . $album_handle;
    $display_album_link_register = $display_album_pretty_link . "?request=" . urlencode(encrypt_json($display_album_ra)) . "#register=true";
    $display_album_link_comment = $display_album_pretty_link . "?request=" . urlencode(encrypt_json($display_album_ra)) . "#modal=comment&albumphoto_id=" . $albumphoto_id . "&albumphoto_s3=" . $albumphoto_s3;

    $pictures_html = "<img src='" . $g_s3_root . "/" . $albumphoto_s3 . "'><br><br>";

    if ($albumphoto_owner_id == $users_to_be_emailed[$i]["id"]) {
        $subject = "$liker_username liked your photo in the $album_handle album";
    } else {
        $subject = "$liker_username liked a photo in your $album_handle album";
    }

    $email_body = <<<EMAIL
        <a href='{$display_album_link_comment}'>Add a comment to this photo</a>
        <br><br>
        $pictures_html
EMAIL;

    if (!$g_debug) {
        send_email($users_to_be_emailed[$i]["email"], $g_founders_email_address, $subject, $email_body);
    }

}
*/



print("1");

?>

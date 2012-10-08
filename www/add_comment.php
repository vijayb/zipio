<?php

ini_set("display_errors", 1);
error_reporting(E_ALL | E_STRICT);

require("constants.php");
require("db.php");
require("helpers.php");


if (!isset($_POST["albumphoto_id"]) ||
    !isset($_POST["comment"]) ||
    !isset($_POST["token"]) ||
    !isset($_POST["commenter_id"]) ||
    !isset($_POST["album_id"]) ||
    !isset($_POST["album_owner_id"]) ||
    !isset($_POST["album_owner_username"]) ||
    !isset($_POST["album_handle"]) ||
    !isset($_POST["commenter_username"]) ||
    !isset($_POST["albumphoto_s3"])
    ) {
    print("0");
    exit();
} else {
    $albumphoto_id = $_POST["albumphoto_id"];
    $comment = mysql_real_escape_string($_POST["comment"]);
    $token = $_POST["token"];
    $commenter_id = $_POST["commenter_id"];
    $album_id = $_POST["album_id"];
    $album_owner_id = $_POST["album_owner_id"];
    $album_owner_username = $_POST["album_owner_username"];
    $album_handle = $_POST["album_handle"];
    $commenter_username = $_POST["commenter_username"];
    $albumphoto_s3 = $_POST["albumphoto_s3"];
}

if (!check_token($commenter_id, $token, "Users")) {
    print("0");
    exit();
}

$query ="INSERT INTO Comments (
            albumphoto_id,
            comment,
            album_id,
            album_owner_id,
            commenter_id
          ) VALUES (
            '$albumphoto_id',
            '$comment',
            '$album_id',
            '$album_owner_id',
            '$commenter_id'
          )";
$result = mysql_query($query, $con);
if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . $query . " - " . mysql_error());


// Now add the event for creating the comment!
$comment_id = mysql_insert_id();

$albumphoto_info = get_albumphoto_info($albumphoto_id);

add_event($commenter_id, ACTION_ADD_COMMENT, $album_id, $albumphoto_id, $comment_id, $album_owner_id, $albumphoto_info["photo_owner_id"], $commenter_id);

$collaborators_array = get_collaborators_info($album_id);

for ($i = 0; $i < count($collaborators_array); $i++) {

    if ($collaborators_array[$i]["id"] == $commenter_id) {
        continue;
    }

    $display_album_ra = array();
    $display_album_ra["user_id"] = $collaborators_array[$i]["id"];
    $display_album_ra["timestamp"] = time();
    $display_album_pretty_link = $g_www_root . "/" . $album_owner_username . "/" . $album_handle;
    $display_album_link_register = $display_album_pretty_link . "?request=" . urlencode(encrypt_json($display_album_ra)) . "#register=true";
    $display_album_link_comment = $display_album_pretty_link . "?request=" . urlencode(encrypt_json($display_album_ra)) . "#modal=comment&albumphoto_id=" . $albumphoto_id . "&albumphoto_s3=" . $albumphoto_s3;

    $pictures_html = "<img src='" . $g_s3_root . "/" . $albumphoto_s3 . "'><br><br>";

    $collaborator_email_body = <<<EMAIL
        <b>{$commenter_username}</b> just commented on a photo in the <b>{$album_handle}</b> album.
        <br><br>
        "{$comment}"
        <br><br>
        <a href='{$display_album_link_comment}'>Add your own comment</a>
        <br><br>
        $pictures_html

EMAIL;

    if (!$g_debug) {
        send_email($collaborators_array[$i]["email"], $g_founders_email_address, "New comment in the $album_handle album", $collaborator_email_body);
    }

}



print("1");

?>
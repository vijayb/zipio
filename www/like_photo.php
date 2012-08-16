<?php

ini_set("display_errors", 1);
error_reporting(E_ALL | E_STRICT);

require("constants.php");
require("db.php");
require("helpers.php");


if (!isset($_POST["albumphoto_id"]) ||
    !isset($_POST["token"]) ||
    !isset($_POST["album_id"]) ||
    !isset($_POST["liker_id"]) ||
    !isset($_POST["old_like_value"]) ||
    !isset($_POST["albumphoto_owner_id"]) ||
    !isset($_POST["album_handle"]) ||
    !isset($_POST["commenter_username"])
    ) {
    print("0");
    exit();
} else {
    $albumphoto_id = $_POST["albumphoto_id"];
    $token = $_POST["token"];
    $album_id = $_POST["album_id"];
    $liker_id = $_POST["liker_id"];
    $old_like_value = $_POST["old_like_value"];
    $albumphoto_owner_id = $_POST["albumphoto_owner_id"];
    $album_handle = $_POST["album_handle"];
    $commenter_username = $_POST["commenter_username"];
}

if (!check_token($liker_id, $token, "Users")) {
    print("0");
    exit();
}

if ($old_like_value == "0") {
    $query ="INSERT INTO AlbumPhotoLikes (
              albumphoto_id,
              album_id,
              photo_owner_id,
              liker_id
            ) VALUES (
              '$albumphoto_id',
              '$album_id',
              '$albumphoto_owner_id',
              '$liker_id'
            ) ON DUPLICATE KEY UPDATE id=id";
    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . $query . " - " . mysql_error());
} else {
    $query ="DELETE from AlbumPhotoLikes where albumphoto_id='$albumphoto_id' and liker_id='$liker_id'";
    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . $query . " - " . mysql_error());
}

/*
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

    $pictures_html = "<img src='" . $g_s3_root . "/" . $albumphoto_s3 . "_cropped'><br><br>";

    $collaborator_email_body = <<<EMAIL
        <b>{$commenter_username}</b> just commented on a photo in the <b>{$album_handle}</b> album.
        <br><br>
        "{$comment}"
        <br><br>
        <a href='{$display_album_link_comment}'>Add your own comment</a>
        <br><br>
        $pictures_html

EMAIL;

    send_email($collaborators_array[$i]["email"], $g_founders_email_address, "New comment in the $album_handle album", $collaborator_email_body);

}
*/



print("1");

?>
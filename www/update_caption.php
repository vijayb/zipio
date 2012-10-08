<?php

ini_set("display_errors", 1);
error_reporting(E_ALL | E_STRICT);

require("constants.php");
require("db.php");
require("helpers.php");

if (!isset($_POST["albumphoto_id"]) ||
    !isset($_POST["caption"]) ||
    !isset($_POST["token"]) ||
    !isset($_POST["album_id"]) ||
    !isset($_POST["album_owner_id"]) ||
    !isset($_POST["caption_modifier_id"]) ||
    !isset($_POST["caption_modifier_username"]) ||
    !isset($_POST["album_handle"]) ||
    !isset($_POST["albumphoto_owner_id"]) ||
    !isset($_POST["albumphoto_s3"])) {
    print("0");
    exit();
} else {
    $albumphoto_id = $_POST["albumphoto_id"];
    $caption = $_POST["caption"];
    $token = $_POST["token"];
    $album_id = $_POST["album_id"];
    $album_owner_id = $_POST["album_owner_id"];
    $caption_modifier_id = $_POST["caption_modifier_id"];
    $caption_modifier_username = $_POST["caption_modifier_username"];
    $album_handle = $_POST["album_handle"];
    $albumphoto_owner_id = $_POST["albumphoto_owner_id"];
    $albumphoto_s3 = $_POST["albumphoto_s3"];
}

if (!check_token($album_id, $token, "Albums")) {
    print("0");
    exit();
}

$result = update_data("AlbumPhotos", $albumphoto_id, array("caption" => $caption));

add_event($caption_modifier_id, ACTION_EDIT_CAPTION, $album_id, $albumphoto_id, NULL, $album_owner_id, $albumphoto_owner_id, NULL);


// Now, let's email the albumphoto owner and album owner about the modified caption

$albumphoto_owner_info = get_user_info($albumphoto_owner_id);
$album_owner_info = get_user_info(get_album_owner($album_id));

$users_to_be_emailed = array();

if ($albumphoto_owner_id != $caption_modifier_id) {
    array_push($users_to_be_emailed, $albumphoto_owner_info);
}

if ($album_owner_info["id"] != $caption_modifier_id && $albumphoto_owner_id != $album_owner_info["id"]) {
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
        $subject = "$caption_modifier_username added a caption to your photo in the $album_handle album";
    } else {
        $subject = "$caption_modifier_username added a caption to a photo in your $album_handle album";
    }

    $email_body = <<<EMAIL
        New caption: {$caption}<BR><BR>
        <a href='{$display_album_link_comment}'>Add a comment to this photo</a>
        <br><br>
        $pictures_html
EMAIL;

    if (!$g_debug) {
        send_email($users_to_be_emailed[$i]["email"], $g_founders_email_address, $subject, $email_body);
    }

}



print("1");

?>

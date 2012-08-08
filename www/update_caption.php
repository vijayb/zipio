<?php

ini_set("display_errors", 1);
error_reporting(E_ALL | E_STRICT);

require("constants.php");
require("db.php");
require("helpers.php");

if (!isset($_POST["albumphoto_id"]) || !isset($_POST["caption"]) || !isset($_POST["token"]) || !isset($_POST["album_id"])) {
    print("0");
    exit();
} else {
    $albumphoto_id = $_POST["albumphoto_id"];
    $caption = $_POST["caption"];
    $token = $_POST["token"];
    $album_id = $_POST["album_id"];
}

if (!check_token($album_id, $token, "Albums")) {
    print("0");
    exit();
}

$result = update_data("AlbumPhotos", $albumphoto_id, array("caption" => mysql_real_escape_string($caption)));

print("1");

?>
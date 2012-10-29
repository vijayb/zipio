<?php

ini_set("display_errors", 1);
error_reporting(E_ALL | E_STRICT);

require("constants.php");
require("db.php");
require("helpers.php");

if (!isset($_GET["albumphoto_id"]) ||
    !isset($_GET["album_id"]) ||
    !isset($_GET["token"])) {
    exit();
} else {
    $albumphoto_id = $_GET["albumphoto_id"];
    $album_id = $_GET["album_id"];
    $token = $_GET["token"];
}

if (!check_token($albumphoto_id, $token, "AlbumPhotos")) {
    print("0");
    exit();
}

update_data("Albums",
            $album_id,
            array("cover_albumphoto_id" => $albumphoto_id));


print("1");

?>
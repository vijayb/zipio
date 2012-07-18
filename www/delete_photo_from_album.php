<?php

ini_set("display_errors", 1);
error_reporting(E_ALL | E_STRICT);

require("constants.php");
require("db.php");
require("helpers.php");

if (!isset($_GET["albumphoto_id"]) ||
    !isset($_GET["token"])) {
    exit();
} else {
    $albumphoto_id = $_GET["albumphoto_id"];
    $token = $_GET["token"];
}

if (!check_token($albumphoto_id, $token, "AlbumPhotos")) {
    print("0");
    exit();
}

$albumphoto_info = get_albumphoto_info($albumphoto_id);

$query = "DELETE FROM AlbumPhotos WHERE photo_id='" . $albumphoto_info["photo_id"] . "' AND album_id='" . $albumphoto_info["album_id"] . "'";
$result = mysql_query($query, $con);
if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());

print("1");

?>
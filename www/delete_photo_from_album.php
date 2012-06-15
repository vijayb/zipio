<?php

ini_set("display_errors", 1);
error_reporting(E_ALL | E_STRICT);

require("db.php");
require("helpers.php");

if (!isset($_GET["photo_id"]) || !isset($_GET["album_id"])) {
    exit();
} else {
    $photo_id = $_GET["photo_id"];
    $album_id = $_GET["album_id"];
}

$query = "DELETE FROM AlbumPhotos WHERE photo_id='$photo_id' AND album_id='$album_id'";
$result = mysql_query($query, $con);
if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());

?>
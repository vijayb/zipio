<?php

ini_set("display_errors", 1);
error_reporting(E_ALL | E_STRICT);

require("constants.php");
require("db.php");
require("helpers.php");

if (!isset($_GET["album_id"]) ||
    !isset($_GET["token"])) {
    exit();
} else {
    $album_id = $_GET["album_id"];
    $token = $_GET["token"];
}

if (!check_token($album_id, $token, "Albums")) {
    print("0");
    exit();
}

$album_info = get_album_info($album_id);

$query = "DELETE FROM Albums WHERE id='$album_id'";
$result = mysql_query($query, $con);
if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());

$query = "DELETE FROM AlbumPhotos WHERE album_id='$album_id'";
$result = mysql_query($query, $con);
if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());

$query = "DELETE FROM Followers WHERE album_id='$album_id'";
$result = mysql_query($query, $con);
if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());

$query = "DELETE FROM Collaborators WHERE album_id='$album_id'";
$result = mysql_query($query, $con);
if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());

print("1");

?>
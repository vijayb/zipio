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
    $cover_photo_id = $_GET["cover_photo_id"];
}

/***************** handles cover photo issue -- shobit *******************/

if ($cover_photo_id == $photo_id) {
    $query = "SELECT photo_id FROM AlbumPhotos WHERE photo_id!='$photo_id' AND album_id='$album_id' LIMIT 1";
    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());
    $row = mysql_fetch_array($result);
    
    $new_cover_photo_id = $row['photo_id'];
    $query = "UPDATE Albums SET cover_photo_id = '$new_cover_photo_id' WHERE id='$album_id'";
    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());
}

/***********************************************************************/

$query = "DELETE FROM AlbumPhotos WHERE photo_id='$photo_id' AND album_id='$album_id'";
$result = mysql_query($query, $con);
if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());

/********************** corner case of cover photo issue **************/
$query = "SELECT * FROM AlbumPhotos WHERE album_id='$album_id'";
$result = mysql_query($query, $con);
if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());
$count = mysql_num_rows($result);
if ($count == 0) {
    $query = "DELETE FROM Albums WHERE id='$album_id'";    
    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());
}
/***********************************************************************/

?>
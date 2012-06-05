<?php
ini_set("display_errors", 1);
error_reporting(E_ALL | E_STRICT);

require("db.php");
require("helpers.php");


if (!isset($_GET["user_id"]) || !isset($_GET["user_token"]) || !isset($_GET["album_id"]) || !isset($_GET["album_token"])) {
    exit();
}

$user_id = $_GET["user_id"];
$user_token = $_GET["user_token"];
$album_id = $_GET["album_id"];
$album_token = $_GET["album_token"];

if (!check_token($user_id, $user_token, "Users") || !check_token($album_id, $album_token, "Albums")) {
    exit();
}

$query = "INSERT INTO Permissions (
            user_id,
            album_id
          ) VALUES (
            '$user_id',
            '$album_id'
          ) ON DUPLICATE KEY UPDATE id=id";
debug($query);
$result = mysql_query($query, $con);
if (!$result) die('Invalid query: ' . $query . " - " . mysql_error());


// Now make all of user_id's photos in album_id visible



$photos_array = get_photos_info($album_id);


for ($i = 0; $i < count($photos_array); $i++) {
    if ($photos_array[$i]["user_id"] == $user_id) {
        $query = "UPDATE AlbumPhotos SET visible=1 WHERE photo_id=" . $photos_array[$i]["id"];
        debug($query);
        $result = mysql_query($query, $con);
        if (!$result) die('Invalid query: ' . $query . " - " . mysql_error());
    }
}

?>

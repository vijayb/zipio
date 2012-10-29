<?php

ini_set("display_errors", 1);
error_reporting(E_ALL | E_STRICT);

require("constants.php");
require("db.php");
require("helpers.php");

if (!isset($_GET["albumphoto_id"])) {
    print("0");
    exit();
} else {
    $albumphoto_id = $_GET["albumphoto_id"];
}




$query = "SELECT
            AlbumPhotoLikes.id,
            AlbumPhotoLikes.liker_id,
            Users.username AS username,
            AlbumPhotoLikes.created
          FROM AlbumPhotoLikes
          LEFT JOIN Users
          ON AlbumPhotoLikes.liker_id = Users.id
          WHERE albumphoto_id='$albumphoto_id'";
$result = mysql_query($query, $con);
if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());


$comments_arr = array();

$num_comments = 0;
while ($row = mysql_fetch_assoc($result)) {
    array_push($comments_arr, $row);
    $num_comments++;
}

echo(json_encode($comments_arr));

?>
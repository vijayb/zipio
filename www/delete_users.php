<?php
ini_set("display_errors", 1);
error_reporting(E_ALL | E_STRICT);

require "db.php";

if (!isset($_GET["user_id"]) || !preg_match("/^[0-9]+$/", $_GET["user_id"])) {
    exit();
}
$user_id = $_GET["user_id"];

$query = "delete from Users where id=$user_id LIMIT 1";
mysql_query($query, $con);

$query = "delete from Collaborators where album_owner_id=$user_id or collaborator_id=$user_id";
mysql_query($query, $con);

$query = "delete from AlbumPhotos where album_owner_id=$user_id or photo_owner_id=$user_id";
mysql_query($query, $con);

$query = "delete from Albums where user_id=$user_id";
mysql_query($query, $con);

$query = "delete from Followers where follower_id=$user_id or album_owner_id=$user_id";
mysql_query($query, $con);

$query = "delete from Friends where user_id=$user_id or friend_id=$user_id";
mysql_query($query, $con);

$query = "delete from Photos where user_id=$user_id";
mysql_query($query, $con);

?>

<?php
ini_set("display_errors", 1);
error_reporting(E_ALL | E_STRICT);

require("db.php");
require("helpers.php");


if (!isset($_GET["user_id"]) || !isset($_GET["user_token"]) || !isset($_GET["target_user_id"]) || !isset($_GET["target_user_token"])) {
	exit();
}

$user_id = $_GET["user_id"];
$user_token = $_GET["user_token"];
$target_user_id = $_GET["target_user_id"];
$target_user_token = $_GET["target_user_token"];

if (!check_token($user_id, $user_token, "USER") || !check_token($target_user_id, $target_user_token, "USER")) {
	exit();
}

$query = "INSERT INTO Friends (
            user_id,
            friend_id
          ) VALUES (
            '$target_user_id',
            '$user_id'
          ), (
	    '$user_id',
	    '$target_user_id'
	  ) ON DUPLICATE KEY UPDATE id=id";
$result = mysql_query($query, $con);
if (!$result) die('Invalid query: ' . $query . " - " . mysql_error());


// Now, all of user_id's photos that live in target_user_id's albums need to be made visible



$query = "UPDATE AlbumPhotos SET visible=1 WHERE photo_owner_id=$user_id AND album_owner_id=$target_user_id";
$result = mysql_query($query, $con);
if (!$result) die('Invalid query: ' . $query . " - " . mysql_error());


$user_info = get_user_info($user_id);

$output = <<<EMAIL
	You've added {$user_info["username"]} (that's {$user_info["email"]}) as a friend. You can now add to each other's albums.
EMAIL;

print($output);

?>
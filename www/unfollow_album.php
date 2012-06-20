<?php
session_start();
ini_set("display_errors", 1);
error_reporting(E_ALL | E_STRICT);

require("db.php");
require("helpers.php");

if (!isset($_GET["user_id"]) || !isset($_GET["album_id"])) {
    exit();
} else {
    $user_id    = $_GET["user_id"];
    $album_id = $_GET["album_id"];
}

if (!check_token($_SESSION["user_id"], $_GET["token"])) {
    print("0");
    exit();
}

$query = "DELETE FROM Followers WHERE follower_id='$user_id' AND album_id='$album_id'";
$result = mysql_query($query, $con);
if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());

print("1");
exit();

?>
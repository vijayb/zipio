<?php

session_start();
ini_set("display_errors", 1);
error_reporting(E_ALL | E_STRICT);

require("db.php");
require("helpers.php");

if (!isset($_GET["userid"]) || !isset($_GET["album_id"])) {
    exit();
} else {
    $userid    = $_GET["userid"];
    $album_id = $_GET["album_id"];
}

$query = "DELETE FROM Followers WHERE follower_id='$userid' AND album_id='$album_id'";
$result = mysql_query($query, $con);
if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());
return;

?>
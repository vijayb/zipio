<?php

ini_set("display_errors", 1);
error_reporting(E_ALL | E_STRICT);

require("constants.php");
require("db.php");
require("helpers.php");

if (!isset($_GET["album_id"]) || !isset($_GET["user_id"]) || !isset($_GET["token"])) {
    exit();
} else {
    $album_id = $_GET["album_id"];
    $user_id = $_GET["user_id"];
    $token = $_GET["token"];
}

if (!check_token($user_id, $token, "Users")) {
    print("0");
    exit();
}

$query ="INSERT INTO AlbumFollowers (
            user_id,
            album_id
          ) VALUES (
            '$user_id',
            '$album_id'
          ) ON DUPLICATE KEY UPDATE id=id";
$result = mysql_query($query, $con);
if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . $query . " - " . mysql_error());

$id = mysql_insert_id();
return $id;

?>
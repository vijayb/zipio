<?php

ini_set("display_errors", 1);
error_reporting(E_ALL | E_STRICT);

require("constants.php");
require("db.php");
require("helpers.php");

if (!isset($_GET["album_id"]) ||
    !isset($_GET["user_id"]) ||
    !isset($_GET["action"]) ||
    !isset($_GET["album_owner_id"]) ||
    !isset($_GET["token"])) {
    exit();
} else {
    $album_id = $_GET["album_id"];
    $user_id = $_GET["user_id"];
    $action = $_GET["action"];
    $album_owner_id = $_GET["album_owner_id"];
    $token = $_GET["token"];
}

if (!check_token($user_id, $token, "Users")) {
    print("0");
    exit();
}

if ($action == "add") {

    $query ="INSERT INTO AlbumFollowers (
                user_id,
                album_id,
                album_owner_id
              ) VALUES (
                '$user_id',
                '$album_id',
                '$album_owner_id'
              ) ON DUPLICATE KEY UPDATE id=id";
    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . $query . " - " . mysql_error());
    print("1");

} else if ($action == "delete") {

    $query = "DELETE FROM AlbumFollowers WHERE user_id='$user_id' AND album_id='$album_id' LIMIT 1";
    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . $query . " - " . mysql_error());
    print("1");
} else {
    print("0");
}



?>
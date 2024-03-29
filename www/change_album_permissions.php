<?php

ini_set("display_errors", 1);
error_reporting(E_ALL | E_STRICT);

require("constants.php");
require("db.php");
require("helpers.php");

if (!isset($_GET["album_id"]) || !isset($_GET["permissions"]) || !isset($_GET["token"])) {
    exit();
} else {
    $album_id = $_GET["album_id"];
    $permissions = $_GET["permissions"];
    $token = $_GET["token"];
}

if (!check_token($album_id, $token, "Albums")) {
    print("0");
    exit();
}

$result = update_data("Albums", $album_id, array("permissions" => mysql_real_escape_string($permissions)));

print("1");

?>
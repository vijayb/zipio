<?php
session_start();
ini_set("display_errors", 1);
error_reporting(E_ALL | E_STRICT);

require("constants.php");
require("db.php");
require("helpers.php");

if (!isset($_GET["id"]) ||
    !isset($_GET["token"]) ||
    !isset($_GET["table"])
    ) {
    exit();
} else {
    $id = $_GET["id"];
    $token = $_GET["token"];
    $table = $_GET["table"];
}

print(check_token($id, $token, $table));

print("<br><br>");

print(calculate_token_from_id($id, $table));

?>
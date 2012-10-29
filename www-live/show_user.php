<?php
ini_set("display_errors", 1);
error_reporting(E_ALL | E_STRICT);

require "db.php";

if (!isset($_GET["user_id"]) || !preg_match("/^[0-9]+$/", $_GET["user_id"])) {
    exit();
}
$user_id = $_GET["user_id"];



?>

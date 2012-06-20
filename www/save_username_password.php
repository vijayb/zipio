<?php
session_start();
ini_set("display_errors", 1);
error_reporting(E_ALL | E_STRICT);

require("db.php");
require("helpers.php");

if (!isset($_GET["token"]) || !isset($_GET["username"]) || !isset($_GET["password_hash"])) {
    exit();
}

if (!check_token($_SESSION["user_id"], $_GET["token"], "Users")) {
    exit();
}

$result = update_data("Users", $_SESSION["user_id"], array("username" => mysql_real_escape_string($_GET["username"]),
                                                           "password_hash" => mysql_real_escape_string($_GET["password_hash"])));

login_user($_SESSION["user_id"]);
print($result);

?>
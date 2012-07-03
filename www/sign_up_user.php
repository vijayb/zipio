<?php
session_start();
ini_set("display_errors", 1);
error_reporting(E_ALL | E_STRICT);

require("db.php");
require("helpers.php");

if (!isset($_GET["username"]) || !isset($_GET["password_hash"]) || !isset($_GET["email"])) {
    exit();
}

$username = mysql_real_escape_string($_GET["username"]);
$password_hash = mysql_real_escape_string($_GET["password_hash"]);
$email = mysql_real_escape_string($_GET["email"]);


$user_id = create_user("", $username, $password_hash, $email);

login_user($user_id);

print($user_id);

?>
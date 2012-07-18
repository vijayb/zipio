<?php
session_start();
ini_set("display_errors", 1);
error_reporting(E_ALL | E_STRICT);

require("constants.php");
require("db.php");
require("helpers.php");

if (!isset($_GET["email"]) || !isset($_GET["password_hash"])) {
    exit();
} else {
    $email = mysql_real_escape_string($_GET["email"]);
    $password_hash = mysql_real_escape_string($_GET["password_hash"]);
}

$query = "SELECT id FROM Users WHERE email='$email' AND password_hash='$password_hash' LIMIT 1";
$result = mysql_query($query, $con);
if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());

if (mysql_num_rows($result) == 1) {
    $row = mysql_fetch_assoc($result);
    $user_id = $row['id'];
    print($user_id);

    login_user($user_id);
    exit();
} else {
    print("0");
}

?>
<?php
// session_start(); // not needed i believe --shobit
ini_set("display_errors", 1);
error_reporting(E_ALL | E_STRICT);

require("db.php");
require("helpers.php");

if (!isset($_GET["username"]) || !isset($_GET["password_hash"]) || !isset($_GET["email_add"])) {
    exit();
}

$username = mysql_real_escape_string($_GET["username"]);
$password_hash = mysql_real_escape_string($_GET["password_hash"]);
$email_add = mysql_real_escape_string($_GET["email_add"]);

$result = create_user($username, $password_hash, $email_add);

print($result);

?>
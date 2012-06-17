<?php

ini_set("display_errors", 1);
error_reporting(E_ALL | E_STRICT);

require("db.php");
require("helpers.php");

if (!isset($_GET["email"])) {
    exit();
} else {
    $email = $_GET["email"];
}

$query = "SELECT id FROM Users WHERE email='$email' LIMIT 1";
$result = mysql_query($query, $con);
if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());

if (mysql_num_rows($result) == 0) {
    print("0");
    exit();
} else {
    print("1");
}


?>
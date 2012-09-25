<?php
session_start();
ini_set("display_errors", 1);
error_reporting(E_ALL | E_STRICT);

require("constants.php");
require("db.php");
require("helpers.php");

if (!isset($_GET["friend_id"]) ||
    !isset($_GET["user_id"]) ||
    !isset($_GET["user_token"])
    ) {
    exit();
} else {
    $friend_id = $_GET["friend_id"];
    $user_id = $_GET["user_id"];
    $user_token = $_GET["user_token"];
}


if (!check_token($user_id, $user_token, "Users")) {
    print("0");
    exit();
}

$query = "DELETE FROM Friends WHERE friend_id='$friend_id' AND user_id='$user_id'";
$result = mysql_query($query, $con);
if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());

print("1");
exit();

?>
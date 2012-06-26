<?php

ini_set("display_errors", 1);
error_reporting(E_ALL | E_STRICT);

require("db.php");
require("helpers.php");

if (!isset($_GET["friend_id"]) ||
    !isset($_GET["user_id"])) {
    exit();
} else {
    $friend_id = $_GET["friend_id"];
    $user_id = $_GET["user_id"];
}

$query = "DELETE FROM Friends WHERE friend_id='" . $friend_id. "' AND user_id='" . $user_id . "'";
$result = mysql_query($query, $con);
if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());

print("1");

?>
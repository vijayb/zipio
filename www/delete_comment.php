<?php
session_start();
ini_set("display_errors", 1);
error_reporting(E_ALL | E_STRICT);

require("constants.php");
require("db.php");
require("helpers.php");

if (!isset($_GET["comment_id"]) ||
    !isset($_GET["commenter_id"]) ||
    !isset($_GET["token"])
    ) {
    print("0");
    exit();
} else {
    $comment_id = $_GET["comment_id"];
    $commenter_id = $_GET["commenter_id"];
    $token = $_GET["token"];
}


if (!check_token($commenter_id, $token, "Users")) {
    print("0");
    exit();
}


$query = "DELETE FROM Comments WHERE id='$comment_id' and (commenter_id='$commenter_id' or album_owner_id='$commenter_id')";
$result = mysql_query($query, $con);
if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());

print("1");
exit();

?>
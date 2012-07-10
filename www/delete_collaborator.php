<?php
session_start();
ini_set("display_errors", 1);
error_reporting(E_ALL | E_STRICT);

require("constants.php");
require("db.php");
require("helpers.php");

if (!isset($_GET["collaborator_id"]) ||
    !isset($_GET["album_id"]) ||
    !isset($_GET["album_token"])
    ) {
    exit();
} else {
    $collaborator_id = $_GET["collaborator_id"];
    $album_id = $_GET["album_id"];
    $album_token = $_GET["album_token"];
}


if (!check_token($album_id, $album_token, "Albums")) {
    print("0");
    exit();
}

$query = "DELETE FROM Collaborators WHERE collaborator_id='$collaborator_id' AND album_id='$album_id'";
$result = mysql_query($query, $con);
if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());

print("1");
exit();

?>
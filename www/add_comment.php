<?php

ini_set("display_errors", 1);
error_reporting(E_ALL | E_STRICT);

require("constants.php");
require("db.php");
require("helpers.php");

if (!isset($_POST["albumphoto_id"]) ||
    !isset($_POST["comment"]) ||
    !isset($_POST["token"]) ||
    !isset($_POST["album_id"]) ||
    !isset($_POST["commenter_id"])
    ) {
    print("0");
    exit();
} else {
    $albumphoto_id = $_POST["albumphoto_id"];
    $comment = mysql_real_escape_string($_POST["comment"]);
    $token = $_POST["token"];
    $album_id = $_POST["album_id"];
    $commenter_id = $_POST["commenter_id"];
}

if (!check_token($album_id, $token, "Albums")) {
    print("0");
    exit();
}


$query ="INSERT INTO Comments (
            albumphoto_id,
            comment,
            album_id,
            commenter_id
          ) VALUES (
            '$albumphoto_id',
            '$comment',
            '$album_id',
            '$commenter_id'
          )";
$result = mysql_query($query, $con);
if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . $query . " - " . mysql_error());



print("1");

?>
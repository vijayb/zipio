<?php

session_start();
ini_set("display_errors", 1);
error_reporting(E_ALL | E_STRICT);

require("db.php");
require("helpers.php");

if (!isset($_GET["email"]) || !isset($_GET["album_id"])) {
    exit();
} else {
    $email    = $_GET["email"];
    $album_id = $_GET["album_id"];
}

/** first, get the album_owner_id **/
$album_info = get_album_info($album_id);
$album_owner_id = $album_info["user_id"];


/** next, check if email is present in db **/
$query = "SELECT id FROM Users WHERE email='$email' LIMIT 1";
$result = mysql_query($query, $con);
if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());

if (mysql_num_rows($result) == 0) { // follower is not present

    $follower_id = create_user("", "", "", "", $email);
    login_user($follower_id);
    create_follower($album_owner_id, $follower_id, $album_id);
    print("0");
    return;
}

//if follower is present
    $row = mysql_fetch_array($result);
    $follower_id = $row["id"];
    login_user($follower_id);
    create_follower($album_owner_id, $follower_id, $album_id);
    print($row["id"]);
    return;

?>
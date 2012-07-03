<?php
session_start();
ini_set("display_errors", 1);
error_reporting(E_ALL | E_STRICT);

require("constants.php");
require("db.php");
require("helpers.php");

if (!isset($_GET["request"])) {
    exit();
}

$request = decrypt_json($_GET["request"]);

$query = "INSERT INTO Followers (
            follower_id,
            album_owner_id,
            album_id
          ) VALUES (
            '" . $request["follower_id"] . "',
            '" . $request["album_owner_id"] . "',
            '" . $request["album_id"] . "'
          ) ON DUPLICATE KEY UPDATE id=id";
$result = mysql_query($query, $con);
if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());


$album_owner_email_body = <<<EMAIL
    <b>{$request["follower_username"]}</b> is now following your <b>{$request["album_handle"]}</b> album.
EMAIL;

$album_owner_email_subject = $request["follower_username"] . " is now following your " . $request["album_handle"] . " album";
send_email($request["album_owner_email"], $g_founders_email_address, $album_owner_email_subject, $album_owner_email_body);

login_user($request["follower_id"]);

header("Location: " . $g_www_root . "/" . $request["album_owner_username"] . "/" . $request["album_handle"] . "#alert=1");

?>
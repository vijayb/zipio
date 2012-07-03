<?php

register_shutdown_function('handle_shutdown');
set_error_handler("on_error");

ini_set("display_errors", 1);
error_reporting(-1);

require("constants.php");
require("db.php");
require("helpers.php");

if (!isset($_POST["email"]) || !isset($_POST["password_hash"]) || !isset($_POST["album_id"])) {
    print("ERROR: A required argument wasn't set. email, password_hash, and album_id are required (as well as a photo).");
}

if ($_FILES["photo"]["tmp_name"] == "") {
    print("ERROR: A photo wasn't posted. The photo name should be 'photo'.");
    exit();
}

$email = strtolower($_POST["email"]);
$password_hash = strtolower($_POST["password_hash"]);
$target_album_id = $_POST["album_id"];

$target_album_info = get_album_info($target_album_id);
$target_user_info = get_user_info($target_album_info["user_id"]);

$query = "SELECT id FROM Users WHERE email_hash=UNHEX(SHA1('$email')) AND password_hash='$password_hash' LIMIT 1";
$result = mysql_query($query, $con);
if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());

$user_id = 0;

if ($row = mysql_fetch_assoc($result)) {
    $user_id = $row["id"];
} else {
    print("ERROR: Invalid username and password combo.");
    exit();
}

if (!class_exists('S3')) require_once 'S3.php';
if (!defined('awsAccessKey')) define('awsAccessKey', 'AKIAJXSDQXVDAE2Q2GFQ');
if (!defined('awsSecretKey')) define('awsSecretKey', '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');

$s3 = new S3(awsAccessKey, awsSecretKey);
$s3_url = ""; // Not used right now, but needed as an argument to add_albumphotos

$path_to_photo = $_FILES["photo"]["tmp_name"];

if ($target_album_info["user_id"] == $user_id) {
    // User owns the album
    if (add_albumphoto($user_id, $target_album_id, $target_user_info["id"], 1, $path_to_photo, $s3_url)) {
        print("1");
        exit();
    }
} else {
    $is_friend = is_friend($target_user_info["id"], $user_id);
    if ($is_friend) {
        // User is posting to a friend's album
        if (add_albumphoto($user_id, $target_album_id, $target_user_info["id"], 1, $path_to_photo, $s3_url)) {
            print("1");
            exit();
        }
    } else {
        // User is posting to a non-friend's album, so post it as invisible
        if (add_albumphoto($user_id, $target_album_id, $target_user_info["id"], 0, $path_to_photo, $s3_url)) {
            print("1");
            exit();
        }
    }
}

print("0");


?>
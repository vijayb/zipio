<?php
session_start();
ini_set("display_errors", 1);
error_reporting(E_ALL | E_STRICT);

require("db.php");
require("helpers.php");

if (!isset($_GET["email"]) || !isset($_GET["album_id"])) {
    exit();
} else {
    $email = mysql_real_escape_string($_GET["email"]);
    $album_id = mysql_real_escape_string($_GET["album_id"]);
}


if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    exit();
}

$album_info = get_album_info($album_id);
$album_owner_info = get_user_info($album_info["user_id"]);


$query = "SELECT id, username FROM Users WHERE email_hash=UNHEX(SHA1('$email')) LIMIT 1";
$result = mysql_query($query, $con);
if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());

if (mysql_num_rows($result) == 1) {
    $row = mysql_fetch_assoc($result);
    $follower_id = $row["id"];
    $follower_username = $row["username"];
} else {
    // CASE 3: Email doesn't exist, so create user
    $follower_username = generate_username($email);
    $follower_id = create_user($follower_username,
                               "",
                               $email);
}


// Send email with a link to confirm the follower relationship between the
// user and the album

$follow_album_ra = array();
$follow_album_ra["follower_id"] = $follower_id;
$follow_album_ra["follower_username"] = $follower_username;
$follow_album_ra["album_id"] = $album_id;
$follow_album_ra["album_handle"] = $album_info["handle"];
$follow_album_ra["album_owner_id"] = $album_owner_info["id"];
$follow_album_ra["album_owner_username"] = $album_owner_info["username"];
$follow_album_ra["album_owner_email"] = $album_owner_info["email"];
$follow_album_ra["timestamp"] = time();
$follow_album_link = $www_root . "/follow_album.php?request=" . urlencode(encrypt_json($follow_album_ra));

$follower_my_albums_page_link = $www_root . "/" . $follower_username;

$follower_email_body = <<<EMAIL
    <a href="$follow_album_link">Click here</a> to confirm that you want to follow <b>{$album_owner_info["email"]}</b>'s <b>{$album_info["handle"]}</b> album.
    <br><br>
    We've created a Zipio account for you. Your username is {$follower_username}, but you can <a href="{$follower_my_albums_page_link}">change it if you like</a>.
EMAIL;


send_email($email, $founders_email_address, "Confirm album following", $follower_email_body);



?>
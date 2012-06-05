<?php

ini_set("display_errors", 1);
error_reporting(E_ALL | E_STRICT);

require("db.php");
require("helpers.php");

debug("1");

if (!isset($_GET["token"])) {
    exit();
}

$request = decrypt_json($_GET["token"]);
print("<!--" . print_r($request, true) . "-->");

if ($request["action"] != "add_friend") {
    print_r($request);
    exit();
}

$user_id = $request["user_id"];
$target_user_id = $request["target_user_id"];

$album_info = get_album_info($request["album_id"]);

$query = "INSERT INTO Friends (
            user_id,
            friend_id
          ) VALUES (
            '$target_user_id',
            '$user_id'
          ), (
            '$user_id',
            '$target_user_id'
          ) ON DUPLICATE KEY UPDATE id=id";
$result = mysql_query($query, $con);
debug($query);
if (!$result) die('Invalid query: ' . $query . " - " . mysql_error());

// Now, all of user_id's photos that live in target_user_id's albums need to be made visible

$query = "UPDATE AlbumPhotos SET visible=1 WHERE photo_owner_id=$user_id AND album_owner_id=$target_user_id";
$result = mysql_query($query, $con);
if (!$result) die('Invalid query: ' . $query . " - " . mysql_error());

$user_info = get_user_info($user_id);
$target_user_info = get_user_info($target_user_id);

$output = <<<EMAIL
    You've added {$user_info["username"]} (that's {$user_info["email"]}) as a friend. You can now add to each other's albums.
EMAIL;

print($output);




$display_album_ta = array();
$display_album_ta["album_id"] = $request["album_id"];
$display_album_ta["action"] = "display_album";
$display_album_ta["timestamp"] = time();
$display_album_link = $www_root . "/display_album.php?token=" . urlencode(encrypt_json($display_album_ta));



$target_user_email_body = <<<EMAIL
    A reminder that you added <b>{$user_info["username"]}</b> (that's {$user_info["email"]}) as a friend.
    <br><br>
    You can now add photos to each other's albums.
EMAIL;


$user_email_body = <<<EMAIL
    <b>{$target_user_info["username"]}</b> (that's {$target_user_info["email"]}) added you as a friend.
    <br><br>
    Your photos now appear in <b>{$target_user_info["username"]}</b>'s {$album_info["handle"]} album.
    <a href='{$display_album_link}'>See album</a>
EMAIL;

send_email($target_user_info["email"], 'founders@zipio.com', "Zipio activity notification", $target_user_email_body);
send_email($user_info["email"], 'founders@zipio.com', "Zipio activity notification", $user_email_body);



?>
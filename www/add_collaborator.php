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


// The owner of the album who clicked the link that triggers this file
$user_id = $request["user_id"];

// The user to be added as a collaborator and the album for collaboration
$collaborator_id = $request["collaborator_id"];
$album_id = $request["album_id"];


$album_info = get_album_info($request["album_id"]);

create_collaborator($collaborator_id, $album_id);
add_friend($user_id, $collaborator_id);
add_friend($collaborator_id, $user_id);

// Now, all of collabortor_id's photos that live in target_user_id's albums need to be made visible

$query = "UPDATE AlbumPhotos SET visible=1 WHERE photo_owner_id=$collaborator_id AND album_id=$album_id";
$result = mysql_query($query, $con);
if (!$result) die('Invalid query: ' . $query . " - " . mysql_error());

$user_info = get_user_info($user_id);
$collaborator_info = get_user_info($collaborator_id);

$display_album_ra = array();
$display_album_ra["user_id"] = $collaborator_id;
$display_album_ra["token"] = $collaborator_info["token"];
$display_album_ra["timestamp"] = time();
$display_album_link = $g_www_root . "/" . $user_info["username"] . "/" . $album_info["handle"] . "?request=" . urlencode(encrypt_json($display_album_ra));


$collaborator_email_body = <<<EMAIL
    Good news, you can now add photos to <b>{$user_info["username"]}</b>'s (that's {$user_info["email"]}) <b>{$album_info["handle"]}</b> album.
    <br><br>
    Any photos you've already tried to add to the album are now in the album. <a href='{$display_album_link}'>See the album</a>.
EMAIL;

send_email($collaborator_info["email"], $g_founders_email_address, "$g_Zipio activity notification", $collaborator_email_body);

$url =  $g_www_root . "/" . $user_info["username"] . "/" . $album_info["handle"] . "#alert=3&email=" . $collaborator_info["email"];

login_user($user_id);

header("Location: $url");

?>
<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require("db.php");
require("helpers.php");

if (!class_exists('S3')) require_once 'S3.php';
if (!defined('awsAccessKey')) define('awsAccessKey', 'AKIAJXSDQXVDAE2Q2GFQ');
if (!defined('awsSecretKey')) define('awsSecretKey', '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');

$album_to_display = $_GET["album_id"];

print_r(get_photo_ids($album_to_display));


function get_photo_ids($album_id) {
    
    global $con;

    $query = "SELECT photo_id FROM AlbumPhotos WHERE album_id='$album_id'";
    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query: ' . mysql_error());
    
    $photos = array();
    
    while ($row = mysql_fetch_assoc($result)) {
        $photo = array();
        $photo["id"] = $row["photo_id"];
        $photo["user_id"] = get_owner_id($photo["id"]);
        array_push($photos, $photo);
    }
    
    return $photos;

}

function get_owner_id($photo_id) {

    global $con;

    $query = "SELECT user_id FROM Photos WHERE id='$photo_id'";
    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query: ' . mysql_error());
    
    if (mysql_num_rows($result) == 1) {
        while ($row = mysql_fetch_assoc($result)) {
            $user_id = $row["user_id"];
        }
    }

    return $user_id;
}





?>

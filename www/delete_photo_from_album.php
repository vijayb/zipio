<?php

ini_set("display_errors", 1);
error_reporting(E_ALL | E_STRICT);

require("constants.php");
require("db.php");
require("helpers.php");

if (!isset($_GET["albumphoto_id"]) ||
    !isset($_GET["token"]) ||
    !isset($_GET["s3"])) {
    exit();
} else {
    $albumphoto_id = $_GET["albumphoto_id"];
    $token = $_GET["token"];
    $s3_handle = $_GET["s3"];
}

if (!check_token($albumphoto_id, $token, "AlbumPhotos")) {
    print("0");
    exit();
}

$albumphoto_info = get_albumphoto_info($albumphoto_id);

$query = "DELETE FROM AlbumPhotos WHERE photo_id='" . $albumphoto_info["photo_id"] . "' AND album_id='" . $albumphoto_info["album_id"] . "'";
$result = mysql_query($query, $con);
if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());

$album_info = get_album_info($albumphoto_info["album_id"]);
$cover_albumphoto_id = $album_info["cover_albumphoto_id"];

if ($cover_albumphoto_id == $albumphoto_id) {
    $new_cover_albumphoto_id = $album_info["albumphoto_ids"][0];
    update_data("Albums", $albumphoto_info["album_id"], array("cover_albumphoto_id" => $new_cover_albumphoto_id));
}




if (!class_exists('S3')) require_once 'S3.php';
if (!defined('awsAccessKey')) define('awsAccessKey', 'AKIAJXSDQXVDAE2Q2GFQ');
if (!defined('awsSecretKey')) define('awsSecretKey', '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');
$s3 = new S3(awsAccessKey, awsSecretKey);

global $g_s3_bucket_name;
global $g_s3_folder_name;


$s3->deleteObject($g_s3_bucket_name, $g_s3_folder_name . "/" . $s3_handle . "_big");
$s3->deleteObject($g_s3_bucket_name, $g_s3_folder_name . "/" . $s3_handle . "_big_filtered");
$s3->deleteObject($g_s3_bucket_name, $g_s3_folder_name . "/" . $s3_handle . "_cropped");
$s3->deleteObject($g_s3_bucket_name, $g_s3_folder_name . "/" . $s3_handle . "_cropped_filtered");

print("1");

?>
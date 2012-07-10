<?php

require("constants.php");
require("db.php");
require("helpers.php");
require "S3.php";

if (!class_exists('S3')) require_once 'S3.php';
if (!defined('awsAccessKey')) define('awsAccessKey', 'AKIAJXSDQXVDAE2Q2GFQ');
if (!defined('awsSecretKey')) define('awsSecretKey', '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');
$s3 = new S3(awsAccessKey, awsSecretKey);


$matches = array();
if (!preg_match("/([0-9]+_[0-9]+[^\&]+)/", $_POST["cropped_image_src"], $matches)) {
    print("********* ERROR *********");
    exit();
}
$s3_name = $matches[0] . "_filtered";

$image_data = base64_decode($_POST["cropped_image_data"]);

if (!$s3->putObject($image_data,
                    $g_s3_bucket_name,
                    "$g_s3_folder_name/" . $s3_name,
                    S3::ACL_PUBLIC_READ,
                    array(),
                    array("Content-Type" => "image/jpeg"))) {
}


$matches = array();
if (!preg_match("/([0-9]+_[0-9]+[^\&]+)/", $_POST["big_image_src"], $matches)) {
    print("********* ERROR *********");
    exit();
}
$s3_name = $matches[0] . "_filtered";

$image_data = base64_decode($_POST["big_image_data"]);

if (!$s3->putObject($image_data,
                    $g_s3_bucket_name,
                    "$g_s3_folder_name/" . $s3_name,
                    S3::ACL_PUBLIC_READ,
                    array(),
                    array("Content-Type" => "image/jpeg"))) {
}


update_data("AlbumPhotos", $_POST["albumphoto_id"], array("filtered" => "1"));


?>
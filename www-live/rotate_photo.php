<?php

ini_set("display_errors", 1);
error_reporting(E_ALL | E_STRICT);

require("constants.php");
require("db.php");
require("helpers.php");

if (!isset($_GET["clockwise"]) ||
    !isset($_GET["albumphoto_id"]) ||
    !isset($_GET["album_id"]) ||
    !isset($_GET["albumphoto_token"]) ||
    !isset($_GET["s3_handle"])) {
    print("0");
    exit();
} else {
    if ($_GET["clockwise"] == "1") {
        $rotate_angle = 90;
    } else {
        $rotate_angle = -90;
    }

    $albumphoto_id = $_GET["albumphoto_id"];
    $album_id = $_GET["album_id"];
    $albumphoto_token = $_GET["albumphoto_token"];
    $s3_handle = $_GET["s3_handle"];
}

if (!check_token($albumphoto_id, $albumphoto_token, "AlbumPhotos")) {
    print("0");
    exit();
}

$albumphoto_info = get_albumphoto_info($albumphoto_id);


if (!class_exists('S3')) require_once 'S3.php';
if (!defined('awsAccessKey')) define('awsAccessKey', 'AKIAJXSDQXVDAE2Q2GFQ');
if (!defined('awsSecretKey')) define('awsSecretKey', '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');
$s3 = new S3(awsAccessKey, awsSecretKey);



$width = 0;
$height = 0;

rotate_s3_image($s3, $s3_handle . "_big", $rotate_angle, $width, $height);
update_data("Photos", $albumphoto_info["photo_id"], array("width" => $width, "height" => $height));



rotate_s3_image($s3, $s3_handle . "_cropped", $rotate_angle, $width, $height);

if ($albumphoto_info["filtered"] == "1") {
  rotate_s3_image($s3, $s3_handle . "_big_filtered", $rotate_angle, $width, $height);
  rotate_s3_image($s3, $s3_handle . "_cropped_filtered", $rotate_angle, $width, $height);
}


print("1");



function rotate_s3_image($s3, $s3_name, $rotate_angle, &$width, &$height) {
  global $g_s3_root;
  global $g_s3_bucket_name;
  global $g_s3_folder_name;

  $image_data = file_get_contents($g_s3_root . "/" . $s3_name);
  
  $image= new Imagick();
  $image->readImageBlob($image_data);
  $image->rotateImage(new ImagickPixel('none'), $rotate_angle); 
  $image_data = $image->getImageBlob();

  $width = $image->getImageWidth();
  $height = $image->getImageHeight();
  
  if (!$s3->putObject($image_data,
		      $g_s3_bucket_name,
		      "$g_s3_folder_name/" . $s3_name,
		      S3::ACL_PUBLIC_READ,
                        array(),
		      array("Content-Type" => "image/jpeg"))) {
    print("0");
    exit();
  }
}

?>
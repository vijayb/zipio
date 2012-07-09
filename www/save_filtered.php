<?php

require "helpers.php";
require "S3.php";

$filename = SHA1($_POST["imageSrc"]);

$s3_name = $_POST["imageSrc"]."_filtered";

$imageData = base64_decode($_POST["imageData"]);

//saveFilteredImageToS3($imageData, $s3_name);


$myFile = "/tmp/image_$filename.png";
$fh = fopen($myFile, 'w') or die("can't open file");
$stringData = base64_decode($_POST["imageData"]);
fwrite($fh, $stringData);
fclose($fh);

?>
<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');


// First, check if this user exists



fwrite($f, print_r($GLOBALS, true));

$uploads_dir = 'attachments';
$name = $_FILES["attachment-1"]["name"];
$tmp_name = $_FILES["attachment-1"]["tmp_name"];

move_uploaded_file($tmp_name, "$uploads_dir/$name");

?>

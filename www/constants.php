<?php

/*

BEFORE PUSHING:

- Compile LESS and use static CSS in static_top.php (if you need to)

To delete the database:

    TRUNCATE TABLE Zipiyo.AlbumPhotos;
    TRUNCATE TABLE Zipiyo.Collaborators;
    TRUNCATE TABLE Zipiyo.Albums;
    TRUNCATE TABLE Zipiyo.Photos;
    TRUNCATE TABLE Zipiyo.Users;

To run ec2 tools
    http://blog.bottomlessinc.com/2010/12/installing-the-amazon-ec2-command-line-tools-to-launch-persistent-instances/

*/

if ($_SERVER["HTTP_HOST"] == "localhost" || $_SERVER["HTTP_HOST"] == "zipiyo.com") {
    $g_debug = 1;
    $g_zipio = "zipiyo";
    $g_Zipio = "Zipiyo";
    $g_database_to_use = "TEST";
    $g_fb_app_id = "255929901188660";
} else {
    $g_debug = 0;
    $g_zipio = "zipio";
    $g_Zipio = "Zipio";
    $g_database_to_use = "PROD";
    $g_fb_app_id = "457795117571468";
}

if ($_SERVER["HTTP_HOST"] == "localhost") {
    //$g_database_to_use = "LOCAL";
}

$g_s3_bucket_name = "s3.$g_zipio.com";
$g_s3_folder_name = "photos";
$g_s3_root = "http://$g_s3_bucket_name/$g_s3_folder_name";
$g_www_root = "http://" . $_SERVER["HTTP_HOST"];
$g_founders_email_address = "$g_Zipio <founders@$g_zipio.com>";


?>
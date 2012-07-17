<?php

/*

BEFORE PUSHING:

- Compile LESS and use static CSS in static_top.php
- Change $g_debug from 1 to 0 in constants.php
- Change $g_database from TEST to PROD in constants.php
- Change $g_zipio to zipio

    TRUNCATE TABLE AlbumPhotos;
    TRUNCATE TABLE Collaborators;
    TRUNCATE TABLE Albums;
    TRUNCATE TABLE Photos;
    TRUNCATE TABLE Users;

To run ec2 tools:
http://blog.bottomlessinc.com/2010/12/installing-the-amazon-ec2-command-line-tools-to-launch-persistent-instances/

*/


$g_zipio = "zipiyo";
$g_Zipio = "Zipiyo";
$g_s3_bucket_name = "s3.$g_zipio.com";
$g_s3_folder_name = "photos";
$g_s3_root = "http://$g_s3_bucket_name/$g_s3_folder_name";
$g_www_root = "http://" . $_SERVER["HTTP_HOST"];
$g_founders_email_address = "$g_Zipio <founders@$g_zipio.com>";


// When set to 1, certain debug statements are turned ON. This must be set to
// 0 before pushing to production because the debug statements might reveal
// information that is sensitive.

$g_debug = 1;



// When set to "PROD", the system uses the database at zipio.com. When set to
// "TEST", the system uses the database at zipiyo.com

$g_database_to_use = "TEST";



if ($_SERVER["HTTP_HOST"] == "localhost") {
    $g_debug = 1;
}

$g_album_privacy_contants[1] = "<i class='icon-lock' style='color:red;'></i> Private";
$g_album_privacy_contants[2] = "<i class='icon-group' style='color:orange;'></i> Friends";
$g_album_privacy_contants[3] = "<i class='icon-globe' style='color:green;'></i> Public";

?>
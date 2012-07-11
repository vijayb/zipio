<?php

/*

1. Compile LESS and use static CSS in static_top.php
2. Change $g_debug from 1 to 0 in constants.php
3. Change $g_database from TEST to PROD in constants.php
4. Change zipiyo to zipio in process_email.php (used in preg_match at least twice)
5. Change the $g_s3_folder_name variable in constants.php from photos_test to photos

    TRUNCATE TABLE AlbumPhotos;
    TRUNCATE TABLE Collaborators;
    TRUNCATE TABLE Albums;
    TRUNCATE TABLE Photos;
    TRUNCATE TABLE Users;

*/


$g_s3_bucket_name = "s3.zipio.com";
$g_s3_folder_name = "photos_test";
$g_s3_root = "http://s3.zipio.com/$g_s3_folder_name";
$g_www_root = "http://" . $_SERVER["HTTP_HOST"];
$g_founders_email_address = "Zipio <founders@zipio.com>";

// When set to 1, certain debug statements are turned ON. This must be set to
// 0 before pushing to production because the debug statements might reveal
// information that is sensitive.

$g_debug = 1;



// When set to "PROD", the system uses the database at zipio.com. When set to
// "TEST", the system uses the database at ec2-23-22-14-153.compute-1.amazonaws.com

$g_database_to_use = "TEST";



if ($_SERVER["HTTP_HOST"] == "localhost") {
    $g_debug = 1;
}

$g_album_privacy_contants[1] = "<i class='icon-lock' style='color:red;'></i> Private";
$g_album_privacy_contants[2] = "<i class='icon-group' style='color:orange;'></i> Friends";
$g_album_privacy_contants[3] = "<i class='icon-globe' style='color:green;'></i> Public";

?>
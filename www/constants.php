<?php

/*

1. Compile LESS and use static CSS in static_top.php
2. Change $g_debug from 1 to 0 in constants.php
3. Change $g_database from TEST to PROD in constants.php

    TRUNCATE TABLE AlbumPhotos;
    TRUNCATE TABLE AlbumAccessors;
    TRUNCATE TABLE Albums;
    TRUNCATE TABLE Friends;
    TRUNCATE TABLE Photos;
    TRUNCATE TABLE Users;
    TRUNCATE TABLE Followers;

*/


$g_s3_bucket_name = "s3.zipio.com";
$g_s3_root = "http://s3.zipio.com/photos";
$g_www_root = "http://" . $_SERVER["HTTP_HOST"];
$g_founders_email_address = "Zipio <founders@zipio.com>";

$g_debug = 0;
$g_database_to_use = "PROD";

$g_album_privacy_contants[1] = "<i class='icon-lock' style='color:red;'></i> Private";
$g_album_privacy_contants[2] = "<i class='icon-group' style='color:orange;'></i> Friends";
$g_album_privacy_contants[3] = "<i class='icon-globe' style='color:green;'></i> Public";

?>
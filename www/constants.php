<?php

/*

BEFORE PUSHING:

- Compile LESS and use static CSS in static_top.php (if you need to)

To delete the database:

    TRUNCATE TABLE Zipiyo.AlbumPhotos;
    TRUNCATE TABLE Zipiyo.Collaborators;
    TRUNCATE TABLE Zipiyo.Comments;
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

    if ($_SERVER["HTTP_HOST"] == "zipiyo.com") {
        $g_database_to_use = "TEST";
    } else if ($_SERVER["HTTP_HOST"] == "localhost") {
        $g_database_to_use = "LOCAL";
    }

    $g_fb_app_id = "255929901188660";
} else {
    $g_debug = 0;
    $g_zipio = "zipio";
    $g_Zipio = "Zipio";
    $g_database_to_use = "PROD";
    $g_fb_app_id = "457795117571468";
}


$g_s3_bucket_name = "s3.$g_zipio.com";
$g_s3_folder_name = "photos";
$g_s3_root = "http://$g_s3_bucket_name/$g_s3_folder_name";
$g_www_root = "http://" . $_SERVER["HTTP_HOST"];
$g_founders_email_address = "$g_Zipio <founders@$g_zipio.com>";

if (!defined("ACTION_ADD_ALBUM")) {
    define("ACTION_ADD_ALBUM", 1);
    define("ACTION_ADD_ALBUMPHOTO", 2);
    define("ACTION_ADD_COMMENT", 3);
    define("ACTION_LIKE_ALBUM", 4);
    define("ACTION_LIKE_ALBUMPHOTO", 5);
    define("ACTION_LIKE_COMMENT", 6);
    define("ACTION_EDIT_CAPTION", 7);
    define("ACTION_EDIT_COMMENT", 8);
    define("ACTION_DELETE_ALBUM",9);
    define("ACTION_DELETE_ALBUMPHOTO",10);
    define("ACTION_DELETE_COMMENT",11);
    define("ACTION_ROTATE_ALBUMPHOTO",12);
    define("ACTION_FILTER_ALBUMPHOTO",13);
    define("ACTION_CHANGE_ALBUM_COVER",14);
}


?>
<?php

ini_set("display_errors", 1);
error_reporting(E_ALL | E_STRICT);

require("db.php");
require("helpers.php");

print_r($_POST);
print("<br><br>");

$sender = $_POST["sender"];
$recipient = $_POST["recipient"];

if (!class_exists('S3')) require_once 'S3.php';
if (!defined('awsAccessKey')) define('awsAccessKey', 'AKIAJXSDQXVDAE2Q2GFQ');
if (!defined('awsSecretKey')) define('awsSecretKey', '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');

$s3 = new S3(awsAccessKey, awsSecretKey);
$s3_root = "https://s3.amazonaws.com/zipio_photos";
$www_root = "http://localhost";

// First, check if this user exists

$brand_new_user = 0;

$query = "SELECT id FROM Users WHERE email='$sender'";
$result = mysql_query($query, $con);
if (!$result) die('Invalid query: ' . mysql_error());

if (mysql_num_rows($result) == 1) {
    // A user with this email already exists, so get the user's ID
    while ($row = mysql_fetch_assoc($result)) {
        $user_id = $row["id"];
    }
    debug("user_id: " . $user_id);
    debug("User with id $user_id already exists prior to this post.");
} else {
    // New user! Create a new row in the Users table and get the ID of the newly
    // created row.

    $brand_new_user = 1;

    $usercode = generate_usercode(5);
    $query = "INSERT INTO Users (
                email,
                usercode,
                username
              ) VALUES (
                '$sender',
                '$usercode',
                '$usercode'
              ) ON DUPLICATE KEY UPDATE last_seen=UTC_TIMESTAMP()";
    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query: ' . $query . " - " . mysql_error());

    $user_id = mysql_insert_id();

    debug("New user added with id $user_id.");

}

$parts = explode('@', $recipient);
$target_album_handle = $parts[0];
$recipient_domain = $parts[1];

debug("target_album_handle: " . $target_album_handle . "\n");

$path_to_photo = $_FILES["attachment-1"]["tmp_name"];




// Check if target user is specified explicitly (e.g., ...@alex.zipio.com)
if (preg_match("/(.+)\.zipiyo\.com/", $recipient_domain, $matches)) {
    $target_userstring = $matches[1];
    debug("target_userstring: " . $target_userstring . "\n");
    $target_user_id = get_user_id_from_userstring($target_userstring);
} else {
    $target_user_id = $user_id;
}






$target_album_id = album_exists($target_album_handle, $target_user_id);
$target_user_info = get_user_info($target_user_id);
$user_info = get_user_info($user_id);


// At this point, we have:
//  $user_id
//      The ID of the user who submitted the photo
//  $target_album_id
//      The ID of the album to add the photo to IF IT EXISTS. If it doesn't
//      exist, this variable is -1 and a new album will be created later.

// The different ways to add a photo:
//  Add to own existing album
//  Add to own new album
//  Add to friend's album
//  Add to not-yet-friend's album

if ($target_album_id > 0) {
    // The target album exists (and so does the target user)...
    $target_album_info = get_album_info($target_album_id);

    debug("Target user ($target_user_id, " . $target_user_info["username"] . ") exists.", "green");
    debug("Target album ($target_album_handle, owned by " . $target_user_info["username"] . ") exists.", "green");
    debug("Target album has ID $target_album_id");

    if ($target_user_id == $user_id) {
        // User is adding to own album
        debug("User is adding to his own album.");
        add_photo($user_id, $target_album_id, 1, $path_to_photo);

        $user_email_body = <<<EMAIL
        You added a photo to your {$target_album_info["handle"]} album.
        <a href='{$www_root}/display_album.php?album_id={$target_album_id}'>See album</a>
EMAIL;
        debug($user_email_body);

    } else {
        // User is adding to another user's album, so check if the submitter of the photo has permission
        $has_write_permission = has_write_permission($target_user_id, $user_id);
        if ($has_write_permission == 1) {
            debug("User " . $user_info["username"] . " has permission to write to " . $target_user_info["username"] . "'s " . $target_album_info["handle"] . ".", "green");
            // Add the photo to the friend's album
            add_photo($user_id, $target_album_id, 1, $path_to_photo);
            $user_email_body = "You added a photo to " . $target_user_info["username"] . "'s " . $target_album_info["handle"] . " album. <a href='$www_root/display_album.php?album_id=$target_album_id'>See album</a>";
            debug($user_email_body);

        } else if ($has_write_permission == 0) {
            debug("User " . $user_info["username"] . " does note have permission to write to " . $target_user_info["username"] . "'s " . $target_album_info["handle"] . ".", "red");
            // Add photo as invisible and send an email to the owner
            add_photo($user_id, $target_album_id, 0, $path_to_photo);


            $user_email_body = <<<EMAIL
            You tried to add a photo to {$target_user_info["username"]}'s (that's {$target_user_info["email"]}) {$target_album_info["handle"]} album.
            Your photo will appear in the album once {$target_user_info["username"]} approves you.
            <a href='$www_root/display_album.php?album_id=$target_album_id'>See album</a>
EMAIL;
            debug($user_email_body);


            $target_email_body = <<<EMAIL
            {$user_info["email"]} tried to post a photo to your {$target_album_info["handle"]} album.
            Is that okay?
            <a href='$www_root/grant_write_permission.php?album_id={$target_album_id}&album_token={$target_album_info["token"]}&user_id={$user_info["id"]}&user_token={$user_info["token"]}'>Yes</a>
            <a href=''>No</a>

EMAIL;
            debug($target_email_body);

        }
    }

} else if ($target_album_id == 0) {
    // The album doesn't exist but the user does...

    debug("Target user ($target_user_id, " . $target_user_info["username"] . ") exists.", "green");
    debug("Target album ($target_album_handle) does not exist.", "red");

    if ($target_user_id == $user_id) {
        // Create an album
        debug("User is attempting to create own album.");
        $target_album_id = create_album($user_id, $target_album_handle);
        $target_album_info = get_album_info($target_album_id);
        add_photo($user_id, $target_album_id, 1, $path_to_photo);

        $user_email_body = <<<EMAIL
        You created a new album called {$target_album_info["handle"]}.
        <a href='{$www_root}/display_album.php?album_id={$target_album_id}'>See album</a>
EMAIL;
        debug($user_email_body);

    } else {
        // A user cannot create an album for another user, so disallow
        debug("User is attempting to create album for another user.", "red");
    }
} else if ($target_album_id == -1) {
    debug("Target user ($target_user_id) does not exist.", "red");
}





// send_email('sanjay@gmail.com', 'founders@zipio.com', 'My Subject', "hello");








/*
$uploads_dir = 'attachments';
$name = $_FILES["attachment-1"]["name"];
$tmp_name = $_FILES["attachment-1"]["tmp_name"];

move_uploaded_file($tmp_name, "$uploads_dir/$name");


TRUNCATE TABLE AlbumPhotos;# MySQL returned an empty result set (i.e. zero rows).
TRUNCATE TABLE Permissions;
TRUNCATE TABLE Albums;# MySQL returned an empty result set (i.e. zero rows).
TRUNCATE TABLE Friends;# MySQL returned an empty result set (i.e. zero rows).
TRUNCATE TABLE Photos;# MySQL returned an empty result set (i.e. zero rows).
TRUNCATE TABLE Users;# MySQL returned an empty result set (i.e. zero rows).



*/
// service request number: 856884051
// 800-624-9896










?>

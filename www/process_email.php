<?php

/*

    TRUNCATE TABLE AlbumPhotos;
    TRUNCATE TABLE Albums;
    TRUNCATE TABLE Friends;
    TRUNCATE TABLE Photos;
    TRUNCATE TABLE Users;
    TRUNCATE TABLE Followers;

*/

ob_start();

ini_set("display_errors", 1);
error_reporting(E_ALL | E_STRICT);

require("db.php");
require("helpers.php");

print("POST REQUEST:");
print_r($_POST);
print("<br><br>");

$sender = $_POST["sender"];
$recipient = $_POST["recipient"];

if (!filter_var($sender, FILTER_VALIDATE_EMAIL) || !filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
    exit();
}

$confirmation_number = rand_string(5);

if (!class_exists('S3')) require_once 'S3.php';
if (!defined('awsAccessKey')) define('awsAccessKey', 'AKIAJXSDQXVDAE2Q2GFQ');
if (!defined('awsSecretKey')) define('awsSecretKey', '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');

$s3 = new S3(awsAccessKey, awsSecretKey);



// First, check if this user exists

$brand_new_user = 0;

$query = "SELECT id FROM Users WHERE email='$sender' LIMIT 1";
$result = mysql_query($query, $con);
if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());

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

    $usercode = generate_username($sender);
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
    if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . $query . " - " . mysql_error());

    $user_id = mysql_insert_id();

    debug("New user added with id $user_id.");

}








$parts = explode('@', $recipient);
$target_album_handle = $parts[0];
$recipient_domain = $parts[1];

debug("target_album_handle: " . $target_album_handle . "\n");

// Get the attached photos
$paths_to_photos = array();
for ($i = 0; $i < $num_photos_attached = $_POST["attachment-count"]; $i++) {
    array_push($paths_to_photos, $_FILES["attachment-" . ($i + 1)]["tmp_name"]);
}

// Stores the S3 URLs of the photos afters they are added to S3
$s3_urls = array();

// Check if target user is specified explicitly (e.g., vacation@alex.zipio.com)
if (preg_match("/(.+)\.zipio\.com/", $recipient_domain, $matches)) {
    $target_userstring = $matches[1];
    debug("target_userstring: " . $target_userstring . "\n");
    $target_user_id = get_user_id_from_userstring($target_userstring);
} else {
    $target_user_id = $user_id;
}


$target_album_id = album_exists($target_album_handle, $target_user_id);
$target_user_info = get_user_info($target_user_id);
$user_info = get_user_info($user_id);


debug("target_album_id: " . $target_album_id);
debug("target_user_info: ");
print_r($target_user_info);
debug("user_info: ");
print_r($user_info);



// At this point, we have:
//  $user_info
//      The info of the user who submitted the photo
//  $target_user_info
//      The info of the user whose album the user is trying to write to. This
//      is set even if the user is submitting to his own album.
//  $target_album_id
//      The ID of the album to add the photo to IF IT EXISTS. If it doesn't
//      exist but the user exists, this variable is 0. If the album doesn't
//      exist AND the user doesn't exist, this variable is -1.


// The different ways to add a photo:
//  Add to own existing album
//  Create a new album and add a photo to it
//  Add to friend's album
//  Add to not-yet-friend's album


if ($target_album_id > 0) {
    // The target album exists (and so does the target user)...
    $target_album_info = get_album_info($target_album_id);

    debug("Target user ($target_user_id, " . $target_user_info["username"] . ") exists.", "green");
    debug("Target album ($target_album_handle, owned by " . $target_user_info["username"] . ") exists.", "green");
    debug("Target album has ID $target_album_id");

    if ($target_user_id == $user_id) {
        // User is adding a photo to own existing album
        debug("User is adding to his own album.");
        for ($i = 0; $i < $num_photos_attached = $_POST["attachment-count"]; $i++) {
            $s3_url = "";
            add_photo($user_id, $target_album_id, $target_user_id, 1, $paths_to_photos[$i], $s3_url);
            array_push($s3_urls, $s3_url);
        }
        email_followers($target_album_info, $s3_urls);

        $display_album_ra = array();
        $display_album_ra["user_id"] = $user_info["id"];
        $display_album_ra["timestamp"] = time();
        $display_album_link = $www_root . "/" . $target_user_info["username"] . "/" . $target_album_info["handle"] . "?request=" . urlencode(encrypt_json($display_album_ra)) . "#register=true";

        $user_email_body = <<<EMAIL
            You added a photo to your <b>{$target_album_info["handle"]}</b> album.
            <a href='{$display_album_link}'>See the album!</a>
            <br><br>
            To add more photos, email them to <b>{$target_album_info["handle"]}@{$user_info["username"]}.zipio.com</b>. Anyone can add photos, so share this email address! (We'll ask you to approve anyone who tries to add photos.)

EMAIL;

    } else {
        // User is adding to another user's album, so check if the submitter of the photo is a friend of the target user

        $is_friend = is_friend($target_user_id, $user_id);
        if ($is_friend == 1) {
            debug("User " . $user_info["username"] . " is a friend of " . $target_user_info["username"], "green");
            // Add the photo to the friend's album
            for ($i = 0; $i < $num_photos_attached = $_POST["attachment-count"]; $i++) {
                $s3_url = "";
                add_photo($user_id, $target_album_id, $target_user_id, 1, $paths_to_photos[$i], $s3_url);
                array_push($s3_urls, $s3_url);
            }
            email_followers($target_album_info, $s3_urls);

            $display_album_ra = array();
            $display_album_ra["user_id"] = $user_info["id"];
            $display_album_ra["timestamp"] = time();
            $display_album_link = $www_root . "/" . $target_user_info["username"] . "/" . $target_album_info["handle"] . "?request=" . urlencode(encrypt_json($display_album_ra)) . "#register=true";



            $user_email_body = <<<EMAIL
                You added a photo to {$target_user_info["username"]}'s <b>{$target_album_info["handle"]}</b> album.
                <a href='{$display_album_link}'>See the album!</a>
EMAIL;

            $target_user_email_body = <<<EMAIL
                {$user_info["email"]} added a photo to your {$target_album_info["handle"]} album.
                 <a href='{$display_album_link}'>See the album!</a>
EMAIL;




        } else if ($is_friend == 0) {
            debug("User " . $user_info["username"] . " is not a friend of " . $target_user_info["username"], "red");
            // Add photo as invisible and send an email to the owner
            for ($i = 0; $i < $num_photos_attached = $_POST["attachment-count"]; $i++) {
                $s3_url = "";
                add_photo($user_id, $target_album_id, $target_user_id, 0, $paths_to_photos[$i], $s3_url);
                array_push($s3_urls, $s3_url);
            }
            // email_followers($target_album_info, $s3_urls);

            $display_album_ra = array();
            $display_album_ra["user_id"] = $user_info["id"];
            $display_album_ra["timestamp"] = time();
            $display_album_link = $www_root . "/" . $target_user_info["username"] . "/" . $target_album_info["handle"] . "?request=" . urlencode(encrypt_json($display_album_ra))  . "#register=true";

            $user_email_body = <<<EMAIL
                You tried to add a photo to <b>{$target_user_info["username"]}</b>'s (that's {$target_user_info["email"]}) <b>{$target_album_info["handle"]}</b> album.
                <br><br>
                Your photo will appear in the album once <b>{$target_user_info["username"]}</b> approves you as a friend.
                <a href='{$display_album_link}'>See the album!</a>
EMAIL;

            $add_friend_ra = array();
            $add_friend_ra["user_id"] = $user_info["id"];
            $add_friend_ra["target_user_id"] = $target_user_info["id"];
            $add_friend_ra["album_id"] = $target_album_id;
            $add_friend_ra["action"] = "add_friend";
            $add_friend_ra["timestamp"] = time();
            $add_friend_link = $www_root . "/add_friend.php?request=" . urlencode(encrypt_json($add_friend_ra));

            $target_user_email_body = <<<EMAIL
                {$user_info["email"]} tried to post a photo to your <b>{$target_album_info["handle"]}</b> album.
                Add as a friend?
                <a href='{$add_friend_link}'>Yes</a>
                <a href='#'>No</a>

EMAIL;

        }
    }

} else if ($target_album_id == 0) {
    // The album doesn't exist but the user does...

    debug("Target user ($target_user_id, " . $target_user_info["username"] . ") exists.", "green");
    debug("Target album ($target_album_handle) does not exist.", "red");

    if ($target_user_id == $user_id) {
        // User is creating a new album and adding a photo to it
        debug("User is attempting to create own album.");
        $target_album_id = create_album($user_id, $target_album_handle);
        $target_album_info = get_album_info($target_album_id);

        for ($i = 0; $i < $num_photos_attached = $_POST["attachment-count"]; $i++) {
            $s3_url = "";
            $current_photo_id = add_photo($user_id, $target_album_id, $target_user_id, 1, $paths_to_photos[$i], $s3_url);
            array_push($s3_urls, $s3_url);
            if ($i == 0) {
                // Set the first photo as the cover photo
                debug("current_photo_id: $current_photo_id");
                update_data("Albums", $target_album_id, array("cover_photo_id" => $current_photo_id));
            }
        }
        email_followers($target_album_info, $s3_urls);

        $display_album_ra = array();
        $display_album_ra["user_id"] = $user_info["id"];
        $display_album_ra["timestamp"] = time();
        $display_album_link = $www_root . "/" . $target_user_info["username"] . "/" . $target_album_info["handle"] . "?request=" . urlencode(encrypt_json($display_album_ra)) . "#register=true";

        $user_email_body = <<<EMAIL
            You created a new album called <b>{$target_album_info["handle"]}</b>.
            <a href='{$display_album_link}'>See the album!</a>
            <br><br>
            To add more photos, email them to <b>{$target_album_info["handle"]}@{$user_info["username"]}.zipio.com</b>. Anyone can add photos, so share this email address! (We'll ask you to approve anyone who tries to add photos.)
EMAIL;

    } else {
        // A user cannot create an album for another user, so disallow
        debug("User is attempting to create album for another user.", "red");

        $user_email_body = <<<EMAIL
            You tried to add a photo to {$target_user_info["username"]}'s {$target_album_info["handle"]} album, but {$target_user_info["username"]} doesn't have an album by that name!
EMAIL;


    }
} else if ($target_album_id == -1) {
    debug("Target user ($target_user_id) does not exist.", "red");
}


if ($brand_new_user) {

    $user_email_body = "Welcome to Zipio! We've assigned you a username of <b>" . $user_info["username"] . "</b>." .  $user_email_body;
}

if (!preg_match("/zipio.com$/", $sender)) {
    send_email($user_info["email"], 'founders@zipio.com', "Zipio activity notification", $user_email_body);
    if (isset($target_user_email_body)) {
        send_email($target_user_info["email"], 'founders@zipio.com', "Zipio activity notification", $target_user_email_body);
        debug($target_user_email_body);
    }
}

debug($user_email_body);


$contents = ob_get_flush();

if (!preg_match("/zipio.com$/", $sender)) {
    send_email("sanjay@gmail.com", 'founders@zipio.com', $confirmation_number . " - process_email", $contents);
}

?>
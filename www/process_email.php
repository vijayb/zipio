<?php


ini_set("display_errors", 1);
error_reporting(-1);

require("constants.php");
require("db.php");
require("helpers.php");

if (isset($_POST["sender"]) && isset($_POST["recipient"])) {
    $sender = strtolower($_POST["sender"]);
    $recipient = strtolower($_POST["recipient"]);
} else {
    $sender = "";
    $recipient = "";
}

$date = gmdate("d-M-Y H:i:s");
$output_handle = fopen("/log/" . $date . "_" . $sender, 'a+') or die('Cannot open file.');


register_shutdown_function('handle_shutdown');
set_error_handler("on_error", -1);


$name = $_POST["from"];
$name = str_replace('"', "", $name);
$name = str_replace("'", "", $name);

$pattern = "/<.*>/";
$name = preg_replace($pattern, "", $name);
print($name);



output("POST REQUEST:\n");

output(print_r($_POST, true) . "\n");


$start_time = time();
output("TIME 1: " . (time() - $start_time) . "\n");

if (!filter_var($sender, FILTER_VALIDATE_EMAIL) || !filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
    exit();
}

$confirmation_number = rand_string(5);

if (!class_exists('S3')) require_once 'S3.php';
if (!defined('awsAccessKey')) define('awsAccessKey', 'AKIAJXSDQXVDAE2Q2GFQ');
if (!defined('awsSecretKey')) define('awsSecretKey', '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');

$s3 = new S3(awsAccessKey, awsSecretKey);


// First, check if the user who SENT the email is an existing Zipio user

$brand_new_user = 0;

$query = "SELECT id FROM Users WHERE email='$sender' LIMIT 1";
$result = mysql_query($query, $con);
if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());

output("TIME 2: " . (time() - $start_time) . "\n");

if (mysql_num_rows($result) == 1) {
    // A user with this email already exists, so get the user's ID
    while ($row = mysql_fetch_assoc($result)) {
        $user_id = $row["id"];
    }
    output("user_id: $user_id\n");
    output("User with id $user_id already exists prior to this post.\n");
} else {
    // New user! Create a new row in the Users table and get the ID of the newly
    // created row.

    $brand_new_user = 1;

    $username = generate_username($sender);
    $query = "INSERT INTO Users (
                name,
                email,
                username
              ) VALUES (
                '$name',
                '$sender',
                '$username'
              ) ON DUPLICATE KEY UPDATE last_seen=UTC_TIMESTAMP()";
    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . $query . " - " . mysql_error());

    $user_id = mysql_insert_id();

    output("New user added with id $user_id\n");

}

output("TIME 3: " . (time() - $start_time) . "\n");





$parts = explode('@', $recipient);
$target_album_handle = strtolower(preg_replace("/[^A-Za-z0-9]/", "", $parts[0]));
$recipient_domain = $parts[1];

output("target_album_handle: $target_album_handle\n");

// Get the attached photos
$paths_to_photos = array();
for ($i = 0; $i < $num_photos_attached = $_POST["attachment-count"]; $i++) {
    array_push($paths_to_photos, $_FILES["attachment-" . ($i + 1)]["tmp_name"]);
}

output("TIME 4: " . (time() - $start_time) . "\n");

// Stores the S3 URLs of the photos afters they are added to S3
$s3_urls = array();

// Check if target user is specified explicitly (e.g., vacation@alex.zipio.com)
if (preg_match("/(.+)\.zipiyo\.com/", $recipient_domain, $matches)) {
    $target_username = $matches[1];
    output("target_username: $target_username\n");
    $target_user_id = get_user_id_from_username($target_username);
} else {
    $target_user_id = $user_id;
}

output("TIME 5: " . (time() - $start_time) . "\n");

$target_album_id = album_exists($target_album_handle, $target_user_id);
$target_user_info = get_user_info($target_user_id);
$user_info = get_user_info($user_id);


output("target_album_id: $target_album_id\n");

output("TIME 6: " . (time() - $start_time) . "\n");

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


if ($target_album_id > 0) {
    // The target album exists (and so does the target user)...
    $target_album_info = get_album_info($target_album_id);

    output("Target album ($target_album_handle, owned by " . $target_user_info["username"] . ") exists.\n");
    output("Target album has ID $target_album_id\n");

    if ($target_user_id == $user_id) {
        // User is adding a photo to own existing album
        output("User is adding to his own album.\n");
        output("TIME 6.1: " . (time() - $start_time) . "\n");
        for ($i = 0; $i < $num_photos_attached = $_POST["attachment-count"]; $i++) {
            $s3_url = "";
            add_albumphoto($user_id, $target_album_id, $target_user_id, 1, $paths_to_photos[$i], $s3_url);
            array_push($s3_urls, $s3_url);
        }
        output("TIME 6.2: " . (time() - $start_time) . "\n");
        // email_followers($target_album_info, $s3_urls);
        output("TIME 6.3: " . (time() - $start_time) . "\n");

        $display_album_ra = array();
        $display_album_ra["user_id"] = $user_info["id"];
        $display_album_ra["timestamp"] = time();
        $display_album_link = $g_www_root . "/" . $target_user_info["username"] . "/" . $target_album_info["handle"] . "?request=" . urlencode(encrypt_json($display_album_ra)) . "#register=true";

        $user_email_body = <<<EMAIL
            You added a photo to your <b>{$target_album_info["handle"]}</b> album.
            <a href='{$display_album_link}'>See the album!</a>
            <br><br>
            To add more photos, email them to <b>{$target_album_info["handle"]}@{$user_info["username"]}.zipio.com</b>. Anyone can add photos, so share this email address! (We'll ask you to approve anyone who tries to add photos.)

EMAIL;

        output("TIME 7: " . (time() - $start_time) . "\n");

    } else {
        // User is adding to another user's album, so check if the submitter
        // of the photo is a collaborator of the album

        $is_collaborator = is_collaborator($user_id, $target_album_id);

        if ($is_collaborator == 1) {
            // The user who SENT the photos is already a collaborator of the target album, so add the photos
            output("User " . $user_info["username"] . " is an access of album with ID $target_album_id.\n");
            for ($i = 0; $i < $num_photos_attached = $_POST["attachment-count"]; $i++) {
                $s3_url = "";
                add_albumphoto($user_id, $target_album_id, $target_user_id, 1, $paths_to_photos[$i], $s3_url);
                array_push($s3_urls, $s3_url);
            }
            // email_followers($target_album_info, $s3_urls);

            $display_album_ra = array();
            $display_album_ra["user_id"] = $user_info["id"];
            $display_album_ra["timestamp"] = time();
            $display_album_link = $g_www_root . "/" . $target_user_info["username"] . "/" . $target_album_info["handle"] . "?request=" . urlencode(encrypt_json($display_album_ra)) . "#register=true";

            $owner_display_album_ra = array();
            $owner_display_album_ra["user_id"] = $target_album_info["user_id"];
            $owner_display_album_ra["timestamp"] = time();
            $owner_display_album_link = $g_www_root . "/" . $target_user_info["username"] . "/" . $target_album_info["handle"] . "?request=" . urlencode(encrypt_json($owner_display_album_ra)) . "#register=true";

            $user_email_body = <<<EMAIL
                You added a photo to {$target_user_info["username"]}'s <b>{$target_album_info["handle"]}</b> album.
                <a href='{$display_album_link}'>See the album!</a>
EMAIL;

            $target_user_email_body = <<<EMAIL
                {$user_info["email"]} added a photo to your {$target_album_info["handle"]} album.
                 <a href='{$owner_display_album_link}'>See the album!</a>
EMAIL;

            output("TIME 8: " . (time() - $start_time) . "\n");


        } else if ($is_collaborator == 0) {

            output("User " . $user_info["username"] . " is not a collaborator of the album " . $target_album_info["handle"] . "\n");
            // Add photo as invisible and send an email to the owner
            for ($i = 0; $i < $num_photos_attached = $_POST["attachment-count"]; $i++) {
                $s3_url = "";
                add_albumphoto($user_id, $target_album_id, $target_user_id, 0, $paths_to_photos[$i], $s3_url);
                array_push($s3_urls, $s3_url);
            }
            // email_followers($target_album_info, $s3_urls);

            $display_album_ra = array();
            $display_album_ra["user_id"] = $user_info["id"];
            $display_album_ra["timestamp"] = time();
            $display_album_link = $g_www_root . "/" . $target_user_info["username"] . "/" . $target_album_info["handle"] . "?request=" . urlencode(encrypt_json($display_album_ra))  . "#register=true";

            $user_email_body = <<<EMAIL
                You tried to add a photo to <b>{$target_user_info["username"]}</b>'s (that's {$target_user_info["email"]}) <b>{$target_album_info["handle"]}</b> album.
                <br><br>
                Your photo will appear in the album once <b>{$target_user_info["username"]}</b> says you're allowed to post to this album.
                <a href='{$display_album_link}'>See the album!</a>
EMAIL;

            $add_collaborator_ra = array();
            $add_collaborator_ra["user_id"] = $user_info["id"];
            $add_collaborator_ra["album_id"] = $target_album_id;
            $add_collaborator_ra["action"] = "add_collaborator";
            $add_collaborator_ra["timestamp"] = time();
            $add_collaborator_link = $g_www_root . "/add_collaborator.php?request=" . urlencode(encrypt_json($add_collaborator_ra));

            $target_user_email_body = <<<EMAIL
                <b>{$user_info["username"]}</b> (that's {$user_info["email"]}) tried to post a photo to your <b>{$target_album_info["handle"]}</b> album.
                <br><br>
                <b>Want to allow photos from {$user_info["username"]} in this album?</b>
                <a href='{$add_collaborator_link}'>Yes, allow <b>{$user_info["username"]}</b> to post to my <b>{$target_album_info["handle"]}</b> album </a>
                <br><br>
                If you don't want to allow <b>{$user_info["username"]}</b> to post photos to your <b>{$target_album_info["handle"]}</b> album, ignore this email.
EMAIL;

        }

    }

} else if ($target_album_id == 0) {
    // The album doesn't exist but the user does...

    output("Target user ($target_user_id, " . $target_user_info["username"] . ") exists.\n");
    output("Target album ($target_album_handle) does not exist.\n");

    if ($target_user_id == $user_id) {
        // User is creating a new album and adding a photo to it
        output("User is attempting to create own album.");
        $target_album_id = create_album($user_id, $target_album_handle);
        $target_album_info = get_album_info($target_album_id);

        for ($i = 0; $i < $num_photos_attached = $_POST["attachment-count"]; $i++) {
            $s3_url = "";
            $current_albumphoto_id = add_albumphoto($user_id, $target_album_id, $target_user_id, 1, $paths_to_photos[$i], $s3_url);
            array_push($s3_urls, $s3_url);
            if ($i == 0) {
                // Set the first photo as the cover photo
                output("current_albumphoto_id: $current_albumphoto_id\n");
                update_data("Albums", $target_album_id, array("cover_albumphoto_id" => $current_albumphoto_id));
                output("Adding albumphoto $current_albumphoto_id added to album $target_album_id\n");
            }
        }
        // email_followers($target_album_info, $s3_urls);
        output("TIME 9: " . (time() - $start_time) . "\n");


        $display_album_ra = array();
        $display_album_ra["user_id"] = $user_info["id"];
        $display_album_ra["timestamp"] = time();
        $display_album_link = $g_www_root . "/" . $target_user_info["username"] . "/" . $target_album_info["handle"] . "?request=" . urlencode(encrypt_json($display_album_ra)) . "#register=true";

        $user_email_body = <<<EMAIL
            You created a new album called <b>{$target_album_info["handle"]}</b>.
            <a href='{$display_album_link}'>See the album!</a>
            <br><br>
            To add more photos, email them to <b>{$target_album_info["handle"]}@{$user_info["username"]}.zipio.com</b>. Anyone can add photos, so share this email address! (We'll ask you to approve anyone who tries to add photos.)
EMAIL;

    } else {
        // A user cannot create an album for another user, so disallow
        output("User is attempting to create album for another user.\n");

        $user_email_body = <<<EMAIL
            You tried to add a photo to {$target_user_info["username"]}'s {$target_album_info["handle"]} album, but {$target_user_info["username"]} doesn't have an album by that name!
EMAIL;
        output("TIME 10: " . (time() - $start_time));

    }
} else if ($target_album_id == -1) {
    output("Target user ($target_user_id) does not exist.\n");
}


if ($brand_new_user) {
    $user_email_body = "Welcome to Zipio! We've assigned you a username of <b>" . $user_info["username"] . "</b>." .  $user_email_body;
}

if (!preg_match("/zipiyo.com$/", $sender)) {
    send_email($user_info["email"], $g_founders_email_address, "Zipio activity notification", $user_email_body);
    if (isset($target_user_email_body)) {
        send_email($target_user_info["email"], $g_founders_email_address, "Zipio activity notification", $target_user_email_body);
        output("$target_user_email_body\n\n");
    }
}

output("$user_email_body\n");
output("TIME 11: " . (time() - $start_time) . "\n");


function output($string) {
    global $output_handle;
    print($string);
    $date = gmdate("d-M-Y H:i:s");
    $date = "[" . $date . " UTC]";
    fwrite($output_handle, $date . " " . $string);
}


function handle_shutdown() {
    $error = error_get_last();
    output(print_r($error, true));


    global $target_album_handle;
    global $target_username;
    global $target_user_id;
    global $target_album_id;
    global $target_user_info;
    global $user_info;
    global $display_album_ra;
    global $display_album_link;
    global $user_email_body;
    global $is_collaborator;
    global $owner_display_album_ra;
    global $target_user_email_body;
    global $add_collaborator_ra;
    global $brand_new_user;
    global $sender;
    global $recipient;

    output(print_r(get_defined_vars(), true));
}

function on_error($num, $str, $file, $line) {
    output("Encountered error $num in $file, line $line: $str\n");
}


?>
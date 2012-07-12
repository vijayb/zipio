<?php
session_start();
ini_set("display_errors", 1);
error_reporting(E_ALL | E_STRICT);

require("constants.php");
require("db.php");
require("helpers.php");
require("lib/rfc822.php");

if (!isset($_GET["emails"]) ||
    !isset($_GET["inviter_id"]) ||
    !isset($_GET["album_id"]) ||
    !isset($_GET["inviter_token"]) ||
    !isset($_GET["album_token"])
    ) {
    exit();
} else {
    $emails = $_GET["emails"];
    $inviter_id = mysql_real_escape_string($_GET["inviter_id"]);
    $album_id = mysql_real_escape_string($_GET["album_id"]);
    $inviter_token = mysql_real_escape_string($_GET["inviter_token"]);
    $album_token = mysql_real_escape_string($_GET["album_token"]);
}

if (!check_token($inviter_id, $inviter_token, "Users") || !check_token($album_id, $album_token, "Albums")) {
    print("0");
    exit();
}

$album_info = get_album_info($album_id);
$album_owner_info = get_user_info($album_info["user_id"]);

$emails = preg_split("/[\s,]+/", $emails);

// These are the only valid characters in an email address
$emails = preg_replace('/[^A-Za-z0-9! # $ % &\'*+-\/=?^_`{|}~@]/i', '', $emails);

$valid_emails = array();

foreach ($emails as $email) {
    if (is_valid_email_address($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        array_push($valid_emails, $email);

        $user_info_corresponding_to_this_email = get_user_info_from_email($email);

        // If the email address doesn't correspond to an existing user, create a user
        if ($user_info_corresponding_to_this_email == 0) {
            print("$email doesn't exist\n");
            $new_username = generate_username($email);
            $user_id_corresponding_to_this_email = create_user("", $new_username, "", $email);
            print("id created is $user_id_corresponding_to_this_email\n");
        } else {
            $user_id_corresponding_to_this_email = $user_info_corresponding_to_this_email["id"];
        }

        create_collaborator($user_id_corresponding_to_this_email, $album_id);

        $display_album_ra = array();
        $display_album_ra["user_id"] = $user_id_corresponding_to_this_email;
        $display_album_ra["timestamp"] = time();
        $display_album_pretty_link = $g_www_root . "/" . $album_owner_info["username"] . "/" . $album_info["handle"];
        $display_album_link = $display_album_pretty_link . "?request=" . urlencode(encrypt_json($display_album_ra)) . "#register=true";


        $email_body = <<<EMAIL

            I've posted some photos online and want you to add more photos to the album. Here's the album:
            <br><br>
            <a href="{$display_album_link}">{$display_album_pretty_link}</a>
            <br><br>
            To add photos, email them to:
            <br><br>
            {$album_info["handle"]}@{$album_owner_info["username"]}.{$g_zipio}.com

EMAIL;


        send_email($email, $album_owner_info["email"], "Check out my photos (and add your own)", $email_body);

    }
}

print(json_encode($valid_emails));



?>
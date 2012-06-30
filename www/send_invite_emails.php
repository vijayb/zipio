<?php
session_start();
ini_set("display_errors", 1);
error_reporting(E_ALL | E_STRICT);

require("db.php");
require("helpers.php");

if (!isset($_GET["emails"]) || !isset($_GET["handle"]) || !isset($_GET["username"]) || !isset($_GET["user_email"])) {
    exit();
} else {
    $emails = mysql_real_escape_string($_GET["emails"]);
    $handle = mysql_real_escape_string($_GET["handle"]);
    $username = mysql_real_escape_string($_GET["username"]);
    $user_email = mysql_real_escape_string($_GET["user_email"]);
}


$emails = str_getcsv($emails);

$valid_emails = array();

foreach ($emails as $index => $email) {
    if (filter_var(trim($email), FILTER_VALIDATE_EMAIL)) {
        array_push($valid_emails, trim($email));
    }
}

$email_body = <<<EMAIL

I've posted some photos online at Zipio. Here they are:
<br><br>
{$www_root}/{$username}/{$handle}
<br><br>
Click the green follow button on the page to get emailed when new photos are added (no signup required)!

EMAIL;


foreach ($valid_emails as $index => $email) {
    send_email($email, $user_email, "Check out my photos", $email_body);
}

print(json_encode($valid_emails));



?>
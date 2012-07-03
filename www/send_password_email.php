<?php
session_start();
ini_set("display_errors", 1);
error_reporting(E_ALL | E_STRICT);

require("constants.php");
require("db.php");
require("helpers.php");

if (!isset($_GET["email"])) {
    exit();
} else {
    $email = mysql_real_escape_string($_GET["email"]);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    exit();
}

$user_info = get_user_info_from_email($email);


$logged_in_link_ra = array();
$logged_in_link_ra["user_id"] = $user_info["id"];
$logged_in_link_ra["timestamp"] = time();
$logged_in_link_link = $g_www_root . "/" . $user_info["username"] . "?request=" . urlencode(encrypt_json($logged_in_link_ra)) . "#register=force";

$email_body = <<<EMAIL
    <a href="$logged_in_link_link">Reset your password</a>
EMAIL;

send_email($email, $g_founders_email_address, "Reset your password", $email_body);



?>
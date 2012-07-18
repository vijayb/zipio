<?php

ini_set("display_errors", 1);
error_reporting(E_ALL | E_STRICT);

require("constants.php");
require("db.php");
require("helpers.php");


if (count($_POST) == 2) {
    if (!isset($_POST["email"]) || !isset($_POST["password_hash"])) {
        exit();
    } else {
        $email = $_POST["email"];
        $password_hash = $_POST["password_hash"];
    }
} else if (count($_GET) == 2) {
    if (!isset($_GET["email"]) || !isset($_GET["password_hash"])) {
        exit();
    } else {
        $email = $_GET["email"];
        $password_hash = $_GET["password_hash"];
    }
} else {
    print("ERROR: Incorrect number of arguments. You should be sending email and password_hash as GET or POST.");
    exit();
}

$query = "SELECT id FROM Users WHERE email_hash=UNHEX(SHA1('$email')) AND password_hash='$password_hash' LIMIT 1";
$result = mysql_query($query, $con);
if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());

$user_id = 0;

if ($row = mysql_fetch_assoc($result)) {
    $user_id = $row["id"];
} else {
    print("ERROR: Invalid username and password combo.");
    exit();
}

$query = "SELECT * FROM Albums WHERE user_id='$user_id'";

$result = mysql_query($query, $con);
if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());

$albums_array = array();
while ($row = mysql_fetch_assoc($result)) {
    $album = get_album_info($row["id"]);
    array_push($albums_array, $album);
}

echo(json_encode($albums_array));

?>
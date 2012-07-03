<?php

$username = "zipio";
$password = "daewoo";
$database = "Zipio";

if ($g_database_to_use == "PROD") {
    $con = mysql_connect ("zipio.com", $username, $password, true);
} else if ($g_database_to_use == "TEST") {
    $con = mysql_connect ("ec2-23-22-14-153.compute-1.amazonaws.com", $username, $password, true);
}


if (!$con) {
    die('Not connected : ' . mysql_error());
}

$db = mysql_select_db($database, $con);

if (!$db) {
    die ('Can\'t use db : ' . mysql_error());
}

?>

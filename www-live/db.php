<?php

$username = "zipio";
$password = "daewoo";
$database = $g_Zipio;


$con = mysql_connect("localhost", $username, $password, true);

if (!$con) {
    die('Not connected : ' . mysql_error());
}

$db = mysql_select_db($database, $con);

if (!$db) {
    die ('Can\'t use db : ' . mysql_error());
}

?>
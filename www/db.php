<?php

$username="zipio";
$password="daewoo";
$database="Zipio";

$con = mysql_connect ("ec2-107-22-123-149.compute-1.amazonaws.com", $username, $password, true);


if (!$con) {
    die('Not connected : ' . mysql_error());
}

$db = mysql_select_db($database, $con);

if (!$db) {
    die ('Can\'t use db : ' . mysql_error());
}

?>

<?php

ini_set("display_errors", 1);
error_reporting(E_ALL | E_STRICT);

require("db.php");
require("helpers.php");

if (!isset($_GET["album_id"])) {
    exit();
} else {
    $album_id = $_GET["album_id"];    
    $vis = $_GET["vis"];
}


if($vis == 1) {
    $query = "UPDATE Albums SET view_permissions = 1 WHERE id='" . $album_id . "'";
    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error()); 
} else if ($vis == 0) {
    $query = "UPDATE Albums SET view_permissions = 0 WHERE id='" . $album_id . "'";
    $result = mysql_query($query, $con);
    if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error()); 
}  

print($vis);

?>
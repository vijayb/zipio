<?php

ini_set("display_errors", 1);
error_reporting(E_ALL | E_STRICT);

require("constants.php");
require("db.php");
require("helpers.php");

if (!isset($_GET["albumphoto_id"])) {
    print("0");
    exit();
} else {
    $albumphoto_id = $_GET["albumphoto_id"];
}

$query = "SELECT
            Comments.id,
            Comments.commenter_id,
            Users.username AS username,
            Comments.comment,
            Comments.created
          FROM Comments
          LEFT JOIN Users
          ON Comments.commenter_id = Users.id
          WHERE albumphoto_id='$albumphoto_id'
          ORDER BY Comments.id ASC";
$result = mysql_query($query, $con);
if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());


$comments_arr = array();

$num_comments = 0;
while ($row = mysql_fetch_assoc($result)) {

    array_push($comments_arr, $row);
    $num_comments++;

}

echo(json_encode($comments_arr));

?>
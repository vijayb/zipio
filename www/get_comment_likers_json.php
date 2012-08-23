<?php

ini_set("display_errors", 1);
error_reporting(E_ALL | E_STRICT);

require("constants.php");
require("db.php");
require("helpers.php");

if (!isset($_GET["comment_id"])) {
    print("0");
    exit();
} else {
    $comment_id = $_GET["comment_id"];
}




$query = "SELECT
            CommentLikes.id,
            CommentLikes.liker_id,
            Users.username AS username,
            CommentLikes.created
          FROM CommentLikes
          LEFT JOIN Users
          ON CommentLikes.liker_id = Users.id
          WHERE comment_id='$comment_id'";
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
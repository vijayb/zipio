<?php

ini_set("display_errors", 1);
error_reporting(E_ALL | E_STRICT);

require("constants.php");
require("db.php");
require("helpers.php");

if (!isset($_GET["albumphoto_id"]) || !isset($_GET["token"]) || !isset($_GET["album_id"])) {
    print("0");
    exit();
} else {
    $albumphoto_id = $_GET["albumphoto_id"];
    $album_id = $_GET["album_id"];
    $token = $_GET["token"];
}

if (!check_token($album_id, $token, "Albums")) {
    print("0");
    exit();
}



$query = "SELECT
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

$num_comments = 0;
while ($row = mysql_fetch_assoc($result)) {
    $html = <<<HTML

        <b>{$row["username"]}:</b>
        {$row["comment"]}
        <span style="font-size:12px; color:#999999">{$row["created"]}</span>
        <br><hr>

HTML;

    $num_comments++;
    print($html);

}

print("<input type='hidden' id='ajax-comment-count' value='$num_comments'>");

?>
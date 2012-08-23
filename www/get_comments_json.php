<?php

ini_set("display_errors", 1);
error_reporting(E_ALL | E_STRICT);

require("constants.php");
require("db.php");
require("helpers.php");

if (!isset($_GET["albumphoto_id"]) ||
    !isset($_GET["user_id"]) ||
    !isset($_GET["token"])) {
    print("0");
    exit();
} else {
    $albumphoto_id = $_GET["albumphoto_id"];
    $user_id = $_GET["user_id"];
    $token = $_GET["token"];
}

if (!check_token($user_id, $token, "Users")) {
    print("0");
    exit();
}


$query = "SELECT
            comment_id,
            commenter_id
          FROM CommentLikes
          WHERE albumphoto_id='$albumphoto_id'";

$result = mysql_query($query, $con);
if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());


$liked_comments = array();
$like_count = array();

while ($row = mysql_fetch_assoc($result)) {
    if ($row["commenter_id"] == $user_id) {
        $liked_comments[$row["comment_id"]] = 1;
    }

    if (!isset($like_count[$row["comment_id"]])) {
        $like_count[$row["comment_id"]] = 1;
    } else {
        $like_count[$row["comment_id"]]++;
    }
}



$query = "SELECT
            Comments.id AS id,
            Comments.commenter_id AS commenter_id,
            Users.username AS username,
            Comments.comment AS comment,
            Comments.created AS created
          FROM Comments
          LEFT JOIN Users
          ON Comments.commenter_id = Users.id
          WHERE albumphoto_id='$albumphoto_id'
          ORDER BY Comments.id ASC";
$result = mysql_query($query, $con);
if (!$result) die('Invalid query in ' . __FUNCTION__ . ': ' . mysql_error());


$comments_arr = array();


while ($row = mysql_fetch_assoc($result)) {
    if (isset($liked_comments[$row["id"]])) {
        $row["liked"] = 1;
    } else {
        $row["liked"] = 0;
    }

    if (isset($like_count[$row["id"]])) {
        $row["like_count"] = $like_count[$row["id"]];
    } else {
        $row["like_count"] = 0;
    }

    array_push($comments_arr, $row);
}

echo(json_encode($comments_arr));

?>
<?php

require("constants.php");
require("db.php");
require("helpers.php");


if (!isset($_GET["like"]) || !isset($_GET["userID"]) || !isset($_GET["albumphotoID"]) || !is_numeric($_GET["albumID"]) ||
    !is_numeric($_GET["like"]) || !is_numeric($_GET["userID"]) || !is_numeric($_GET["albumphotoID"]) || 
    !is_numeric($_GET["albumID"])) {
    print("0");
    exit();
}


update_likes($_GET["albumphotoID"], $_GET["albumID"], $_GET["userID"], $_GET["like"]);


?>

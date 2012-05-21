<?php


// http://linux-is-myfriend.blogspot.com/2010/09/php-fatal-error-class-httprequest-not.html

ini_set("display_errors", 1);
error_reporting(E_ALL | E_STRICT);

require("db.php");
require("helpers.php");

send_email('sanjay@gmail.com', 'founders@zipiyo.com', 'My Subject', "Hello!");

?>
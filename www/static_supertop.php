<?php

session_start();
ini_set("display_errors", 1);
error_reporting(E_ALL | E_STRICT);

require("constants.php");
require("db.php");
require("helpers.php");

check_request_for_login($_GET);

?>
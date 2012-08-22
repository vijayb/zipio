<?php


session_start();
$_SESSION = array();


setcookie("user_id", "", time() - 1000000);
setcookie("user_token", "", time() - 1000000);
session_destroy();

header('Location: ' . $_SERVER['HTTP_REFERER']);

?>
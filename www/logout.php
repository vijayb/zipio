<?php

session_start();
session_destroy();
header('Location: ' . $_SERVER['HTTP_REFERER']);

setcookie("user_id", "", time() - 3600);
setcookie("user_token", "", time() - 3600);

?>
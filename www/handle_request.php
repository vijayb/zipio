<?php

ini_set("display_errors", 1);
error_reporting(E_ALL | E_STRICT);

require("db.php");
require("helpers.php");

print("GET:<br>\n");
print_r($_GET);

print("<br>\n");

print("POST:<br>\n");
print_r($_POST);

if (!isset($_GET["request"])) {
    exit();
}

$request = decrypt_json($_GET["request"]);

debug("request:");
print_r($request);

if ($request["action"] == "change_username") {
    update_data("Users", $request["user_id"], $_POST);
}
<?php

set_time_limit(0);

ini_set("display_errors", 1);
error_reporting(E_ALL | E_STRICT);

require("constants.php");
require("db.php");
require("helpers.php");

if (!isset($_GET["user_id"]) ||
    !isset($_GET["token"])) {
    print("0");
    exit();
} else {
    $user_id = $_GET["user_id"];
    $token = $_GET["token"];
    $num_notifications = $_GET["num_notifications"];
}

if (!check_token($user_id, $token, "Users")) {
    print("0");
    exit();
}

$events_array = array();

for ($i = 0; $i < 10; $i++) {

    $events_array = get_events_array($user_id);

    if (count($events_array) != $num_notifications) {
        break;
    }

    sleep(5);

}

print(json_encode($events_array));





?>
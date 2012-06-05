<?php

ini_set("display_errors", 1);
error_reporting(E_ALL | E_STRICT);

require("db.php");
require("helpers.php");

print_r($_GET);
print_r($_POST);

if (!isset($_GET["token"])) {
    exit();
}

$request = decrypt_json($_GET["token"]);

debug("request:");
print_r($request);

if ($request["action"] == "display_album") {

    $photos_array = get_photos_info($request["album_id"]);

    for ($i = 0; $i < count($photos_array); $i++) {
        if ($photos_array[$i]["visible"] == 0) {
            $opacity = "0.4";
        } else {
            $opacity = "1.0";
        }

        print("<img style='opacity:$opacity;'src='https://s3.amazonaws.com/zipio_photos/" . $photos_array[$i]["s3_url"] . "'><br><br>");

    }

} else if ($request["action"] == "change_username") {

    update_data("Users", $request["user_id"], $_POST);

}
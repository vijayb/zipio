<?php
ini_set("display_errors", 1);
error_reporting(E_ALL | E_STRICT);

require("db.php");
require("helpers.php");

$album_to_display = $_GET["album_id"];

$photos_array = get_photos_info($album_to_display);
for ($i = 0; $i < count($photos_array); $i++) {
    if ($photos_array[$i]["visible"] == 0) {
        $opacity = "0.4";
    } else {
        $opacity = "1.0";
    }

    print("<img style='opacity:$opacity;'src='https://s3.amazonaws.com/zipio_photos/" . $photos_array[$i]["s3_url"] . "'><br><br>");

}

?>

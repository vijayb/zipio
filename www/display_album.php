<?php

ini_set("display_errors", 1);
error_reporting(E_ALL | E_STRICT);

require("db.php");
require("helpers.php");

if (!isset($_GET["token"])) {
    exit();
}

$request = decrypt_json($_GET["token"]);
print("<!--" . print_r($request, true) . "-->");

if ($request["action"] != "display_album") {
    exit();
}

?>






<?php require("static_top.php"); ?>



<div class="row-fluid">
    <div class="span12">
        <div id="masonry-container">

<?php

$album_to_display = $request["album_id"];

$photos_array = get_photos_info($album_to_display);
for ($i = 0; $i < count($photos_array); $i++) {
    if ($photos_array[$i]["visible"] == 0) {
        $opacity = "0.4";
    } else {
        $opacity = "1.0";
    }

    print("<div class='item'><img style='opacity:$opacity;'src='https://s3.amazonaws.com/zipio_photos/" . $photos_array[$i]["s3_url"] . "_480'></div>");

}



?>


        </div>
    </div>
</div>



<?php require("static_bottom.php"); ?>
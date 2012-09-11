<?php
// |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
require("static_supertop.php");
// |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||


if (!isset($_GET["albumphoto_id"])) {
    exit();
} else {
    $albumphoto_to_display = $_GET["albumphoto_id"];
    $albumphoto_info = get_albumphoto_info($albumphoto_to_display);
    $album_info = get_album_info($albumphoto_info["album_id"]);
    $album_owner_info = get_user_info($album_info["user_id"]);
    $album_info["username"] = get_username_from_user_id($album_info["user_id"]);
    if ($g_debug) {
        print("<!--" . $_SERVER["SCRIPT_FILENAME"] . "-->");
        print("<!-- albumphoto_to_display: $albumphoto_to_display -->\n");
        print("<!-- album_info: " . print_r($albumphoto_info, true) . "-->");
    }
}



////////////////////////////////////////////////////////////////////////////////
// Page-specific PHP goes here

if (is_logged_in()) {
}


if ($albumphoto_info["filtered"] > 0) {
    $is_filtered = "_filtered";
} else {
    $is_filtered = "";
}



////////////////////////////////////////////////////////////////////////////////
// The following variables should be set

$page_title = <<<HTML
        {$album_info["handle"]}@<a href="/{$album_owner_info["username"]}">{$album_owner_info["username"]}</a>.{$g_zipio}.com <!-- <i class="icon-info-sign big-icon"></i> -->
HTML;


$page_subtitle = <<<HTML
        <i class="icon-caret-left"></i> <a href="/{$album_owner_info["username"]}/{$album_info["handle"]}">Back to album</a>
HTML;

?>

<?php require("static_top.php"); ?>
<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->



<?php

for ($i = 0; $i < count($album_info["albumphoto_ids"]); $i++) {
    if ($album_info["albumphoto_ids"][$i] == $albumphoto_to_display) {

        if ($i == 0) {
            $prev_albumphoto_id = $album_info["albumphoto_ids"][count($album_info["albumphoto_ids"]) - 1];
        } else {
            $prev_albumphoto_id = $album_info["albumphoto_ids"][$i - 1];
        }

        if ($i == count($album_info["albumphoto_ids"]) - 1) {
            $next_albumphoto_id = $album_info["albumphoto_ids"][0];
        } else {
            $next_albumphoto_id = $album_info["albumphoto_ids"][$i + 1];
        }

    }
}

// Get the next photo so we have its URL so we can preload it below
$next_albumphoto_info = get_albumphoto_info($next_albumphoto_id);
$prev_albumphoto_info = get_albumphoto_info($prev_albumphoto_id);

if ($next_albumphoto_info["filtered"] > 0) {
    $next_is_filtered = "_filtered";
} else {
    $next_is_filtered = "";
}

if ($prev_albumphoto_info["filtered"] > 0) {
    $prev_is_filtered = "_filtered";
} else {
    $prev_is_filtered = "";
}

$html = <<<HTML

<div class="row-fluid">
    <div class="span8"
         style="background-color:#eeeeee; text-align:center; margin-bottom:10px;"
         id="one-up-photo"
         albumphoto-id="{$albumphoto_info["id"]}"
         albumphoto-s3="{$albumphoto_info["s3_url"]}_big{$is_filtered}">
        <img src="{$g_s3_root}/{$albumphoto_info["s3_url"]}_big{$is_filtered}"
             style="max-height:100%;"
             id="image-{$albumphoto_info["id"]}"
             owner-id="{$albumphoto_info["photo_owner_id"]}"
             albumphoto-s3="{$albumphoto_info["s3_url"]}_cropped{$is_filtered}">
    </div>

    <div class="span4">
        <div style="overflow:hidden">
            <div style="float:left">
                <a href="/one_up.php?albumphoto_id={$prev_albumphoto_id}" class="no-underline">
                    <i class="icon-arrow-left"></i> <img style="height:50px; width:50px" src="{$g_s3_root}/{$prev_albumphoto_info["s3_url"]}_cropped{$prev_is_filtered}"></a>
            </div>
            <div style="float:right">
                <a href="/one_up.php?albumphoto_id={$next_albumphoto_id}" class="no-underline">
                    <img style="height:50px; width:50px" src="{$g_s3_root}/{$next_albumphoto_info["s3_url"]}_cropped{$next_is_filtered}"> <i class="icon-arrow-right"></i></a>
            </div>
        </div>
HTML;

    $html .= <<<HTML
        <div id="one-up-comments" style="clear:both; margin-top:10px;">
        </div>
HTML;


        if (is_logged_in()) {

            $html .= <<<HTML

        <div style="margin-top:10px;">
            <textarea id="one-up-comment-input" class="input-xlarge" style="width:100%; box-sizing:border-box; height:80px;" placeholder="Add a comment..."></textarea>
            <button onclick="submitCommentOneUp();"
                    class="btn btn-primary btn-block" id="one-up-comment-submit" data-loading-text="Adding comment...">
                    Add comment
            </button>
        </div>
HTML;

        }


$html .= <<<HTML
    </div>
</div>
HTML;

print($html);




$output_js = <<<HTML
    preload(['{$g_s3_root}/{$next_albumphoto_info["s3_url"]}_big{$next_is_filtered}']);
HTML;


?>


<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->
<?php require("static_scripts.php"); ?>
<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->









<script>

var gAlbumphoto;
var gAlbum;

$(function() {

    <?php

    print("gAlbumphoto = " . json_encode($albumphoto_info) . ";");
    print("gAlbum = " . json_encode($album_info) . ";");

    print($output_js);

    ?>

    if (isLoggedIn()) {
        reloadCommentsOneUp(gAlbumphoto["id"], gUser["id"]);
    }

});

</script>




<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->
<?php require("static_bottom.php"); ?>
<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->
























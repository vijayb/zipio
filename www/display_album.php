<?php
session_start();
ini_set("display_errors", 1);
error_reporting(E_ALL | E_STRICT);

require("db.php");
require("helpers.php");

check_request_for_login($_GET);
print("<!--" . print_r($_SESSION, true) . "-->");

if (!isset($_GET["username"]) || !isset($_GET["album_handle"])) {
    exit();
} else {
    $album_to_display = album_exists($_GET["album_handle"], $_GET["username"]);
    $album_info = get_album_info($album_to_display);
    $username = $_GET["username"];
    $username = get_username_from_userstring($username);
    print("<!-- album_id: $album_to_display -->\n");
    print("<!-- username: $username -->\n");
}

// |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||| //
// |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||| //
// |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||| //

$page_title = <<<HTML
    <a href="/{$username}">{$username}</a> &rsaquo; {$album_info["handle"]}
HTML;

$page_title_right = <<<HTML
<div class="btn-group">
    <a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
    Album options <span class="caret"></span>
    </a>
    <ul class="dropdown-menu">
    <!-- dropdown menu links -->
    </ul>
</div>
HTML;

$page_title_right = "";

?>



<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->
<?php require("static_top.php"); ?>
<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->



<div class="row" id="masonry-container">

<?php

$photos_array = get_photos_info($album_to_display);

$photos_array_js = "";

for ($i = 0; $i < count($photos_array); $i++) {
    if ($photos_array[$i]["visible"] == 0) {
        $opacity = "0.4";
    } else {
        $opacity = "1.0";
    }

    $photos_array_js .= "'http://s3.amazonaws.com/zipio_photos/" . $photos_array[$i]["s3_url"] . "_800',";

    $html = <<<HTML
        <div class="item span3" id="photo-{$photos_array[$i]["id"]}">
            <a class="fancybox" data-fancybox-type="image" rel="fancybox" href="http://s3.amazonaws.com/zipio_photos/{$photos_array[$i]["s3_url"]}_800">
                <img style='opacity:{$opacity};' src='http://s3.amazonaws.com/zipio_photos/{$photos_array[$i]["s3_url"]}_cropped'>
            </a>

            <div class="tile-options" style="display:none;">
                <div class="btn-group">
                    <button class="btn btn-inverse dropdown-toggle" data-toggle="dropdown">
                        <i class="icon-chevron-down icon-white"></i>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a href="javascript:void(0);" onclick="deletePhotoFromAlbum({$photos_array[$i]["id"]}, {$album_to_display});"><i class="icon-trash"></i> Delete this photo</a></li>
                    </ul>
                </div>
            </div>

        </div>
HTML;

    print($html);
}

$photos_array_js = rtrim($photos_array_js, ",");

?>

</div>

<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->
<?php require("static_scripts.php"); ?>
<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->




<script>



var span3Width = 0;

$(function() {

    resizeWindow();
    span3Width = $(".span3").width();

    $(window).resize(function () {
        resizeWindow();
    });

    $(".fancybox").fancybox({
        prevEffect: 'none',
        nextEffect: 'none',
        padding: '1',
        helpers: {
            title: {
                type: 'outside'
            },
            overlay: {
                opacity: 0.8,
                css: {
                    'background-color': '#000'
                }
            },
            thumbs: {
                width: 50,
                height: 50
            }
        }
    });

    $(".item").each(function(index) {
        $(this).mouseenter(function() {
            $(this).find(".tile-options").stop(true, true).show();
        });
        $(this).mouseleave(function() {
            $(this).find(".tile-options").stop(true, true).fadeOut();
        });
    });

    // If the user is logged in but has not yet registered (i.e., set a
    // password), then show the registration dialog
    if (isLoggedIn() && user["password_hash"] == "") {
        $('#register-modal').modal('show');
    }


<?php

$output_js = <<<HTML
    preload([{$photos_array_js}]);
HTML;

print($output_js);

?>

});

</script>




<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->
<?php require("static_bottom.php"); ?>
<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->

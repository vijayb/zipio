<?php
session_start();
ini_set("display_errors", 1);
error_reporting(E_ALL | E_STRICT);

require("db.php");
require("helpers.php");

check_request_for_login($_GET);


if (!isset($_GET["username"])) {
    exit();
} else {
    $user_id = get_user_id_from_username($_GET["username"]);
    $username = get_username_from_user_id($user_id);
    print("<!-- user_id: $user_id -->\n");
    print("<!-- username: $username -->\n");
}


// |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||| //
// |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||| //
// |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||| //

$page_title = <<<HTML
    Albums I'm Following
HTML;

$page_subtitle = "To unfollow any album, use the drop-down button on that album"

?>




<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->
<?php require("static_top.php"); ?>
<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->


<div class="row" id="masonry-container">

<?php

$albums_array = get_following_albums_info($user_id);

for ($i = 0; $i < count($albums_array); $i++) {
    
      if ($albums_array[$i]["permissions"] == 1) {
        if (is_logged_in() && $_SESSION["user_id"] == $albums_array[$i]["user_id"]) {
            // Viewer is the owner of the gallery
        } else {
            continue;
        }
    } else if ($albums_array[$i]["permissions"] == 2) {
        $owner_info = get_user_info($albums_array[$i]["user_id"]);
        if (is_logged_in() && $_SESSION["user_id"] == $albums_array[$i]["user_id"]) {
            // Viewer is the owner of the albums
        } else if (is_logged_in() && in_array($_SESSION["user_id"], $owner_info["friends"])) {
            // View is a friend of the owner of the albums
        } else {
            continue;
        }
    } else if ($albums_array[$i]["permissions"] == 3) {
        // Anyone can see this album...
    }

    $cover_albumphoto_info = get_albumphoto_info($albums_array[$i]["cover_albumphoto_id"], $albums_array[$i]["id"]);
    $album_owner_name = get_username_from_user_id($albums_array[$i]["user_id"]);

    $html = <<<HTML
    <div class="item span3">
        <a href="/{$album_owner_name}/{$albums_array[$i]["handle"]}">
            <img src='{$s3_root}/{$cover_albumphoto_info["s3_url"]}_cropped_0'>
            <div class="album-details">
                <span class="album-title">
                    {$albums_array[$i]["handle"]}
                </span>
            </div>
        </a>

            <div class="tile-options" style="display:none">
                <div class="btn-group">
                    <button class="btn btn-inverse dropdown-toggle" data-toggle="dropdown">
                        <i class="icon-sort-down icon-white"></i>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a href=javascript:void(0) onclick = "unfollowAlbum({$_SESSION["user_id"]},
                                               {$albums_array[$i]["id"]},
                                               '{$_SESSION["user_info"]["token"]}');"><i class="icon-eye-close"></i>Unfollow Album</a></li>
                    </ul>
                </div>
            </div>

    </div>
HTML;

    print($html);
}

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

    $(".item").each(function(index) {
        $(this).mouseenter(function() {
            $(this).find(".tile-options").stop(true, true).show();
        });
        $(this).mouseleave(function() {
            $(this).find(".tile-options").stop(true, true).fadeOut();
        });
    });
});

</script>




<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->
<?php require("static_bottom.php"); ?>
<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->

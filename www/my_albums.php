<?php
session_start();
ini_set("display_errors", 1);
error_reporting(E_ALL | E_STRICT);

require("db.php");
require("helpers.php");

check_request_for_login($_GET);

/**
if (!is_logged_in()) {
    exit();
} else if ($user_id != is_logged_in()) {
    exit();
}**/

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
    {$username}
HTML;

$page_subtitle = "Create a new album by sending photos to <b><i>album_name</i>@zipio.com</b>"

?>




<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->
<?php require("static_top.php"); ?>
<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->


<div class="row" id="masonry-container">

<?php

$albums_array = get_albums_info($user_id);

$user_info = get_user_info($user_id);

for ($i = 0; $i < count($albums_array); $i++) {

    $cover_albumphoto_info = get_albumphoto_info($albums_array[$i]["cover_albumphoto_id"], $albums_array[$i]["id"]);

    if (is_logged_in() && $_SESSION["user_id"] == $user_id) {
    $html = <<<HTML
    <div class="item span3">
        <a href="/{$username}/{$albums_array[$i]["handle"]}">
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
                        <li>
                        <a href=javascript:void(0) onclick=makeAlbumPrivate({$albums_array[$i]["id"]},'{$user_info["token"]}')><i class="icon-ban-circle"></i> Make album private
                        </a>
                        </li>
                    </ul>
                </div>
            </div>
    </div>
HTML;
    } else {
        $html = <<<HTML
    <div class="item span3">
        <a href="/{$username}/{$albums_array[$i]["handle"]}">
            <img src='{$s3_root}/{$cover_albumphoto_info["s3_url"]}_cropped_0'>
            <div class="album-details">
                <span class="album-title">
                    {$albums_array[$i]["handle"]}
                </span>
            </div>
        </a>

<!--
            <div class="tile-options" style="display:none">
                <div class="btn-group">
                    <button class="btn btn-inverse dropdown-toggle" data-toggle="dropdown">
                        <i class="icon-sort-down icon-white"></i>
                    </button>
                    <ul class="dropdown-menu">
                    </ul>
                </div>
            </div>
-->

    </div>
HTML;
    }

    print($html);
}

?>

<!-- for private albums -->
<?php if (is_logged_in() && $_SESSION["user_id"] == $user_id) {
    
    $albums_array = get_private_albums_info($user_id);

for ($i = 0; $i < count($albums_array); $i++) {

    $cover_albumphoto_info = get_albumphoto_info($albums_array[$i]["cover_albumphoto_id"], $albums_array[$i]["id"]);

    $html = <<<HTML
    <div class="item span3">
        <a href="/{$username}/{$albums_array[$i]["handle"]}">
            <img src='{$s3_root}/{$cover_albumphoto_info["s3_url"]}_cropped_0' style='opacity:0.6'>
            <div class="album-details">
                <div class="">
                    private
                </div>
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
                        <li>
                        <a href=javascript:void(0) onclick=makeAlbumPublic({$albums_array[$i]["id"]},'{$user_info["token"]}')><i class="icon-globe"></i> Make album public
                        </a>
                        </li>
                    </ul>
                </div>
            </div>
    </div>
HTML;

    print($html);
}

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

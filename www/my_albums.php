<?php
session_start();
ini_set("display_errors", 1);
error_reporting(E_ALL | E_STRICT);

require("db.php");
require("helpers.php");

check_request_for_login($_GET);

if (!isset($_GET["owner_username"]) && !isset($_GET["follower_username"])) {
    exit();
} else {
    if (isset($_GET["following"])) {
        $owner_id = get_user_id_from_username($_GET["follower_username"]);
        $albums_array = get_following_albums_info($owner_id);
        if ($_SESSION["user_id"] != $owner_id) {
            goto_homepage();
        }
    } else {
        $owner_id = get_user_id_from_username($_GET["owner_username"]);
        $albums_array = get_albums_info($owner_id);
    }
    $owner_username = get_username_from_user_id($owner_id);
    $owner_info = get_user_info($owner_id);
    print("<!-- owner_id: $owner_id -->\n");
    print("<!-- owner_username: $owner_username -->\n");
    print("<!-- owner_info: " . print_r($owner_info, true) . "-->");
    print("<!-- albums_array: " . print_r($albums_array, true) . "-->");
}

// |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||| //
// |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||| //
// |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||| //

if ($owner_id == 0) {
    goto_homepage("#alert=5&username=" . $_GET["owner_username"]);
}

if (!isset($_GET["following"])) {
    if (is_logged_in() && $_SESSION["user_id"] == $owner_id) {
        // Viewer is looking at his own albums
        $page_title = <<<HTML
        {$owner_username} (this is you)
HTML;
        $page_subtitle = <<<HTML
        To create a new album, send photos to <b>new_album_name@zipio.com</b>
HTML;

    } else if (is_logged_in() && in_array($_SESSION["user_id"], $owner_info["friends"])) {
        // Viewer is looking at a friend's albums
        $page_title = <<<HTML
        {$owner_username} (one of your friends)
HTML;
        $page_subtitle = <<<HTML
        You're seeing {$owner_username}'s public and friends albums
HTML;

    } else {
        // Viewer is looking at a stranger's albums
        $page_title = <<<HTML
        {$owner_username}
HTML;
        $page_subtitle = <<<HTML
        You're seeing {$owner_username}'s public albums only
HTML;
    }
} else {
    $page_title = <<<HTML
    Albums I'm Following
HTML;
    $page_subtitle = <<<HTML
    You'll get an email when photos are added to these albums
HTML;
}

?>




<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->
<?php require("static_top.php"); ?>
<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->


<div class="row">
<?php

for ($i = 0; $i < count($albums_array); $i++) {


    if ($albums_array[$i]["permissions"] == 1) {
        if (is_logged_in() && $_SESSION["user_id"] == $albums_array[$i]["user_id"]) {
            // Viewer is the owner of the gallery
        } else {
            continue;
        }
    } else if ($albums_array[$i]["permissions"] == 2) {
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
    $album_owner_info = get_user_info($albums_array[$i]["user_id"]);

    $upper_left = $album_owner_info["email"];

    $html = <<<HTML
    <div class="tile span3">
        <a href="/{$album_owner_info["username"]}/{$albums_array[$i]["handle"]}">
            <img src='{$s3_root}/{$cover_albumphoto_info["s3_url"]}_cropped_0'>
            <div class="album-details">
                <span class="album-title">
                    {$albums_array[$i]["handle"]}
                </span>
            </div>
            <div class="album-privacy">
                {$upper_left}
            </div>
        </a>
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

$(function() {

    $(".tile").each(function(index) {
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

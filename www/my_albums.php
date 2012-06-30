<?php
// |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
require("static_supertop.php");
// |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||

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

    if ($debug) {
        print("<!-- owner_id: $owner_id -->\n");
        print("<!-- owner_username: $owner_username -->\n");
        print("<!-- owner_info: " . print_r($owner_info, true) . "-->");
        print("<!-- albums_array: " . print_r($albums_array, true) . "-->");
    }
}

// |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||| //
// |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||| //
// |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||| //

if ($owner_id == 0) {
    goto_homepage("#alert=5&username=" . $_GET["owner_username"]);
}

$note_to_user_who_is_looking_at_his_own_albums = "";
$page_subtitle = "";

if (!isset($_GET["following"])) {

    // =========================================================================
    // Displaying a user's albums (his own, a friend's, OR a stranger's) =======
    // =========================================================================

    if (is_logged_in() && $_SESSION["user_id"] == $owner_id) {
        // ---------------------------------------------------------------------
        // Viewer is looking at his own albums
        // ---------------------------------------------------------------------
        $page_title = "$owner_username's albums (this is you)";
        $page_subtitle = "To create a new album, send photos to <b>new_album_name@zipio.com</b>";
        $which_showing = "Showing $album_privacy_contants[1], $album_privacy_contants[2], and $album_privacy_contants[3] albums";


    } else if (is_logged_in() && in_array($_SESSION["user_id"], $owner_info["friends"])) {
        // ---------------------------------------------------------------------
        // Viewer is looking at a friend's albums
        // ---------------------------------------------------------------------
        $page_title = "$owner_username's albums (one of your friends)";
        //$page_subtitle = "You're seeing $owner_username's public and friends albums";
        $which_showing = "Showing $album_privacy_contants[2] and $album_privacy_contants[3] albums only";

    } else {
        // ---------------------------------------------------------------------
        // Viewer is looking at a stranger's albums
        // ---------------------------------------------------------------------
        $page_title = "$owner_username's albums";
        //$page_subtitle = "You're seeing $owner_username's public albums only";
        $which_showing = "Showing $album_privacy_contants[3] albums only";

    }

    $third_row = $which_showing;

} else {

    // =========================================================================
    // Displaying albums a user is FOLLOWING ===================================
    // =========================================================================

    $page_title = "Albums I'm Following";
    $page_subtitle = "You'll get an email when photos are added to these albums";

}




?>




<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->
<?php require("static_top.php"); ?>
<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->


<div class="row">
<?php

for ($i = 0; $i < count($albums_array); $i++) {

    $cover_albumphoto_info = get_albumphoto_info($albums_array[$i]["cover_albumphoto_id"], $albums_array[$i]["id"]);
    $album_owner_info = get_user_info($albums_array[$i]["user_id"]);
    $upper_left = $album_owner_info["username"];

    if ($albums_array[$i]["permissions"] == 1) {
        if (is_logged_in() && $_SESSION["user_id"] == $albums_array[$i]["user_id"]) {
            // Viewer is the owner of the gallery
            $upper_left = $album_privacy_contants[$albums_array[$i]["permissions"]];
        } else {
            continue;
        }
    } else if ($albums_array[$i]["permissions"] == 2) {
        if (is_logged_in() && $_SESSION["user_id"] == $albums_array[$i]["user_id"]) {
            // Viewer is the owner of the albums
            $upper_left = $album_privacy_contants[$albums_array[$i]["permissions"]];
        } else if (is_logged_in() && in_array($_SESSION["user_id"], $owner_info["friends"])) {
            // View is a friend of the owner of the albums
        } else {
            continue;
        }
    } else if ($albums_array[$i]["permissions"] == 3) {
        // Anyone can see this album...
        if (is_logged_in() && $_SESSION["user_id"] == $albums_array[$i]["user_id"]) {
            // Viewer is the owner of the albums
            $upper_left = $album_privacy_contants[$albums_array[$i]["permissions"]];
        }
    }




    $html = <<<HTML
    <div class="tile span3" id="album-{$albums_array[$i]["id"]}">
        <a href="/{$album_owner_info["username"]}/{$albums_array[$i]["handle"]}">
            <img src='{$s3_root}/{$cover_albumphoto_info["s3_url"]}_cropped_0'>
            <div class="album-details"></div>
            <div class="album-title">{$albums_array[$i]["handle"]}</div>
            <div class="album-privacy">
                {$upper_left}
            </div>
        </a>
HTML;

    if (!isset($_GET["following"]) && is_logged_in() && $_SESSION["user_id"] == $albums_array[$i]["user_id"]) {
        $html .= <<<HTML
            <div class="tile-options" style="display:none; padding:10px">
                <div class="btn-group">
                    <button class="btn btn-inverse dropdown-toggle" data-toggle="dropdown">
                        <i class="icon-sort-down icon-white"></i>
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <a href="javascript:void(0);" onclick="if (confirm('Are you sure?')) { deleteAlbum({$albums_array[$i]["id"]}, '{$albums_array[$i]["token"]}'); }">
                                <i class="icon-trash"></i>Delete this album
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
HTML;
    }

    $html .= <<<HTML
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

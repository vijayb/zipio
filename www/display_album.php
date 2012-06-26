<?php
session_start();
ini_set("display_errors", 1);
error_reporting(E_ALL | E_STRICT);

require("db.php");
require("helpers.php");

check_request_for_login($_GET);
print("<!-- session: " . print_r($_SESSION, true) . "-->");

if (!isset($_GET["album_owner_username"]) || !isset($_GET["album_handle"])) {
    exit();
} else {
    $album_to_display = album_exists($_GET["album_handle"], $_GET["album_owner_username"]);
    $album_info = get_album_info($album_to_display);
    $album_owner_info = get_user_info($album_info["user_id"]);
    print("<!-- album_id: $album_to_display -->\n");
    print("<!-- album_owner_info['username']: " . $album_owner_info["username"] . " -->\n");
    print("<!-- album_info: " . print_r($album_info, true) . "-->");

}

// |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||| //
// |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||| //
// |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||| //

$has_permission_to_view_album = 0;

if ($album_info["permissions"] == 1) {
    if (is_logged_in() && $_SESSION["user_id"] == $album_info["user_id"]) {
        // Viewer is the owner of the album
        $has_permission_to_view_album = 1;
    }
} else if ($album_info["permissions"] == 2) {
    if (is_logged_in() && $_SESSION["user_id"] == $album_info["user_id"]) {
        // Viewer is the owner of the album
        $has_permission_to_view_album = 1;
    } else if (is_logged_in() && in_array($_SESSION["user_id"], $album_owner_info["friends"])) {
        // Viewer is a friend of the album owner
        $has_permission_to_view_album = 1;
    }
} else if ($album_info["permissions"] == 3) {
    // Anyone can see this album...
    $has_permission_to_view_album = 1;
}

if (!$has_permission_to_view_album) {
    header("Location: $error_404");
}








$page_title = <<<HTML
    {$album_info["handle"]}<span style="color:#000000">@<a href="/{$album_owner_info["username"]}">{$album_owner_info["username"]}</a>.zipio.com</span> <!-- <i class="icon-info-sign big-icon"></i> -->
HTML;

$page_subtitle = <<<HTML
    To add photos, email them to the address above
HTML;

if (!is_logged_in()) {
    // User is not logged in, so show the follow button since we don't know
    // whether they are following the album or not. The follow button will open
    // the follow modal so the user can enter his email address.
    $page_title_right = <<<HTML
        <button class="btn btn-large btn-success"
                onclick="showFollowModal();"
                id="follow-submit"
                data-loading-text="Please wait...">
            Follow this album<br><span style="font-size:12px;">No signup required!</a>
        </button>
HTML;

} else {
    // User is logged in
    $user_id = is_logged_in();
    $logged_in_username = get_username_from_user_id($user_id);


    // Create a follow link to follow_album.php in case the user is logged in (which
    // in tern means when he clicks the follow button, we need not ask him for his
    // email address; we can immediately create the following relationship with
    // follow_album.php).
    $follow_album_ra = array();
    $follow_album_ra["follower_id"] = $user_id;
    $follow_album_ra["follower_username"] = $logged_in_username;
    $follow_album_ra["album_id"] = $album_info["id"];
    $follow_album_ra["album_handle"] = $album_info["handle"];
    $follow_album_ra["album_owner_id"] = $album_owner_info["id"];
    $follow_album_ra["album_owner_username"] = $album_owner_info["username"];
    $follow_album_ra["album_owner_email"] = $album_owner_info["email"];
    $follow_album_ra["timestamp"] = time();
    $follow_album_link = $www_root . "/follow_album.php?request=" . urlencode(encrypt_json($follow_album_ra));

    if ($logged_in_username == $album_owner_info["username"]) {
        // Logged in user is the album owner
        $page_title_right = "";

    } else {
        // Logged in user is viewing someone else's album

        if (isset($album_info) && is_following($user_id, $album_info["id"]) == 1) {
            // Logged in user is already following this album, so show the
            // unfollow button.
            $page_title_right = <<<HTML
                <button class="btn btn-large"
                        onclick="unfollowAlbum({$_SESSION["user_id"]},
                                               {$album_info["id"]},
                                               '{$_SESSION["user_info"]["token"]}');"
                        id="unfollow-submit"
                        data-loading-text="Please wait...">
                    Unfollow this album
                </button>
HTML;
        } else {
            // Logged in user is NOT following this album, so show the follow
            // button, but the onclick will immediately cause the user to be
            // following the album rather than asking for an email address.
            $page_title_right = <<<HTML
                <button class="btn btn-large btn-success"
                        onclick="$(this).button('loading'); window.location.replace('{$follow_album_link}');"
                        id="follow-submit"
                        data-loading-text="Please wait...">
                    Follow this album
                </button>
HTML;
        }
    }
}

?>



<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->
<?php require("static_top.php"); ?>
<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->

<?php

if (is_logged_in() && $_SESSION["user_id"] == $album_info["user_id"]) {

    $permissions_title = "";
    $permissions_string = "";

    if ($album_info["permissions"] == 1) {
        $permissions_title = "Private album.";
        $permissions_string = <<<HTML
Only you can see or add photos to this album.
HTML;

    } else if ($album_info["permissions"] == 2) {
        $permissions_title = "Friends album.";
        $permissions_string = <<<HTML
Only you and your friends can see and add photos to this album.
HTML;

    } else if ($album_info["permissions"] == 3) {
        $permissions_title = "Public album.";
        $permissions_string = <<<HTML
Anyone can see this album, but only you and your friends can add photos (if anyone else tries to add a photo, we'll ask you first).
HTML;

}

    $html = <<<HTML

<div class="row" style="margin-bottom:20px">
    <div class="span12">
        <div class="well well-small">
            <strong>$permissions_title</strong>
            {$permissions_string}
            <a href="javascript:void(0);" onclick="showAlbumSettingsModal();">
                Change
            </a>
        </div>
    </div>
</div>

HTML;

print($html);

}

?>





<div class="row" id="masonry-container">

<?php

$albumphotos_array = get_albumphotos_info($album_to_display);
$albumphotos_array_js = "";

for ($i = 0; $i < count($albumphotos_array); $i++) {
    if ($albumphotos_array[$i]["visible"] == 0) {
        $opacity = "0.4";
    } else {
        $opacity = "1.0";
    }

    $albumphotos_array_js .= "'" . $s3_root . "/" . $albumphotos_array[$i]["s3_url"] . "_800_" . $albumphotos_array[$i]["filter_code"] . "',";

    $html = <<<HTML

<div class="item span3" id="albumphoto-{$albumphotos_array[$i]["id"]}">

    <a id="fancybox-{$albumphotos_array[$i]["id"]}" class="fancybox" data-fancybox-type="image" rel="fancybox" href="{$s3_root}/{$albumphotos_array[$i]["s3_url"]}_800_{$albumphotos_array[$i]["filter_code"]}">
        <img id="image-{$albumphotos_array[$i]["id"]}" style='opacity:{$opacity};' src='{$s3_root}/{$albumphotos_array[$i]["s3_url"]}_cropped_{$albumphotos_array[$i]["filter_code"]}'>
    </a>

    <!--
    albumphoto_id: {$albumphotos_array[$i]["id"]}<br>
    photo_id: {$albumphotos_array[$i]["photo_id"]}<br>
    album_id: {$album_to_display}<br>
    cover_albumphoto_id: {$album_info["cover_albumphoto_id"]}<br>
    albumphoto_token: {$albumphotos_array[$i]["albumphoto_token"]}<br>
    s3_url: {$albumphotos_array[$i]["s3_url"]}<br>
    -->

    <div class="tile-options" style="display:none;">
        <div class="btn-group">
            <button class="btn btn-inverse dropdown-toggle" data-toggle="dropdown">
                <i class="icon-sort-down icon-white"></i>
            </button>
            <ul class="dropdown-menu">
                <li>
                    <a href="javascript:void(0);" onclick="deletePhotoFromAlbum({$albumphotos_array[$i]["id"]},
                                                                                '{$albumphotos_array[$i]["albumphoto_token"]}');"><i class="icon-trash"></i> Delete this photo
                    </a>
                </li>
                <li class="divider"></li>
                <li><a href="javascript:void(0);" onclick="changeFilter({$albumphotos_array[$i]["photo_id"]}, {$albumphotos_array[$i]["id"]}, 0);">Original</a></li>
                <li><a href="javascript:void(0);" onclick="changeFilter({$albumphotos_array[$i]["photo_id"]}, {$albumphotos_array[$i]["id"]}, 1);">Tilt shift</a></li>
                <li><a href="javascript:void(0);" onclick="changeFilter({$albumphotos_array[$i]["photo_id"]}, {$albumphotos_array[$i]["id"]}, 2);">Gotham</a></li>
                <li><a href="javascript:void(0);" onclick="changeFilter({$albumphotos_array[$i]["photo_id"]}, {$albumphotos_array[$i]["id"]}, 3);">Kelvin</a></li>
          </ul>
        </div>
    </div>

</div>

HTML;
    print($html);
}

$albumphotos_array_js = rtrim($albumphotos_array_js, ",");

?>






</div>

<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->
<?php require("static_scripts.php"); ?>
<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->




<script>



var span3Width = 0;
var gAlbum;

$(function() {


    <?php

    print("gAlbum = " . json_encode($album_info));

    ?>

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

    if (isLoggedIn() && gUser["id"] == gAlbum["user_id"]) {
        $(".item").each(function(index) {
            $(this).mouseenter(function() {
                $(this).find(".tile-options").stop(true, true).show();
            });
            $(this).mouseleave(function() {
                $(this).find(".tile-options").stop(true, true).fadeOut();
            });
        });
    }

<?php

$output_js = <<<HTML
    preload([{$albumphotos_array_js}]);
HTML;

print($output_js);

?>

});

</script>




<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->
<?php require("static_bottom.php"); ?>
<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->

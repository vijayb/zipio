<?php
// |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
require("static_supertop.php");
// |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||


if (!isset($_GET["album_owner_username"]) || !isset($_GET["album_handle"])) {
    exit();
} else {
    $album_to_display = album_exists($_GET["album_handle"], $_GET["album_owner_username"]);
    $album_info = get_album_info($album_to_display);
    $album_owner_info = get_user_info($album_info["user_id"]);
    $followers_info = get_followers_user_info($album_to_display);

    if ($g_debug) {
        print("<!-- GET: " . print_r($_GET, true) . "-->");
        print("<!-- album_to_display: $album_to_display -->\n");
        print("<!-- album_owner_info['username']: " . $album_owner_info["username"] . " -->\n");
        print("<!-- album_info: " . print_r($album_info, true) . "-->");
        print("<!-- followers_info: " . print_r($followers_info, true) . "-->");
    }
}

// |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||| //
// |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||| //
// |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||| //


$page_title = <<<HTML
    {$album_info["handle"]}<span style="color:#000000">@<a href="/{$album_owner_info["username"]}">{$album_owner_info["username"]}</a>.zipio.com</span> <!-- <i class="icon-info-sign big-icon"></i> -->
HTML;

$page_subtitle = "";








/*


// Set the right side button

if (!is_logged_in()) {
    // |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
    // |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
    // User is not logged in, so show the follow button since we don't know
    // whether they are following the album or not. The follow button will open
    // the follow modal so the user can enter his email address.
    // |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
    // |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||

    $page_title_right = <<<HTML
        <button class="btn btn-large btn-success follow-button"
                onclick="showFollowModal();"
                id="follow-submit"
                data-loading-text="Please wait...">
            Follow this album<br><span style="font-size:12px;">No signup required!</a>
        </button>
HTML;

} else {
    // |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
    // |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
    // User is logged in
    // |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
    // |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||

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
    $follow_album_link = $g_www_root . "/follow_album.php?request=" . urlencode(encrypt_json($follow_album_ra));

    if ($logged_in_username == $album_owner_info["username"]) {
        // =====================================================================
        // Logged in user is the album owner
        // =====================================================================

        $page_title_right = <<<HTML
HTML;


    } else {
        // =====================================================================
        // Logged in user is viewing someone else's album
        // =====================================================================

        if (isset($album_info) && is_following($user_id, $album_info["id"]) == 1) {
            // -----------------------------------------------------------------
            // Logged in user is already following this album, so show the
            // unfollow button.
            // -----------------------------------------------------------------
            $page_title_right = <<<HTML
                <button class="btn btn-large follow-button"
                        onclick="unfollowAlbum({$_SESSION["user_id"]},
                                               {$album_info["id"]},
                                               '{$_SESSION["user_info"]["token"]}');"
                        id="unfollow-submit"
                        data-loading-text="Please wait...">
                    Unfollow this album
                </button>
HTML;
        } else {
            // -----------------------------------------------------------------
            // Logged in user is NOT following this album, so show the follow
            // button, but the onclick will immediately cause the user to be
            // following the album rather than asking for an email address.
            // -----------------------------------------------------------------
            $page_title_right = <<<HTML
                <button class="btn btn-large btn-success follow-button"
                        onclick="$(this).button('loading'); window.location.replace('{$follow_album_link}');"
                        id="follow-submit"
                        data-loading-text="Please wait...">
                    Follow this album
                </button>
HTML;
        }
    }
}

*/













// Set the "third row" string


$following_html = count($followers_info) . " followers";

// $third_row = $permissions_html . "&#160;&#160;   &#183;   &#160;&#160;" . $following_html;
$third_row = $following_html;


?>






<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->
<?php require("static_top.php"); ?>
<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->





<div class="row">

    <div class="span9">
        <div class="row">

<?php

$albumphotos_array = get_albumphotos_info($album_to_display);
$albumphotos_array_js = "";

for ($i = 0; $i < count($albumphotos_array); $i++) {
    if ($albumphotos_array[$i]["visible"] == 0) {
        $opacity = "0.4";
    } else {
        $opacity = "1.0";
    }

    $albumphotos_array_js .= "'" . $g_s3_root . "/" . $albumphotos_array[$i]["s3_url"] . "_" . $albumphotos_array[$i]["max_size"] . "_" . $albumphotos_array[$i]["filter_code"] . "',";

    $html = <<<HTML

        <div class="span3 tile" id="albumphoto-{$albumphotos_array[$i]["id"]}">
            <a id="fancybox-{$albumphotos_array[$i]["id"]}" class="fancybox" data-fancybox-type="image" rel="fancybox" href="{$g_s3_root}/{$albumphotos_array[$i]["s3_url"]}_{$albumphotos_array[$i]["max_size"]}_{$albumphotos_array[$i]["filter_code"]}">
                <img id="image-{$albumphotos_array[$i]["id"]}" style='opacity:{$opacity};' src='{$g_s3_root}/{$albumphotos_array[$i]["s3_url"]}_cropped_{$albumphotos_array[$i]["filter_code"]}'>
            </a>
HTML;

    if (is_logged_in() && $_SESSION["user_id"] == $album_info["user_id"]) {

        $html .= <<<HTML
            <div class="tile-options" style="display:none">
                <div class="btn-group">
                    <button class="btn btn-inverse dropdown-toggle" data-toggle="dropdown">
                        <i class="icon-sort-down icon-white"></i>
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <a href="javascript:void(0);" onclick="deletePhotoFromAlbum({$albumphotos_array[$i]["id"]},
                                                                                        '{$albumphotos_array[$i]["token"]}');"><i class="icon-trash"></i> Delete this photo
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

$albumphotos_array_js = rtrim($albumphotos_array_js, ",");

?>

        </div>
    </div>

    <div class="span3">
        <div>
            <h2>Album Collaborators</h2>
            <h4>Collaborators can <b style="color:#666666">add</b> photos</h4>

            <div id="collaborators-list" style="margin:10px 0px;">
<?php

$collaborators_info = get_collaborators_info($album_to_display);

$html = "";

foreach ($collaborators_info as $collaborator) {
    $html .= <<<HTML
<li id="collaborator-{$collaborator["id"]}">
    {$collaborator["email"]}
    <a href="javascript:void(0);"
       onclick="if (confirm('Sure you want to remove this collaborator?')) {
                        deleteCollaborator({$collaborator["id"]},
                                           '{$collaborator["collaborator_token"]}',
                                           {$album_info["id"]},
                                           '{$album_info["token"]}');
                    }">
        <i class="icon-remove"></i>
    </a>
</li>
HTML;
}

print($html);

?>

            </div>

            <button class="btn btn-primary btn-large" href="javascript:void(0);" onclick="showInviteModal();"><i class="icon-plus-sign"></i> Invite collaborators</button>
        </div>

        <div style="height:30px"></div>

        <div>
            <h2>Privacy</h2>
            <h4>Who is allowed to <b style="color:#666666">view</b> this album?</h4>
        </div>
    </div>


</div>




<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->
<?php require("static_scripts.php"); ?>
<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->











<script>

var gAlbum;

$(function() {


    <?php

    if (is_logged_in() && $_SESSION["user_id"] == $album_info["user_id"]) {
        print("gAlbum = " . json_encode($album_info));
    }

    ?>

    $(".fancybox").fancybox({
        prevEffect: 'none',
        nextEffect: 'none',
        padding: '1',
        arrows: false,
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
                width: 100,
                height: 100
            }
        }
    });


    $(".tile").each(function(index) {
        $(this).mouseenter(function() {
            $(this).find(".tile-options").stop(true, true).show();
        });
        $(this).mouseleave(function() {
            $(this).find(".tile-options").stop(true, true).fadeOut();
        });
    });

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

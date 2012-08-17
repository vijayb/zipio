<?php
// |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
require("static_supertop.php");
// |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||


if (!isset($_GET["album_owner_username"]) || !isset($_GET["album_handle"])) {
    exit();
} else {
    $album_to_display = album_exists($_GET["album_handle"], $_GET["album_owner_username"]);
    $album_info = get_album_info($album_to_display);
    $album_info["username"] = $_GET["album_owner_username"];
    $album_owner_info = get_user_info($album_info["user_id"]);
    $collaborators_info = get_collaborators_info($album_to_display);

    if ($g_debug) {
        print("<!--" . $_SERVER["SCRIPT_FILENAME"] . "-->");
        print("<!-- GET: " . print_r($_GET, true) . "-->");
        print("<!-- album_to_display: $album_to_display -->\n");
        print("<!-- album_owner_info['username']: " . $album_owner_info["username"] . " -->\n");
        print("<!-- album_info: " . print_r($album_info, true) . "-->");
        print("<!-- collaborators_info: " . print_r($collaborators_info, true) . "-->");
    }
}

// |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||| //
// |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||| //
// |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||| //

if (is_logged_in()) {
    $user_id = $_SESSION['user_id'];
    $albumphoto_likes_info = get_albumphoto_likes_info($user_id, $album_info["id"]);
} else {
  $albumphotos_likes_info = array();
}

$is_owner = 0;
$is_collaborator = 0;

if (is_logged_in() && is_collaborator($_SESSION["user_id"], $album_to_display)) {
    $is_collaborator = 1;
} else if (is_logged_in() && $album_info["user_id"] == $_SESSION["user_id"]) {
    $is_owner = 1;
}

if ($album_info["read_permissions"] == 1 && !($is_collaborator || $is_owner)) {
    goto_homepage();
}

if ($g_debug) {
    print("<!-- is_collaborator: $is_collaborator -->\n");
    print("<!-- is_owner: $is_owner -->\n");
}

$page_title = <<<HTML
    {$album_info["handle"]}@<a href="/{$album_owner_info["username"]}">{$album_owner_info["username"]}</a>.{$g_zipio}.com <!-- <i class="icon-info-sign big-icon"></i> -->
HTML;

$page_subtitle = "To add photos, email them to the above address";

if ($is_owner || $is_collaborator) {
    $photos_area_width = "span9";
} else {
    $photos_area_width = "span12";
}

?>
<?php require("static_top.php"); ?>
<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->



<div class="row">

    <div class="<?php print($photos_area_width); ?>">
        <div class="row">

<?php

$albumphotos_array = get_albumphotos_info($album_to_display);
$albumphotos_array_js = "";

$photo_owners = array();

// The first albumphoto that is VISIBLE is 0, the second is 1, and so on. We
// cannot simply use $i in the loop below because some albumphotos in the array
// won't be visible (hence the "continue").
$index_of_albumphoto_in_album = 0;

// If this page is being loaded with an albumphoto GET paramter, then we need to
// know the fancybox index (which is the index of the albumphoto in the album
// AS DISPLAYED, such that the first photo DISPLAYED in the album has index 0)
// so that we can open that image up with fancybox.
$fancybox_image_to_show = 0;

for ($i = 0; $i < count($albumphotos_array); $i++) {

    $albumphoto_id = $albumphotos_array[$i]["id"];

    if ($albumphotos_array[$i]["visible"] == 0) {
        continue;
    }

    if ($albumphotos_array[$i]["filtered"] > 0) {
        $is_filtered = "_filtered";
    } else {
        $is_filtered = "";
    }

    $albumphotos_array_js .= "'" . $g_s3_root . "/" . $albumphotos_array[$i]["s3_url"] . "_big" . $is_filtered . "',";

    // Get the owner of the current photo. $photo_owners is a temporary store of
    // user objects (who are various photo owners) so that we don't need to do
    // a get_user_info each time for the same potential owner. We can make this
    // simpler after memcache has been added to the entire system.
    if (isset($photo_owners[$albumphotos_array[$i]["photo_owner_id"]])) {
        $photo_owner = $photo_owners[$albumphotos_array[$i]["photo_owner_id"]];
    } else {
        $photo_owner = get_user_info($albumphotos_array[$i]["photo_owner_id"]);
        $photo_owners[$albumphotos_array[$i]["photo_owner_id"]] = $photo_owner;
    }

    $link_to_album_with_image_opened = $g_www_root . "/" . $album_owner_info["username"] . "/" . $album_info["handle"] . "?albumphoto=" . $albumphoto_id;

    $html = <<<HTML
        <div class="span3 tile" id="albumphoto-{$albumphoto_id}" one-up-link="{$link_to_album_with_image_opened}">

            <div style="position:relative">

                <a id="fancybox-{$albumphoto_id}"
                   class="fancybox"
                   data-fancybox-type="image"
                   rel="fancybox"
                   href="{$g_s3_root}/{$albumphotos_array[$i]["s3_url"]}_big{$is_filtered}">

                    <!------------------------------------------------------------->
                    <!-- THE ACTUAL PHOTO ----------------------------------------->
                    <!------------------------------------------------------------->
                    <img class="albumphoto-image" id="image-{$albumphoto_id}" src='{$g_s3_root}/{$albumphotos_array[$i]["s3_url"]}_cropped{$is_filtered}'>
                    <!------------------------------------------------------------->
                    <!------------------------------------------------------------->
                    <!------------------------------------------------------------->

                    <div class="album-privacy">
                        by <b>{$photo_owners[$albumphotos_array[$i]["photo_owner_id"]]["username"]}</b>
                    </div>

                </a>
HTML;








////////////////////////////////////////////////////////////////////////////////
// FACEBOOK LIKE AND PINTEREST BUTTON
////////////////////////////////////////////////////////////////////////////////

/*

    $pin_website = urlencode($g_www_root . "/" . $album_owner_info["username"] . "/" . $album_info["handle"]);
    $pin_image = urlencode($g_s3_root."/".$albumphotos_array[$i]["s3_url"]."_big" . $is_filtered);
    $html .= <<<HTML

        <div class="likes-panel">

            <div>
                <fb:like href="{$link_to_album_with_image_opened}"
                         send="false"
                         layout="button_count"
                         show_faces="true"
                         font="arial">
                </fb:like>
            </div>

            <div style="margin-top:5px;">
                <a href="http://pinterest.com/pin/create/button/?url={$pin_website}&media={$pin_image}&description='I liked this photo I found on zipio'" class="pin-it-button" count-layout="none"><img src="//assets.pinterest.com/images/PinExt.png" alt="Pin it" / ></a>
            </div>

        </div>
HTML;

    if (isset($_GET["albumphoto"]) && $albumphoto_id == $_GET["albumphoto"]) {
        $fancybox_image_to_show = $index_of_albumphoto_in_album;
    }

*/

////////////////////////////////////////////////////////////////////////////////
// END FACEBOOK AND PINTEREST LIKE BUTTON
////////////////////////////////////////////////////////////////////////////////



    $display_like_count = "";
    if ($albumphotos_array[$i]["num_comments"] == 0) {
        $display_like_count = "style='display:none'";
    }

    $html .= <<<HTML
            <div class="comment-count">
                <span id="comment-count-{$albumphoto_id}" $display_like_count class="count-number">
                    {$albumphotos_array[$i]["num_comments"]}
                </span>
                <a href="javascript:void(0)" class="no-underline" onclick="showCommentsModal($albumphoto_id, '{$albumphotos_array[$i]["s3_url"]}_cropped{$is_filtered}');">
                    <i class="icon-comments"></i>
                </a>
            </div>
HTML;


    if (isset($albumphoto_likes_info) && array_key_exists($albumphoto_id, $albumphoto_likes_info)) {
        $heart_class = "heart-red";
        $liked = 1;
    } else {
        $heart_class = "heart-gray";
        $liked = 0;
    }

    $photo_owner_id = $albumphotos_array[$i]["photo_owner_id"];
    if (isset($user_id)) {
        $liker_id = $user_id;
    } else {
        $liker_id = 0;
    }

    $display_like_count = "";
    if ($albumphotos_array[$i]["num_likes"] == 0) {
        $display_like_count = "style='display:none'";
    }
    $html .= <<<HTML
        <div id="albumphoto-like-{$albumphoto_id}" class="albumphoto-like" liked="{$liked}">
            <a id="albumphoto-like-count-link-{$albumphoto_id}" href="javascript:void(0)" class="no-underline count-number" onclick="showLikersModal({$albumphoto_id});" {$display_like_count}>
                <span id="albumphoto-like-count-{$albumphoto_id}">{$albumphotos_array[$i]["num_likes"]}</span>
            </a>
            <a href="javascript:void(0)" class="no-underline" onclick="likeAlbumphoto({$albumphoto_id}, {$liker_id}, {$albumphotos_array[$i]["photo_owner_id"]}, '{$albumphotos_array[$i]["s3_url"]}_cropped{$is_filtered}');">
                 <i id="albumphoto-like-heart-{$albumphoto_id}" class="icon-heart $heart_class"></i>
            </a>
        </div>
HTML;


    // If there IS a caption -------------------------------------------------//

    if (isset($albumphotos_array[$i]["caption"]) && $albumphotos_array[$i]["caption"] != "") {

        $edit_caption_string = "";
        if ($is_owner || $is_collaborator) {
            $edit_caption_string = <<<HTML
                <a id="add-caption-{$albumphotos_array[$i]["id"]}" href="javascript:void(0)" onclick="showCaptionModal({$albumphotos_array[$i]["id"]})" class="no-underline">
                    &nbsp; <i class="icon-pencil"></i> Edit
                </a>
HTML;
        }

        $html .= <<<HTML
            <div class="albumphoto-caption-always-visible">
                <span id="albumphoto-caption-{$albumphotos_array[$i]["id"]}">{$albumphotos_array[$i]["caption"]}</span>
                {$edit_caption_string}
            </div>
HTML;
    } else {

    // If there is NO caption ------------------------------------------------//

        $edit_caption_string = "";
        if ($is_owner || $is_collaborator) {
            $edit_caption_string = <<<HTML
                <a id="add-caption-{$albumphotos_array[$i]["id"]}" href="javascript:void(0)" onclick="showCaptionModal({$albumphotos_array[$i]["id"]})" class="no-underline">
                    <i class="icon-pencil"></i> Add a caption
                </a>
HTML;

            $html .= <<<HTML
            <div class="albumphoto-caption">
                <span id="albumphoto-caption-{$albumphotos_array[$i]["id"]}">{$albumphotos_array[$i]["caption"]}</span>
                {$edit_caption_string}
            </div>
HTML;


        }

    }

    $html .= <<<HTML

        </div>
HTML;

    if ($is_owner || $is_collaborator) {
        $html .= <<<HTML
            <div style="position:relative; top:3px;">

                <div rel="tooltip" title="Post to Facebook" class="btn ttip" style="color:#666" onclick="showFacebookModal({$albumphoto_id});">
                    <i class="icon-facebook"></i>
                </div>

                <div class="btn-group" style="float:right; margin-left:5px;">
                    <button rel="tooltip" title="Filters" id="filter-{$albumphoto_id}" class="btn dropdown-toggle ttip" style="color:#666" data-toggle="dropdown" data-loading-text="Filtering...">
                         <i class="icon-beaker"></i> <i class="icon-sort-down icon-white"></i>
                    </button>
                    <ul class="dropdown-menu pull-right">
                        <li>
                            <a href="javascript:void(0);" onclick="resetPhotoToOriginal({$albumphoto_id},
                                                                                        '{$g_s3_root}/{$albumphotos_array[$i]["s3_url"]}_cropped',
                                                                                        '{$g_s3_root}/{$albumphotos_array[$i]["s3_url"]}_big');">
                                <i class="icon-undo"></i> Back to Original
                            </a>
                        </li>
                        <li class="divider"></li>
                        <li><a href="javascript:void(0);" onclick="applyFilter({$albumphoto_id}, '{$g_www_root}/proxy.php?url={$g_s3_root}/{$albumphotos_array[$i]["s3_url"]}_cropped&mime_type=image/jpeg', 1);">Hoppe</a></li>
                        <li><a href="javascript:void(0);" onclick="applyFilter({$albumphoto_id}, '{$g_www_root}/proxy.php?url={$g_s3_root}/{$albumphotos_array[$i]["s3_url"]}_cropped&mime_type=image/jpeg', 2);">Hayek</a></li>
                        <li><a href="javascript:void(0);" onclick="applyFilter({$albumphoto_id}, '{$g_www_root}/proxy.php?url={$g_s3_root}/{$albumphotos_array[$i]["s3_url"]}_cropped&mime_type=image/jpeg', 3);">Sowell</a></li>
                        <li><a href="javascript:void(0);" onclick="applyFilter({$albumphoto_id}, '{$g_www_root}/proxy.php?url={$g_s3_root}/{$albumphotos_array[$i]["s3_url"]}_cropped&mime_type=image/jpeg', 4);">Spooner</a></li>
                        <li><a href="javascript:void(0);" onclick="applyFilter({$albumphoto_id}, '{$g_www_root}/proxy.php?url={$g_s3_root}/{$albumphotos_array[$i]["s3_url"]}_cropped&mime_type=image/jpeg', 5);">Paul</a></li>
                        <li><a href="javascript:void(0);" onclick="applyFilter({$albumphoto_id}, '{$g_www_root}/proxy.php?url={$g_s3_root}/{$albumphotos_array[$i]["s3_url"]}_cropped&mime_type=image/jpeg', 6);">Walter</a></li>
                        <li><a href="javascript:void(0);" onclick="applyFilter({$albumphoto_id}, '{$g_www_root}/proxy.php?url={$g_s3_root}/{$albumphotos_array[$i]["s3_url"]}_cropped&mime_type=image/jpeg', 7);">Mises</a></li>
                        <li><a href="javascript:void(0);" onclick="applyFilter({$albumphoto_id}, '{$g_www_root}/proxy.php?url={$g_s3_root}/{$albumphotos_array[$i]["s3_url"]}_cropped&mime_type=image/jpeg', 8);">Menger</a></li>
                        <li><a href="javascript:void(0);" onclick="applyFilter({$albumphoto_id}, '{$g_www_root}/proxy.php?url={$g_s3_root}/{$albumphotos_array[$i]["s3_url"]}_cropped&mime_type=image/jpeg', 10);">Rothbard</a></li>
                    </ul>
                </div>
                <button id="save-{$albumphoto_id}" class="btn btn-primary" href="#" style="color:#666; float:right; margin-left:5px; display:none" onclick="saveFiltered({$albumphoto_id},
                                                                                                                               '{$g_www_root}/proxy.php?url={$g_s3_root}/{$albumphotos_array[$i]["s3_url"]}_cropped&mime_type=image/jpeg',
                                                                                                                               '{$g_www_root}/proxy.php?url={$g_s3_root}/{$albumphotos_array[$i]["s3_url"]}_big&mime_type=image/jpeg',
                                                                                                                               '{$g_s3_root}/{$albumphotos_array[$i]["s3_url"]}_big'
                                                                                                                               );">
                    Save
                </button>



                <div class="btn-group" style="float:right; margin-left:5px;">
                    <button rel="tooltip" title="Options" id="options-{$albumphoto_id}" style="color:#666" class="btn dropdown-toggle ttip" data-toggle="dropdown">
                        <i class="icon-wrench"></i> <i class="icon-sort-down icon-white"></i>
                    </button>
                    <ul class="dropdown-menu pull-right">

                        <li>
                            <a href="javascript:void(0);" onclick="setAsAlbumCover({$albumphotos_array[$i]["id"]}, {$albumphotos_array[$i]["album_id"]}, '{$albumphotos_array[$i]["token"]}');">
                                <i class="icon-picture"></i> Set as album cover
                            </a>
                        </li>

                        <li>
                            <a href="javascript:void(0);" onclick="if (confirm('Really delete this photo?')) {
                                                                            deletePhotoFromAlbum({$albumphotos_array[$i]["id"]}, '{$albumphotos_array[$i]["token"]}');
                                                                        }">
                                <i class="icon-trash"></i> Delete this photo
                            </a>
                        </li>

                    </ul>
                </div>

            </div>
HTML;
    }




    $html .= <<<HTML

            <div id="cover-{$albumphoto_id}" style="position:absolute; top:0px; left:0px; width:100%; height:100%; background-color:black; opacity:0.7; text-align:center; display:none;">
                <span style="position:relative; top:45%; color:#ffffff; font-size:26px;">Saving...</span>
            </div>
        </div>
HTML;

    print($html);

    $index_of_albumphoto_in_album++;
}


$html = <<<HTML

<!--
<div class="span3 tile">
    <div style="width:100%; height:100%; background-color:#dddddd;">
        <img src="http://s3.zipiyo.com/photos/1_1_08d481fc329626acd51cff1adf7f28a3f8952b32_cropped_filtered">
    </div>
</div>
-->

HTML;

print($html);


$albumphotos_array_js = rtrim($albumphotos_array_js, ",");
?>

        </div>
    </div>



<?php


////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////


// RIGHT SIDE PANELS

if ($is_owner || $is_collaborator) {

    if ($is_owner) {
        $you_html = "- this is you";
    } else {
        $you_html = "";
    }

    $html = <<<HTML
        <div class="span3">
            <div style="margin-bottom:50px">
                <h2>Album Collaborators</h2>
                <h4>Collaborators can <b style="color:#444444">add</b> photos and invite others</h4>

                <div id="collaborators-list" style="margin:10px 0px;">
                    <div style="padding:3px;">
                        <div style="float:left; width:20px; overflow:hidden; position:relative; top:2px;"><i class="icon-asterisk"></i></div>
                        <div style="overflow:hidden;">
                            <b>{$album_owner_info["username"]} (owner)</b> {$you_html}
                            <br>
                            <span style="color:#666666">{$album_owner_info["email"]}</span>
                        </div>
                    </div>
HTML;


    foreach ($collaborators_info as $collaborator) {

        if ($collaborator["id"] == $album_info["user_id"]) {
            continue;
        }

        if ($collaborator["id"] == $_SESSION["user_id"]) {
            $you_html = "- this is you";
        } else {
            $you_html = "";
        }
        $html .= <<<HTML
                    <div id="collaborator-{$collaborator["id"]}" style="padding:3px;">
HTML;
    if ($collaborator["id"] == $_SESSION["user_id"] || $is_owner) {
        $html .= <<<HTML
                        <div style="float:left; width:20px; overflow:hidden; position:relative; top:2px;">
                            <a href="javascript:void(0);"
                               onclick="if (confirm('Sure you want to remove this collaborator?')) {
                                                deleteCollaborator({$collaborator["id"]},
                                                                   {$album_info["id"]},
                                                                   '{$album_info["token"]}');
                                            }">
                                <i class="icon-remove"></i>
                            </a>
                        </div>
HTML;
    } else {
        $html .= <<<HTML
                        <div style="float:left; width:20px; overflow:hidden; position:relative; top:2px;">
                                <i class="icon-user"></i>
                        </div>
HTML;
    }
    $html .= <<<HTML
                        <div style="overflow:hidden;">
                            <a href="/{$collaborator["username"]}"><b>{$collaborator["username"]}</b></a> {$you_html}
                            <br>
                            <span style="color:#666666">{$collaborator["email"]}</span>
                        </div>

                    </div>
HTML;
    }



    $html .= <<<HTML
                </div>

                <button style="margin-bottom:15px;" class="btn btn-primary btn-large" href="javascript:void(0);" onclick="showInviteModal();"><i class="icon-plus-sign"></i> Invite more collaborators</button>
HTML;

    if ($is_owner) {
        $html .= <<<HTML
                <label class="checkbox" style="margin:0px;">
                    <input type="checkbox" id="write-permissions-checkbox" onclick="changeAlbumWritePermissions();">
                    <span style="color:#666666;">
                        Allow people <i>not</i> listed above to add photos without my approval. <a href="#" id="write-permissions-qm" rel="popover"><i class="icon-question-sign"></i></a>
                        <span id="write-permissions-saved" style="display:none; color:green;"><i class='icon-ok-sign'></i> Saved!</span>
                    </span>
                </label>
HTML;
    }

    $html .= <<<HTML
            </div>
HTML;


    if ($is_owner) {

        $html .= <<<HTML
            <div>
                <h2>Privacy</h2>
                <h4 style="margin-bottom:10px;">Who is allowed to <b style="color:#444444">view</b> this album? Only the album owner can change this.</h4>

                <label class="radio" style="margin:0px;">
                    <input type="radio" name="album-privacy" id="album-privacy-1" value="1" checked="" onclick="changeAlbumPrivacy();">
                    <b>Album collaborators only</b> <span id="album-privacy-saved-1" style="display:none; color:green;"><i class='icon-ok-sign'></i> Saved!</span>
                </label>
                <p style="margin-left:20px; color:#666666;">
                    Just the folks listed above
                </p>


                <label class="radio" style="margin:0px;">
                    <input type="radio" name="album-privacy" id="album-privacy-2" value="2" checked="" onclick="changeAlbumPrivacy();">
                    <b>Anyone on the web</b> <span id="album-privacy-saved-2" style="display:none; color:green;"><i class='icon-ok-sign'></i> Saved!</span>
                </label>
                <p style="margin-left:20px; color:#666666;">
                     Album is visible to <i>everyone</i>, at <a href='{$g_www_root}/{$album_owner_info["username"]}/{$album_info["handle"]}'>{$g_www_root}/{$album_owner_info["username"]}/{$album_info["handle"]}</a>
                </p>

            </div>
HTML;


    } else if ($is_collaborator) {

        if ($album_info["read_permissions"] == 1) {
            $read_permissions_html = "Only album collaborators (listed above) can see this album.";
        } else if ($album_info["read_permissions"] == 2) {
            $read_permissions_html = <<<HTML
                <i>Anyone</i> can see this album at:
                <br>
                <a href='{$g_www_root}/{$album_owner_info["username"]}/{$album_info["handle"]}'>{$g_www_root}/{$album_owner_info["username"]}/{$album_info["handle"]}</a>
HTML;
        }

        $html .= <<<HTML
            <div>
                <h2>Privacy</h2>
                <h4 style="margin-bottom:10px;">Who is allowed to <b style="color:#444444">view</b> this album?</h4>
                <p>
                    {$read_permissions_html}
                </p>
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

var gAlbum;

$(function() {


    <?php

    if ($is_owner || $is_collaborator) {
        print("gAlbum = " . json_encode($album_info) . ";");
    } else {
        $album_info_without_token = $album_info;
        unset($album_info_without_token["token"]);
        print("gAlbum = " . json_encode($album_info_without_token) . ";");
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



    // Set some things if the user is logged in AND gAlbum is set. gAlbum is set
    // only if the user is EITHER a collaborator OR an owner
    if (isLoggedIn() && (typeof gAlbum != 'undefined')) {
        $("#album-privacy-" + gAlbum["read_permissions"]).prop('checked', true);

        if (gAlbum["write_permissions"] == 2) {
            $("#write-permissions-checkbox").attr("checked", true);
        }

        $("#write-permissions-qm").popover({
            content: "<b>If this is unchecked,</b> we'll email you for approval if someone who's <i>not</i> a collaborator tries to add a photo to this album. \
                      <br><br> \
                      <b>If this is checked,</b> <i>anyone in the world</i> can add photos to this album without your approval."
        });

        $(".ttip").tooltip();
    }

<?php

$output_js = <<<HTML
    preload([{$albumphotos_array_js}]);
HTML;

print($output_js);

?>


    if (getURLParameter("albumphoto") != "null") {

        $.fancybox.open($(".fancybox"), {
            prevEffect: 'none',
            nextEffect: 'none',
            padding: '1',
            arrows: false,
            index: <?php print($fancybox_image_to_show); ?>,
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
    }


});

</script>




<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->
<?php require("static_bottom.php"); ?>
<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->

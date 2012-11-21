    <?php
// |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
require("static_supertop.php");
// |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||


if (!isset($_GET["album_owner_username"]) || !isset($_GET["album_handle"])) {
    exit();
} else {
    $album_to_display = album_exists($_GET["album_handle"], $_GET["album_owner_username"]);

    if ($album_to_display == -1) {
        goto_homepage("#alert=5&username=" . $_GET["owner_username"]);
    }

    $album_info = get_album_info($album_to_display);
    // The below line makes gAlbum["username"] available
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
    goto_homepage("");
}

if ($album_info["username"] != $album_owner_info["username"]) {
    goto_homepage("");
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
    $photos_area_width = "span12";
    $tile_width = "span3";
} else {
    $photos_area_width = "span12";
    $tile_width = "span3";
}

?>
<?php require("static_top.php"); ?>
<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->




<?php


$html = <<<HTML

<div class="row-fluid">

    <div class="span12" style="overflow:hidden; position:relative;" id="main-div">
HTML;

print($html);


// The photos-panel div needs to be pushed over 300px to show the left-panel
$margin_for_photos_panel = "";

if ($is_owner || $is_collaborator) {

    $margin_for_photos_panel = "margin-left:300px";


    $html = <<<HTML
        <div style="width:300px; float:left; position:absolute; height:100%; left:0px;" id="left-panel">

            <!-- drawer handle -->
            <div style="width:12px; background-color:#222222; position:absolute; right:0px; top:0px; height:100%;" id="toggle-div">
                <a href="javascript:void(0);" onclick="toggleLeftPanel();"; style="height:100%; width:100%; display:block; font-size:20px;" class="white no-underline">

                </a>
            </div>


            <div style="margin:25px 30px 25px 15px;">
HTML;

    print($html);

    if ($is_owner) {
        $you_html = "- this is you";
    } else {
        $you_html = "";
    }

    $html = <<<HTML
            <div style="margin-bottom:50px">
                <h3 style="margin-top:0px;">Album Collaborators</h3>
                <h5>Collaborators can <b style="color:#fffff">add</b> photos and invite others</h5>

                <div id="collaborators-list" style="margin:10px 0px;">
                    <div style="padding:3px;">
                        <div style="float:left; width:20px; overflow:hidden; position:relative; top:2px;"><i class="icon-asterisk"></i></div>
                        <div style="overflow:hidden;">
                            <b>{$album_owner_info["username"]} (owner)</b> {$you_html}
                            <br>
                            <span class="gray">{$album_owner_info["email"]}</span>
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
                               class="no-underline"
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
                            <span class="gray">{$collaborator["email"]}</span>
                        </div>

                    </div>
HTML;
    }



    $html .= <<<HTML
                </div>

                <button style="margin-bottom:15px;" class="btn btn-primary btn-large btn-block" href="javascript:void(0);" onclick="showInviteModal();"><i class="icon-plus-sign"></i> Invite collaborators</button>
HTML;

    if ($is_owner) {
        $html .= <<<HTML
                <label class="checkbox" style="margin:0px;">
                    <input type="checkbox" id="write-permissions-checkbox" onclick="changeAlbumWritePermissions();">
                    <span class="gray">
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
                <h3>Privacy</h3>
                <h5 style="margin-bottom:10px;">Who is allowed to <b style="color:#ffffff">view</b> this album? Only the album owner can change this.</h5>

                <label class="radio" style="margin:0px;">
                    <input type="radio" name="album-privacy" id="album-privacy-1" value="1" checked="" onclick="changeAlbumPrivacy();">
                    <b>Album collaborators only</b> <span id="album-privacy-saved-1" style="display:none; color:green;"><i class='icon-ok-sign'></i> Saved!</span>
                </label>
                <p style="margin-left:20px;" class="gray">
                    Just the folks listed above can view this album
                </p>


                <label class="radio" style="margin:0px;">
                    <input type="radio" name="album-privacy" id="album-privacy-2" value="2" checked="" onclick="changeAlbumPrivacy();">
                    <b>Anyone on the web</b> <span id="album-privacy-saved-2" style="display:none; color:green;"><i class='icon-ok-sign'></i> Saved!</span>
                </label>
                <p style="margin-left:20px;" class="gray">
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
                <h3>Privacy</h3>
                <h5 style="margin-bottom:10px;">Who is allowed to <b style="color:#444444">view</b> this album?</h5>
                <p>
                    {$read_permissions_html}
                </p>
            </div>
HTML;

    }

    $html .= <<<HTML
            </div>
        </div> <!-- left-panel -->

HTML;

    print($html);

}

?>

        <div style="<? print($margin_for_photos_panel); ?>" id="photos-panel">
            <div style="min-height:500px;">

<?php

$albumphotos_array = get_albumphotos_info($album_to_display);
$albumphotos_array_js = "";

$photo_owners = array();

// The first albumphoto that is VISIBLE is 0, the second is 1, and so on. We
// cannot simply use $i in the loop below because some albumphotos in the array
// won't be visible (hence the "continue").
$index_of_albumphoto_in_album = 0;


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
        <div class="{$tile_width} tile" id="albumphoto-{$albumphoto_id}">

            <div style="position:relative">

                <a href="/{$album_info["username"]}/{$album_info["handle"]}/{$albumphoto_id}">

                    <img class="albumphoto-image" id="image-{$albumphoto_id}" src='{$g_s3_root}/{$albumphotos_array[$i]["s3_url"]}_cropped{$is_filtered}' owner-id="{$albumphotos_array[$i]["photo_owner_id"]}" albumphoto-s3="{$albumphotos_array[$i]["s3_url"]}_cropped{$is_filtered}">

                    <div class="albumphoto-owner">
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

*/

////////////////////////////////////////////////////////////////////////////////
// END FACEBOOK AND PINTEREST LIKE BUTTON
////////////////////////////////////////////////////////////////////////////////


    $display_comment_count = "";
    if ($albumphotos_array[$i]["num_comments"] == 0) {
        $display_comment_count = "style='display:none'";
    }

    $html .= <<<HTML
            <div class="comment-count big-icon">
                <a href="javascript:void(0)" class="no-underline" onclick="showCommentsModal($albumphoto_id, '{$albumphotos_array[$i]["s3_url"]}_cropped{$is_filtered}', '{$g_s3_root}/{$albumphotos_array[$i]["s3_url"]}_big{$is_filtered}');">
                    <i class="icon-comments"></i>
                </a>
                <span id="comment-count-{$albumphoto_id}" $display_comment_count class="count-number">
                    {$albumphotos_array[$i]["num_comments"]}
                </span>
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
            <div id="albumphoto-like-{$albumphoto_id}" class="albumphoto-like big-icon" liked="{$liked}">
                <a href="javascript:void(0)" class="no-underline" onclick="likeAlbumphoto({$albumphoto_id}, {$liker_id}, {$albumphotos_array[$i]["photo_owner_id"]}, '{$albumphotos_array[$i]["s3_url"]}_cropped{$is_filtered}');">
                     <i id="albumphoto-like-heart-{$albumphoto_id}" class="icon-heart $heart_class"></i>
                </a>
                <a id="albumphoto-like-count-link-{$albumphoto_id}" href="javascript:void(0)" class="no-underline count-number" onclick="showLikersModal({$albumphoto_id});" {$display_like_count}>
                    <span id="albumphoto-like-count-{$albumphoto_id}">{$albumphotos_array[$i]["num_likes"]}</span>
                </a>
            </div>
HTML;

    if (isset($albumphotos_array[$i]["latitude"]) && $albumphotos_array[$i]["longitude"]) {
        $html .= <<<HTML
            <div id="map-marker-{$albumphoto_id}" class="map-marker big-icon">
                <a href="javascript:void(0)" class="no-underline" onclick="showMapModal({$albumphotos_array[$i]["latitude"]},{$albumphotos_array[$i]["longitude"]});">
                     <i id="map-marker-open-{$albumphoto_id}" class="icon-map-marker"></i>
                </a>
            </div>


HTML;
}


    $html .= <<<HTML

            <div class="containing">

HTML;


    // If there IS a caption -------------------------------------------------//

    if (isset($albumphotos_array[$i]["caption"]) && $albumphotos_array[$i]["caption"] != "") {

        $edit_caption_string = "";
        if ($is_owner || $is_collaborator) {
            $edit_caption_string = <<<HTML
                    <a id="add-caption-{$albumphotos_array[$i]["id"]}" href="javascript:void(0)" onclick="showCaptionModal({$albumphotos_array[$i]["id"]}, {$albumphotos_array[$i]["photo_owner_id"]}, '{$albumphotos_array[$i]["s3_url"]}_cropped{$is_filtered}')" class="no-underline">
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
                        <a id="add-caption-{$albumphotos_array[$i]["id"]}" href="javascript:void(0)" onclick="showCaptionModal({$albumphotos_array[$i]["id"]}, {$albumphotos_array[$i]["photo_owner_id"]}, '{$albumphotos_array[$i]["s3_url"]}_cropped{$is_filtered}')" class="no-underline">
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










    if ($is_owner || $is_collaborator) {
        $html .= <<<HTML
            <div class="photo-controls" style="display:none;  margin:3px 0px;">

                <div rel="tooltip" title="Post to Facebook" class="btn ttip btn-inverse" onclick="showFacebookModal('{$album_owner_info["username"]}', '{$album_info["handle"]}', {$albumphoto_id});">
                    <i class="icon-facebook"></i>
                </div>

                <div class="btn-group" style="float:right; margin-left:5px;">
                    <button rel="tooltip" title="Filters" id="filter-{$albumphoto_id}" class="btn btn-inverse dropdown-toggle ttip" data-toggle="dropdown" data-loading-text="Filtering...">
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
                <button id="save-{$albumphoto_id}" class="btn btn-primary" href="#" style="float:right; margin-left:5px; display:none" onclick="saveFiltered({$albumphoto_id},
                                                                                                                               '{$g_www_root}/proxy.php?url={$g_s3_root}/{$albumphotos_array[$i]["s3_url"]}_cropped&mime_type=image/jpeg',
                                                                                                                               '{$g_www_root}/proxy.php?url={$g_s3_root}/{$albumphotos_array[$i]["s3_url"]}_big&mime_type=image/jpeg',
                                                                                                                               '{$g_s3_root}/{$albumphotos_array[$i]["s3_url"]}_big'
                                                                                                                               );">
                    Save
                </button>



                <div class="btn-group" style="float:right;">
                    <button rel="tooltip" title="Options" id="options-{$albumphoto_id}" class="btn btn-inverse dropdown-toggle ttip" data-toggle="dropdown">
                        <i class="icon-wrench"></i> <i class="icon-sort-down icon-white"></i>
                    </button>
                    <ul class="dropdown-menu pull-right">

                        <li>
                            <a href="javascript:void(0);" onclick="rotatePhoto(0, {$albumphotos_array[$i]["id"]}, {$albumphotos_array[$i]["album_id"]}, '{$albumphotos_array[$i]["token"]}', '{$albumphotos_array[$i]["s3_url"]}');">
                                <i class="icon-undo"></i> Rotate left
                            </a>
                        </li>

                        <li>
                            <a href="javascript:void(0);" onclick="rotatePhoto(1, {$albumphotos_array[$i]["id"]}, {$albumphotos_array[$i]["album_id"]}, '{$albumphotos_array[$i]["token"]}', '{$albumphotos_array[$i]["s3_url"]}');">
                                <i class="icon-repeat"></i> Rotate right
                            </a>
                        </li>

                        <li>
                            <a href="javascript:void(0);" onclick="setAsAlbumCover({$albumphotos_array[$i]["id"]}, {$albumphotos_array[$i]["album_id"]}, '{$albumphotos_array[$i]["token"]}');">
                                <i class="icon-picture"></i> Set as album cover
                            </a>
                        </li>


                        <li>
                            <a href="javascript:void(0);" onclick="if (confirm('Really delete this photo?')) {
                                                                            deletePhotoFromAlbum({$albumphotos_array[$i]["id"]}, '{$albumphotos_array[$i]["token"]}', '{$albumphotos_array[$i]["s3_url"]}');
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


            </div>

            <div id="cover-{$albumphoto_id}" style="position:absolute; top:0px; left:0px; width:100%; height:100%; background-color:black; opacity:0.7; text-align:center; display:none;">
                <span id="cover-message-{$albumphoto_id}" style="position:relative; top:45%; color:#ffffff; font-size:26px;">Saving...</span>
            </div>


        </div>
HTML;





    $html .= <<<HTML
        </div>
HTML;

    print($html);

    $index_of_albumphoto_in_album++;
}

$albumphotos_array_js = rtrim($albumphotos_array_js, ",");

?>
                <div style="clear:both"></div>

            </div>
        </div> <!-- photos-panel -->
    </div> <!-- span-12 -->
</div> <!-- row-fluid -->

<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->
<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->
<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->
<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->
<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->
<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->
<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->
<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->
<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->















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



    $("#toggle-div").hover(
        function() {
            $("#toggle-div").css("background-color", "#333333");
        },
        function() {
            $("#toggle-div").css("background-color", "#222222");
        }
    );







    $(".tile").each(function(index) {
        $(this).mouseenter(function() {
            $(this).find(".albumphoto-caption").stop(true, true).show();
        });
        $(this).mouseleave(function() {
            $(this).find(".albumphoto-caption").stop(true, true).fadeOut();
        });
    });


    $(".tile").each(function(index) {
        $(this).mouseenter(function() {
            $(this).find(".tile-options, .photo-controls").stop(true, true).show();
        });
        $(this).mouseleave(function() {
            $(this).find(".tile-options, .photo-controls").stop(true, true).fadeOut();
        });
    });












    $("#left-panel").css("min-height", ($(window).height() - $('#left-panel').offset().top));
    $("#main-div").css("min-height", ($(window).height() - $('#left-panel').offset().top));


<?php

$output_js = <<<HTML
    // preload([{$albumphotos_array_js}]);
HTML;

print($output_js);

?>


});

</script>




<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->
<?php require("static_bottom.php"); ?>
<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->

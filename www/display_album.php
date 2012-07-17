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
    $collaborators_info = get_collaborators_info($album_to_display);

    if ($g_debug) {
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

print("<!-- is_collaborator: $is_collaborator -->\n");
print("<!-- is_owner: $is_owner -->\n");

$page_title = <<<HTML
    {$album_info["handle"]}<span style="color:#000000">@<a href="/{$album_owner_info["username"]}">{$album_owner_info["username"]}</a>.{$g_zipio}.com</span> <!-- <i class="icon-info-sign big-icon"></i> -->
HTML;

$page_subtitle = "To add photos, email them to the above address";

if ($is_owner || $is_collaborator) {
    $photos_area_width = "span9";
} else {
    $photos_area_width = "span12";
}

?>



<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->
<?php require("static_top.php"); ?>
<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->



<div class="row">

    <div class="<?php print($photos_area_width); ?>">
        <div class="row">

<?php

$albumphotos_array = get_albumphotos_info($album_to_display);
$albumphotos_array_js = "";

$photo_owners = array();

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

    if (isset($album_info["likes"][$albumphoto_id])) {
        $like_count = $album_info["likes"][$albumphoto_id];
    } else {
        $like_count = 0;
    }

    $html = <<<HTML
        <div class="span3 tile" id="albumphoto-{$albumphoto_id}" likes={$like_count}>

            <a id="fancybox-{$albumphoto_id}"
               class="fancybox"
               data-fancybox-type="image"
               rel="fancybox"
               href="{$g_s3_root}/{$albumphotos_array[$i]["s3_url"]}_big{$is_filtered}">

                <img id="image-{$albumphoto_id}" src='{$g_s3_root}/{$albumphotos_array[$i]["s3_url"]}_cropped{$is_filtered}'>

                <div class="album-privacy">
                    posted by <b>{$photo_owners[$albumphotos_array[$i]["photo_owner_id"]]["username"]}</b>
                </div>
            </a>
HTML;


    if ($is_owner || $is_collaborator || $album_info["write_permissions"] == 2) {
        $user_id = $_SESSION['user_id'];

        if (isset($album_info["user_likes"][$user_id."_".$albumphoto_id])) {
            $heart_color = "red";
        } else {
            $heart_color = "grey";
        }
        
        $html .= <<<HTML
            <div class="tile-options" style="display:none;">
            <a href="javascript:void(0)" onclick="toggleLikePhoto($user_id, $albumphoto_id, $album_to_display);">
            <img alt="{$like_count}" id='like-{$albumphoto_id}' src='{$g_www_root}/{$heart_color}_heart.png' style='float:left; margin-right:5px;'>
            </a>



                <div class="btn-group" style="float:left; margin-right:5px;">
                    <button id="filter-{$albumphoto_id}" class="btn btn-inverse dropdown-toggle" data-toggle="dropdown" data-loading-text="Filtering...">
                        Filter <i class="icon-sort-down icon-white"></i>
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <a href="javascript:void(0);" onclick="resetPhotoToOriginal({$albumphoto_id},
                                                                                        '{$g_s3_root}/{$albumphotos_array[$i]["s3_url"]}_cropped',
                                                                                        '{$g_s3_root}/{$albumphotos_array[$i]["s3_url"]}_big');">
                                Original
                            </a>
                        </li>
                        <li><a href="javascript:void(0);" onclick="applyFilter({$albumphoto_id}, '{$g_www_root}/proxy.php?url={$g_s3_root}/{$albumphotos_array[$i]["s3_url"]}_cropped&mime_type=image/jpeg', 1);">1</a></li>
                        <li><a href="javascript:void(0);" onclick="applyFilter({$albumphoto_id}, '{$g_www_root}/proxy.php?url={$g_s3_root}/{$albumphotos_array[$i]["s3_url"]}_cropped&mime_type=image/jpeg', 2);">2</a></li>
                        <li><a href="javascript:void(0);" onclick="applyFilter({$albumphoto_id}, '{$g_www_root}/proxy.php?url={$g_s3_root}/{$albumphotos_array[$i]["s3_url"]}_cropped&mime_type=image/jpeg', 3);">3</a></li>
                        <li><a href="javascript:void(0);" onclick="applyFilter({$albumphoto_id}, '{$g_www_root}/proxy.php?url={$g_s3_root}/{$albumphotos_array[$i]["s3_url"]}_cropped&mime_type=image/jpeg', 4);">4</a></li>
                        <li><a href="javascript:void(0);" onclick="applyFilter({$albumphoto_id}, '{$g_www_root}/proxy.php?url={$g_s3_root}/{$albumphotos_array[$i]["s3_url"]}_cropped&mime_type=image/jpeg', 5);">5</a></li>
                        <li><a href="javascript:void(0);" onclick="applyFilter({$albumphoto_id}, '{$g_www_root}/proxy.php?url={$g_s3_root}/{$albumphotos_array[$i]["s3_url"]}_cropped&mime_type=image/jpeg', 6);">6</a></li>
                        <li><a href="javascript:void(0);" onclick="applyFilter({$albumphoto_id}, '{$g_www_root}/proxy.php?url={$g_s3_root}/{$albumphotos_array[$i]["s3_url"]}_cropped&mime_type=image/jpeg', 7);">7</a></li>
                        <li><a href="javascript:void(0);" onclick="applyFilter({$albumphoto_id}, '{$g_www_root}/proxy.php?url={$g_s3_root}/{$albumphotos_array[$i]["s3_url"]}_cropped&mime_type=image/jpeg', 8);">8</a></li>
                        <li><a href="javascript:void(0);" onclick="applyFilter({$albumphoto_id}, '{$g_www_root}/proxy.php?url={$g_s3_root}/{$albumphotos_array[$i]["s3_url"]}_cropped&mime_type=image/jpeg', 10);">10</a></li>
                    </ul>
                </div>
                <button id="save-{$albumphoto_id}" class="btn btn-primary" href="#" style="display:none" onclick="saveFiltered({$albumphoto_id},
                                                                                                                               '{$g_www_root}/proxy.php?url={$g_s3_root}/{$albumphotos_array[$i]["s3_url"]}_cropped&mime_type=image/jpeg',
                                                                                                                               '{$g_www_root}/proxy.php?url={$g_s3_root}/{$albumphotos_array[$i]["s3_url"]}_big&mime_type=image/jpeg',
                                                                                                                               '{$g_s3_root}/{$albumphotos_array[$i]["s3_url"]}_big'
                                                                                                                               );">
                    Save
                </button>
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

// RIGHT SIDE PANELS

if ($is_owner || $is_collaborator) {

    if ($is_owner) {
        $you_html = "- this is you";
    } else {
        $you_html = "";
    }

    $html = <<<HTML
        <div class="span3">
            <div>
                <h2>Album Collaborators</h2>
                <h4>Collaborators can <b style="color:#444444">add</b> photos and invite others</h4>

                <div id="collaborators-list" style="margin:10px 0px;">
                    <div style="padding:3px;">
                        <div style="float:left; width:20px; overflow:hidden; position:relative; top:2px;">&nbsp;</div>
                        <div style="overflow:hidden;">
                            <b>{$album_owner_info["username"]} (owner)</b> {$you_html}
                            <br>
                            <span style="color:#666666">{$album_owner_info["email"]}</span>
                        </div>
                    </div>
HTML;


    foreach ($collaborators_info as $collaborator) {
        if ($collaborator["id"] == $_SESSION["user_id"]) {
            $you_html = "- this is you";
        } else {
            $you_html = "";
        }
        $html .= <<<HTML
                    <div id="collaborator-{$collaborator["id"]}" style="padding:3px;">

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

            <div style="height:50px"></div>
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
            $(".tile").find(".album-privacy").stop(true, true).delay(500).fadeOut();
            $(this).find(".tile-options, .album-privacy").stop(true, true).show();
        });
        $(this).mouseleave(function() {
            $(".tile").find(".tile-options, .album-privacy").stop(true, true).delay(500).fadeOut();
        });
    });

    // Set some things if the user is logged in
    if (isLoggedIn()) {
        $("#album-privacy-" + gAlbum["read_permissions"]).prop('checked', true);

        if (gAlbum["write_permissions"] == 2) {
            $("#write-permissions-checkbox").attr("checked", true);
        }

        $("#write-permissions-qm").popover({
            content: "<b>If this is unchecked,</b> we'll email you for approval if someone who's <i>not</i> a collaborator tries to add a photo to this album. \
                      <br><br> \
                      <b>If this is checked,</b> <i>anyone in the world</i> can add photos to this album without your approval."
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

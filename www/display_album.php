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

    if ($g_debug) {
        print("<!-- GET: " . print_r($_GET, true) . "-->");
        print("<!-- album_to_display: $album_to_display -->\n");
        print("<!-- album_owner_info['username']: " . $album_owner_info["username"] . " -->\n");
        print("<!-- album_info: " . print_r($album_info, true) . "-->");
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

print("<!-- is_collaborator: $is_collaborator -->\n");
print("<!-- is_owner: $is_owner -->\n");

$page_title = <<<HTML
    {$album_info["handle"]}<span style="color:#000000">@<a href="/{$album_owner_info["username"]}">{$album_owner_info["username"]}</a>.zipio.com</span> <!-- <i class="icon-info-sign big-icon"></i> -->
HTML;

$page_subtitle = "To add a photo, email the above address";

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
    if ($albumphotos_array[$i]["visible"] == 0) {
        $opacity = "0.4";
    } else {
        $opacity = "1.0";
    }

    $albumphotos_array_js .= "'" . $g_s3_root . "/" . $albumphotos_array[$i]["s3_url"] . "_big',";

    if ($albumphotos_array[$i]["filtered"] > 0) {
        $is_filtered = "_filtered";
    } else {
        $is_filtered = "";
    }

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

    $html = <<<HTML
        <div class="span3 tile" id="albumphoto-{$albumphotos_array[$i]["id"]}">
            <a id="fancybox-{$albumphotos_array[$i]["id"]}" class="fancybox" data-fancybox-type="image" rel="fancybox" href="{$g_s3_root}/{$albumphotos_array[$i]["s3_url"]}_big">
                <img id="image-{$albumphotos_array[$i]["id"]}" style='opacity:{$opacity};' src='{$g_s3_root}/{$albumphotos_array[$i]["s3_url"]}_cropped{$is_filtered}'>
            <div class="album-privacy">
                by <b>{$photo_owners[$albumphotos_array[$i]["photo_owner_id"]]["username"]}</b>
            </div>
            </a>
HTML;

    if ($is_owner) {

        $html .= <<<HTML
            <div class="tile-options" style="display:none">
                <div class="btn-group">
                    <button class="btn btn-inverse dropdown-toggle" data-toggle="dropdown">
                        <i class="icon-sort-down icon-white"></i>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a href="javascript:void(0);" onclick="deletePhotoFromAlbum({$albumphotos_array[$i]["id"]}, '{$albumphotos_array[$i]["token"]}');"><i class="icon-trash"></i> Delete this photo</a></li>
                        <li><a href="javascript:void(0);" onclick="applyFilter({$albumphotos_array[$i]["id"]},'{$g_www_root}/proxy.php?url={$g_s3_root}/{$albumphotos_array[$i]["s3_url"]}_cropped&mimeType=image/jpeg', 1);">1</a></li>
                        <li><a href="javascript:void(0);" onclick="applyFilter({$albumphotos_array[$i]["id"]},'{$g_www_root}/proxy.php?url={$g_s3_root}/{$albumphotos_array[$i]["s3_url"]}_cropped&mimeType=image/jpeg', 2);">2</a></li>
                        <li><a href="javascript:void(0);" onclick="applyFilter({$albumphotos_array[$i]["id"]},'{$g_www_root}/proxy.php?url={$g_s3_root}/{$albumphotos_array[$i]["s3_url"]}_cropped&mimeType=image/jpeg', 3);">3</a></li>
                        <li><a href="javascript:void(0);" onclick="applyFilter({$albumphotos_array[$i]["id"]},'{$g_www_root}/proxy.php?url={$g_s3_root}/{$albumphotos_array[$i]["s3_url"]}_cropped&mimeType=image/jpeg', 4);">4</a></li>
                        <li><a href="javascript:void(0);" onclick="applyFilter({$albumphotos_array[$i]["id"]},'{$g_www_root}/proxy.php?url={$g_s3_root}/{$albumphotos_array[$i]["s3_url"]}_cropped&mimeType=image/jpeg', 5);">5</a></li>
                        <li><a href="javascript:void(0);" onclick="applyFilter({$albumphotos_array[$i]["id"]},'{$g_www_root}/proxy.php?url={$g_s3_root}/{$albumphotos_array[$i]["s3_url"]}_cropped&mimeType=image/jpeg', 6);">6</a></li>
                        <li><a href="javascript:void(0);" onclick="applyFilter({$albumphotos_array[$i]["id"]},'{$g_www_root}/proxy.php?url={$g_s3_root}/{$albumphotos_array[$i]["s3_url"]}_cropped&mimeType=image/jpeg', 7);">7</a></li>
                        <li><a href="javascript:void(0);" onclick="applyFilter({$albumphotos_array[$i]["id"]},'{$g_www_root}/proxy.php?url={$g_s3_root}/{$albumphotos_array[$i]["s3_url"]}_cropped&mimeType=image/jpeg', 8);">8</a></li>
                        <li><a href="javascript:void(0);" onclick="applyFilter({$albumphotos_array[$i]["id"]},'{$g_www_root}/proxy.php?url={$g_s3_root}/{$albumphotos_array[$i]["s3_url"]}_cropped&mimeType=image/jpeg', 10);">10</a></li>
                        <li><a href="javascript:void(0);" onclick="saveFiltered({$albumphotos_array[$i]["id"]},'{$g_www_root}/proxy.php?url={$g_s3_root}/{$albumphotos_array[$i]["s3_url"]}_cropped&mimeType=image/jpeg','{$g_www_root}/proxy.php?url={$g_s3_root}/{$albumphotos_array[$i]["s3_url"]}_big&mimeType=image/jpeg');">Save</a></li>
                        <li><a href="javascript:void(0);" onclick="undoFilter('{$albumphotos_array[$i]["id"]}');">Undo</a></li>
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



<?php

if ($is_owner || $is_collaborator) {

    $collaborators_info = get_collaborators_info($album_to_display);



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
                            <span style="color:#999999">{$album_owner_info["email"]}</span>
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
                            <b>{$collaborator["username"]}</b> {$you_html}
                            <br>
                            <span style="color:#999999">{$collaborator["email"]}</span>
                        </div>
                    </div>
HTML;
    }

    $html .= <<<HTML
                </div>
                <button class="btn btn-primary btn-large" href="javascript:void(0);" onclick="showInviteModal();"><i class="icon-plus-sign"></i> Invite more collaborators</button>
            </div>

            <div style="height:70px"></div>

            <div>
                <h2>Privacy</h2>
                <h4 style="margin-bottom:10px;">Who is allowed to <b style="color:#444444">view</b> this album?</h4>

                <label class="radio">
                    <input type="radio" name="album-privacy" id="album-privacy-1" value="1" checked="" onclick="changeAlbumPrivacy();">
                    <b>Album collaborators only</b> <span id="album-privacy-saved-1" style="display:none; color:green;"><i class='icon-ok-sign'></i> Saved!</span>
                </label>
                <p style="margin-left:20px; color:#999999;">
                    Just the folks listed above
                </p>


                <label class="radio">
                    <input type="radio" name="album-privacy" id="album-privacy-2" value="2" checked="" onclick="changeAlbumPrivacy();">
                    <b>Anyone on the web</b> <span id="album-privacy-saved-2" style="display:none; color:green;"><i class='icon-ok-sign'></i> Saved!</span>
                </label>
                <p style="margin-left:20px; color:#999999;">
                     Album visible to all at {$g_www_root}/{$album_owner_info["username"]}/{$album_info["handle"]}
                </p>

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
            $(this).find(".tile-options").stop(true, true).show();
        });
        $(this).mouseleave(function() {
            $(this).find(".tile-options").stop(true, true).fadeOut();
        });
    });

    // Set the privacy radio button

    $("#album-privacy-" + gAlbum["read_permissions"]).prop('checked', true);

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

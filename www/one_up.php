<?php
// |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
require("static_supertop.php");
// |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||


if (!isset($_GET["albumphoto_id"])) {
    exit();
} else {
    $albumphoto_to_display = $_GET["albumphoto_id"];
    $albumphoto_info = get_albumphoto_info($albumphoto_to_display);
    $albumphoto_owner_info = get_user_info($albumphoto_info["photo_owner_id"]);
    $album_info = get_album_info($albumphoto_info["album_id"]);
    $album_owner_info = get_user_info($album_info["user_id"]);
    $album_info["username"] = get_username_from_user_id($album_info["user_id"]);
    if ($g_debug) {
        print("<!--" . $_SERVER["SCRIPT_FILENAME"] . "-->");
        print("<!-- albumphoto_to_display: $albumphoto_to_display -->\n");
        print("<!-- album_info: " . print_r($albumphoto_info, true) . "-->");
    }
}



////////////////////////////////////////////////////////////////////////////////
// Page-specific PHP goes here

if (is_logged_in()) {
    $user_id = $_SESSION['user_id'];
    $albumphoto_likes_info = get_albumphoto_likes_info($user_id, $album_info["id"]);
} else {
  $albumphotos_likes_info = array();
}


if ($albumphoto_info["filtered"] > 0) {
    $is_filtered = "_filtered";
} else {
    $is_filtered = "";
}



////////////////////////////////////////////////////////////////////////////////
// The following variables should be set

$page_title = <<<HTML
    <i class="icon-caret-left"></i> <a href="/{$album_owner_info["username"]}/{$album_info["handle"]}">Back to full album</a>
HTML;


$page_subtitle = <<<HTML
HTML;

?>

<?php require("static_top.php"); ?>
<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->



<?php

for ($i = 0; $i < count($album_info["albumphoto_ids"]); $i++) {
    if ($album_info["albumphoto_ids"][$i] == $albumphoto_to_display) {

        if ($i == 0) {
            $prev_albumphoto_id = $album_info["albumphoto_ids"][count($album_info["albumphoto_ids"]) - 1];
        } else {
            $prev_albumphoto_id = $album_info["albumphoto_ids"][$i - 1];
        }

        if ($i == count($album_info["albumphoto_ids"]) - 1) {
            $next_albumphoto_id = $album_info["albumphoto_ids"][0];
        } else {
            $next_albumphoto_id = $album_info["albumphoto_ids"][$i + 1];
        }

    }
}

// Get the next photo so we have its URL so we can preload it below
$next_albumphoto_info = get_albumphoto_info($next_albumphoto_id);
$prev_albumphoto_info = get_albumphoto_info($prev_albumphoto_id);

if ($next_albumphoto_info["filtered"] > 0) {
    $next_is_filtered = "_filtered";
} else {
    $next_is_filtered = "";
}

if ($prev_albumphoto_info["filtered"] > 0) {
    $prev_is_filtered = "_filtered";
} else {
    $prev_is_filtered = "";
}


if (isset($albumphoto_likes_info) && array_key_exists($albumphoto_info["id"], $albumphoto_likes_info)) {
    $heart_class = "heart-red";
    $liked = 1;
} else {
    $heart_class = "heart-gray";
    $liked = 0;
}

$photo_owner_id = $albumphoto_info["photo_owner_id"];
if (isset($user_id)) {
    $liker_id = $user_id;
} else {
    $liker_id = 0;
}

$display_like_count = "";
if ($albumphoto_info["num_likes"] == 0) {
    $display_like_count = "style='display:none'";
}


$html = <<<HTML

<div class="row-fluid">
    <div class="span8"
         style="background-color:#eeeeee; text-align:center; margin-bottom:10px; position:relative"
         id="one-up-photo"
         albumphoto-id="{$albumphoto_info["id"]}"
         albumphoto-s3="{$albumphoto_info["s3_url"]}_big{$is_filtered}">
        <img src="{$g_s3_root}/{$albumphoto_info["s3_url"]}_big{$is_filtered}"
             style="max-height:100%;"
             id="image-{$albumphoto_info["id"]}"
             owner-id="{$albumphoto_info["photo_owner_id"]}"
             albumphoto-s3="{$albumphoto_info["s3_url"]}_cropped{$is_filtered}"></img>
        <div class="albumphoto-owner">
            by <b>{$albumphoto_owner_info["username"]}</b>
        </div>
        <div id="albumphoto-like-{$albumphoto_info['id']}" class="albumphoto-like" liked="{$liked}">
            <a id="albumphoto-like-count-link-{$albumphoto_info['id']}" href="javascript:void(0)" class="no-underline count-number" onclick="showLikersModal({$albumphoto_info['id']});" {$display_like_count}>
                <span id="albumphoto-like-count-{$albumphoto_info['id']}">{$albumphoto_info["num_likes"]}</span>
            </a>
            <a href="javascript:void(0)" class="no-underline" onclick="likeAlbumphoto({$albumphoto_info['id']}, {$liker_id}, {$albumphoto_info["photo_owner_id"]}, '{$albumphoto_info["s3_url"]}_cropped{$is_filtered}');">
                 <i id="albumphoto-like-heart-{$albumphoto_info['id']}" class="icon-heart $heart_class"></i>
            </a>
        </div>
    </div>

    <div class="span4">
        <div class="well well-small" style="overflow:hidden">
            <div style="float:left">
                <a href="/one_up.php?albumphoto_id={$prev_albumphoto_id}" class="no-underline" id="link-prev">
                    <i class="icon-caret-left"></i> <img style="height:50px; width:50px" src="{$g_s3_root}/{$prev_albumphoto_info["s3_url"]}_cropped{$prev_is_filtered}"></a>
            </div>
            <div style="float:right">
                <a href="/one_up.php?albumphoto_id={$next_albumphoto_id}" class="no-underline" id="link-next">
                    <img style="height:50px; width:50px" src="{$g_s3_root}/{$next_albumphoto_info["s3_url"]}_cropped{$next_is_filtered}"> <i class="icon-caret-right"></i></a>
            </div>
        </div>
HTML;

    $html .= <<<HTML
        <div style="clear:both; margin-top:20px;">
            <h3 style="margin-top:0px;">Comments</h3>
            <div id="one-up-comments">
                <span style="color:#999999">No comments yet. Be the first!</span>
            </div>
        </div>
HTML;


        if (is_logged_in()) {

            $html .= <<<HTML

        <div style="margin-top:10px;">
            <textarea id="one-up-comment-input" class="input-xlarge" style="width:100%; box-sizing:border-box; height:50px;" placeholder="Add a comment..."></textarea>
            <button onclick="submitCommentOneUp();"
                    class="btn btn-primary btn-block" id="one-up-comment-submit" data-loading-text="Adding comment...">
                    Add comment
            </button>
        </div>
HTML;

        }


$html .= <<<HTML






        <div class="btn-group" style="margin-top:10px;">
            <button rel="tooltip" title="Options" id="options-{$albumphoto_info['id']}" class="btn dropdown-toggle ttip" data-toggle="dropdown">
                <i class="icon-wrench"></i> <i class="icon-sort-down icon-white"></i>
            </button>
            <ul class="dropdown-menu">

                <li>
                    <a href="javascript:void(0);" onclick="rotatePhoto(0, {$albumphoto_info["id"]}, {$albumphoto_info["album_id"]}, '{$albumphoto_info["token"]}', '{$albumphoto_info["s3_url"]}');">
                        <i class="icon-undo"></i> Rotate left
                    </a>
                </li>

                <li>
                    <a href="javascript:void(0);" onclick="rotatePhoto(1, {$albumphoto_info["id"]}, {$albumphoto_info["album_id"]}, '{$albumphoto_info["token"]}', '{$albumphoto_info["s3_url"]}');">
                        <i class="icon-repeat"></i> Rotate right
                    </a>
                </li>

                <li>
                    <a href="javascript:void(0);" onclick="setAsAlbumCover({$albumphoto_info["id"]}, {$albumphoto_info["album_id"]}, '{$albumphoto_info["token"]}');">
                        <i class="icon-picture"></i> Set as album cover
                    </a>
                </li>


                <li>
                    <a href="javascript:void(0);" onclick="if (confirm('Really delete this photo?')) {
                                                                   deletePhotoFromAlbum({$albumphoto_info["id"]}, '{$albumphoto_info["token"]}', '{$albumphoto_info["s3_url"]}');
                                                                   window.location.href = $('#link-next').attr('href') + '#alert=9';
                                                               }">
                        <i class="icon-trash"></i> Delete this photo
                    </a>
                </li>

            </ul>
        </div>



    </div>
</div>
HTML;

print($html);




$output_js = <<<HTML
    preload(['{$g_s3_root}/{$next_albumphoto_info["s3_url"]}_big{$next_is_filtered}']);
HTML;


?>


<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->
<?php require("static_scripts.php"); ?>
<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->









<script>

var gAlbumphoto;
var gAlbum;

$(function() {

    <?php

    print("gAlbumphoto = " . json_encode($albumphoto_info) . ";");
    print("gAlbum = " . json_encode($album_info) . ";");

    print($output_js);

    ?>

    if (isLoggedIn()) {
        reloadCommentsOneUp(gAlbumphoto["id"], gUser["id"]);
    }


    $(window).keydown(function(event) {
        if (event.which == 39 && !/input|text/.test((event.target.toString()).toLowerCase())) {
            window.location.href = $('#link-next').attr('href');
        }

        if (event.which == 37 && !/input|text/.test((event.target.toString()).toLowerCase())) {
            window.location.href = $('#link-prev').attr('href');
        }

    });


});

</script>




<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->
<?php require("static_bottom.php"); ?>
<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->
























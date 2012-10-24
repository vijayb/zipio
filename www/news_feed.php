<?php
// |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
require("static_supertop.php");
// |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||

if (!isset($_GET["user_username"])) {
    exit();
} else {
    $user_id = get_user_id_from_username($_GET["user_username"]);
    $user_username = get_username_from_user_id($user_id);
    $user_info = get_user_info($user_id);

    if ($g_debug) {
        print("<!-- user_id: $user_id -->\n");
        print("<!-- user_username: $user_username -->\n");
        print("<!-- user_info: " . print_r($user_info, true) . "-->");
    }
}

// |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||| //
// |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||| //
// |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||| //

if ($user_id == 0) {
    goto_homepage("#alert=5&username=" . $_GET["user_username"]);
}

if (!is_logged_in() || $user_id != $_SESSION["user_id"]) {
    goto_homepage("");
}

$page_title = "$user_username's News Feed";
$page_subtitle = "Here's what's going on at Zipio";


?>
<?php require("static_top.php"); ?>
<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->


<?php

$events_array = get_events_array($user_id, 0);


$html = <<<HTML
<div class="row" style="padding:20px;">
    <div class="span8">
HTML;


for ($i = 0; $i < count($events_array); $i++) {




    $actor_info = get_user_info($events_array[$i]["actor_id"]);

    if (isset($events_array[$i]["album_id"])) {
        $album_info = get_album_info($events_array[$i]["album_id"]);
        $album_user_info = get_user_info($album_info["user_id"]);
        $object_user_info = $album_user_info;
        $object_user_username = $album_user_info["username"];
    }

    if (isset($events_array[$i]["albumphoto_id"])) {
        $albumphoto_info = get_albumphoto_info($events_array[$i]["albumphoto_id"]);
        $albumphoto_user_info = get_user_info($albumphoto_info["photo_owner_id"]);
        $object_user_info = $albumphoto_user_info;
        $object_user_username = $albumphoto_user_info["username"];
        $truncated_caption = trim(substr($albumphoto_info["caption"], 0, 40)) . '...';
    }

    if (isset($events_array[$i]["comment_id"])) {
        $comment_info = get_comment_info($events_array[$i]["comment_id"]);
        $commenter_info = get_user_info($comment_info["commenter_id"]);
        $object_user_info = $commenter_info;
        $object_user_username = $commenter_info["username"];

        $truncated_comment = trim(substr($comment_info["comment"], 0, 40)) . '...';

    }

    $link_albumphoto_one_up = "/" . $album_user_info['username'] . "/" . $album_info['handle'] . "/" . $albumphoto_info['id'];
    $link_actor = "/" . $actor_info["username"];
    $img_albumphoto_cropped = $g_s3_root . "/" . $albumphoto_info['s3_url'] . "_cropped";
    if ($albumphoto_info["filtered"] == 1) {
        $img_albumphoto_cropped .= "_filtered";
    }

    $actor_username = $actor_info["username"];

    if ($actor_info["id"] == $user_info["id"]) {
        $actor_username = "You";
    }

    if ($object_user_info["id"] == $user_info["id"]) {
        $object_user_username = "your";
    } else {
        $object_user_username .= "'s";
    }

    if ($object_user_info["id"] == $actor_info["id"]) {
        $object_user_username = "their";
    }

    if ($album_user_info["id"] == $user_info["id"]) {
        $album_user_username = "your";
    } else {
        $album_user_username = $album_user_info["username"] . "'s";
    }

    if ($actor_info["id"] == $album_user_info["id"]) {
        $album_user_username = "their own";
    }

    switch ($events_array[$i]["action_type"]) {

        case ACTION_ADD_ALBUM:
            $html .= <<<HTML
            <div class="nf-photo">
                <a href="{$link_albumphoto_one_up}">
                    <img class="nf-photo-image" src="{$img_albumphoto_cropped}">
                </a>
            </div>

            <div class="nf-message">
                <a href="{$link_actor}">{$actor_username}</a> created an album named
                 <a href="/{$album_user_info['username']}/{$album_info['handle']}">{$album_info['handle']}</a>
            </div>

HTML;
            break;

        case ACTION_ADD_ALBUMPHOTO:

            $html .= <<<HTML

            <div class="nf-photo">
                <a href="{$link_albumphoto_one_up}">
                    <img class="nf-photo-image" src="{$img_albumphoto_cropped}">
                </a>
            </div>

            <div class="nf-message">
                <a href="{$link_actor}">{$actor_username}</a> added a photo to
                {$album_user_username} <a href="/{$album_user_info['username']}/{$album_info['handle']}">{$album_info['handle']} album</a>
            </div>


HTML;
            break;

        case ACTION_ADD_COMMENT:
            $html .= <<<HTML

            <div class="nf-photo">
                <a href="{$link_albumphoto_one_up}">
                    <img class="nf-photo-image" src="{$img_albumphoto_cropped}">
                </a>
            </div>

            <div class="nf-message">
                <a href="{$link_actor}">{$actor_username}</a> commented on
                <a href="$link_actor">{$object_user_username}</a> photo:
                <a href="{$link_albumphoto_one_up}" style="color:#999999">{$truncated_comment}</a>
            </div>

HTML;
            break;

        case ACTION_LIKE_ALBUM:
            $html .= <<<HTML

HTML;
            break;

        case ACTION_LIKE_ALBUMPHOTO:
            $html .= <<<HTML

            <div class="nf-photo">
                <a href="{$link_albumphoto_one_up}">
                    <img class="nf-photo-image" src="{$img_albumphoto_cropped}">
                </a>
            </div>

            <div class="nf-message">
                <a href="{$link_actor}">{$actor_username}</a> liked
                <a href="$link_actor">{$object_user_username}</a> photo
            </div>


HTML;
            break;

        case ACTION_LIKE_COMMENT:
            $html .= <<<HTML

            <div class="nf-photo">
                <a href="{$link_albumphoto_one_up}">
                    <img class="nf-photo-image" src="{$img_albumphoto_cropped}">
                </a>
            </div>

            <div class="nf-message">
                <a href="{$link_actor}">{$actor_username}</a> liked
                <a href="$link_actor">{$object_user_username}</a> comment:
                <a href="{$link_albumphoto_one_up}" style="color:#999999">{$truncated_comment}</a>
            </div>


HTML;
            break;

        case ACTION_EDIT_CAPTION:
            $html .= <<<HTML

            <div class="nf-photo">
                <a href="{$link_albumphoto_one_up}">
                    <img class="nf-photo-image" src="{$img_albumphoto_cropped}">
                </a>
            </div>

            <div class="nf-message">
                <a href="{$link_actor}">{$actor_username}</a> added a caption to
                <a href="$link_actor">{$object_user_username}</a> photo:
                <a href="{$link_albumphoto_one_up}" style="color:#999999">{$truncated_caption}</a>
            </div>

HTML;
            break;

        case ACTION_EDIT_COMMENT:
            $html .= <<<HTML

HTML;
            break;

        case ACTION_DELETE_ALBUM:
            $html .= <<<HTML

HTML;
            break;

        case ACTION_DELETE_ALBUMPHOTO:
            $html .= <<<HTML

HTML;
            break;

        case ACTION_DELETE_COMMENT:
            $html .= <<<HTML

HTML;
            break;

        case ACTION_ROTATE_ALBUMPHOTO:
            $html .= <<<HTML

HTML;
            break;

        case ACTION_FILTER_ALBUMPHOTO:
            $html .= <<<HTML

            <div class="nf-photo">
                <a href="{$link_albumphoto_one_up}">
                    <img class="nf-photo-image" src="{$img_albumphoto_cropped}">
                </a>
            </div>

            <div class="nf-message">
                <a href="{$link_actor}">{$actor_username}</a> filtered a photo in {$album_user_username}
                {$album_info['handle']} <a href="/{$album_user_info['username']}/{$album_info['handle']}"> album</a>
            </div>
HTML;

            break;

        case ACTION_CHANGE_ALBUM_COVER:
            $html .= <<<HTML

HTML;
            break;


        default:
            $html .= <<<HTML

HTML;

    }


    $html .= <<<HTML
            <div style="clear:both; height:10px;"></div>
HTML;
}



$friends_info = get_friends($user_id);



$html .= <<<HTML
    </div>

    <div class="span4">
        <div style="margin-bottom:50px">
            <h3 style="margin-top:0px;">People in your feed</h3>
            <h5>The people listed below appear in your news feed.</h5>
HTML;

foreach ($friends_info as $friend) {

    $albums_info = get_albums_info_where_owner($friend["id"]);
    $num_albums = count($albums_info);
    $followed_albums = "";
    $num_followed_albums = 0;

    foreach ($albums_info as $album) {

        $is_follower = is_follower($user_id, $album["id"]);

        if ($is_follower) {
            $checked ='checked="checked"';
            $num_followed_albums++;
        } else {
            $checked = "";
        }

        $followed_albums .= <<<HTML
                        <div style="padding:1px;">
                            <label class="checkbox">
                                <input id="album-checkbox-{$album["id"]}"type="checkbox" onclick="toggleAlbumFollower({$album["id"]}, '{$album["user_id"]}');" {$checked}>{$album["handle"]} ({$album["id"]})
                                <span id="saved-{$album["id"]}" style="display:none; color:green;"><i class='icon-ok-sign'></i> Saved!</span>
                            </label>
                        </div>
HTML;

    }



    $html .= <<<HTML
            <div id="friend-{$friend["id"]}" style="padding:3px">

                <div style="float:left; width:20px; overflow:hidden; position:relative; top:2px;">
                    <a href="javascript:void(0);"
                       class="no-underline"
                       onclick="if (confirm('Sure you don\'t want to see updates from {$friend["username"]}?')) {
                                    deleteFriend({$friend["id"]}, {$user_id}, '{$user_info["token"]}');
                                }">
                        <i class="icon-remove"></i>
                    </a>
                </div>

                <div style="overflow:hidden;">
                    <a href="/{$friend["username"]}"><b>{$friend["username"]}</b></a>
                    <br>
                    <span style="color:#666666">
                        <a href="javascript:void(0);"
                           class="no-underline"
                           onclick="showNewsFeedAlbums('{$friend["id"]}');">
                           <i id ="albums-caret-{$friend["id"]}" class="icon-caret-right"></i>
                           Following <span id="num-followed-{$friend["id"]}">{$num_followed_albums}</a> of {$num_albums} albums
                        </a>
                        <br>
                    </span>


                    <div style="margin-left:10px; display:none;" id="albums-{$friend["id"]}">

                        <div style="color:#666666; margin:5px 0px; font-size:12px;">
                            You'll get updates (here and via email) on the albums checked below.
                        </div>

                        {$followed_albums}


                    </div>

                </div>




            </div>
HTML;
}




$html .= <<<HTML
        </div>
    </div>
</div>

HTML;


print($html);



?>








<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->
<?php require("static_scripts.php"); ?>
<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->







<script>

$(function() {



});

</script>




<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->
<?php require("static_bottom.php"); ?>
<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->

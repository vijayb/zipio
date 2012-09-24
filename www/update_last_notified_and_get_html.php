<?php

ini_set("display_errors", 1);
error_reporting(E_ALL | E_STRICT);

require("constants.php");
require("db.php");
require("helpers.php");


if (!isset($_GET["user_id"]) ||
    !isset($_GET["token"])) {
    print("0");
    exit();
} else {
    $user_id = $_GET["user_id"];
    $user_info = get_user_info($user_id);
    $token = $_GET["token"];
}

if (!check_token($user_id, $token, "Users")) {
    print("0");
    exit();
}

$events_array = get_events_array($user_id);

$html = "";

for ($i = 0; $i < count($events_array); $i++) {




    $actor_info = get_user_info($events_array[$i]["actor_id"]);

    if (isset($events_array[$i]["album_id"])) {
        $album_info = get_album_info($events_array[$i]["album_id"]);
        $album_owner_info = get_user_info($album_info["user_id"]);
        $object_owner_info = $album_owner_info;
        $object_owner_username = $album_owner_info["username"];
    }

    if (isset($events_array[$i]["albumphoto_id"])) {
        $albumphoto_info = get_albumphoto_info($events_array[$i]["albumphoto_id"]);
        $albumphoto_owner_info = get_user_info($albumphoto_info["photo_owner_id"]);
        $object_owner_info = $albumphoto_owner_info;
        $object_owner_username = $albumphoto_owner_info["username"];
        $truncated_caption = trim(substr($albumphoto_info["caption"], 0, 40)) . '...';
    }

    if (isset($events_array[$i]["comment_id"])) {
        $comment_info = get_comment_info($events_array[$i]["comment_id"]);
        $commenter_info = get_user_info($comment_info["commenter_id"]);
        $object_owner_info = $commenter_info;
        $object_owner_username = $commenter_info["username"];

        $truncated_comment = trim(substr($comment_info["comment"], 0, 40)) . '...';

    }

    $link_albumphoto_one_up = "/" . $albumphoto_owner_info['username'] . "/" . $album_info['handle'] . "/" . $albumphoto_info['id'];
    $link_actor = "/" . $actor_info["username"];
    $img_albumphoto_cropped = $g_s3_root . "/" . $albumphoto_info['s3_url'] . "_cropped";
    if ($albumphoto_info["filtered"] == 1) {
        $img_albumphoto_cropped .= "_filtered?" . time();
    }

    $actor_username = $actor_info["username"];





    if ($actor_info["id"] == $user_info["id"]) {
        $actor_username = "You";
    }

    if ($object_owner_info["id"] == $user_info["id"]) {
        $object_owner_username = "your";
    } else {
        $object_owner_username .= "'s";
    }

    if ($object_owner_info["id"] == $actor_info["id"]) {
        $object_owner_username = "their";
    }



    if ($album_owner_info["id"] == $user_info["id"]) {
        $album_owner_username = "your";
    } else {
        $album_owner_username = $album_owner_info["username"] . "'s";
    }

    if ($actor_info["id"] == $album_owner_info["id"]) {
        $album_owner_username = "their own";
    }







    $html .= <<<HTML
        <li style="padding:5px 15px">
HTML;

    switch ($events_array[$i]["action_type"]) {

        case ACTION_ADD_ALBUM:
            $html .= <<<HTML
            <div class="notification-photo">
                <a class="normal" href="{$link_albumphoto_one_up}">
                    <img class="notification-photo-image" src="{$img_albumphoto_cropped}">
                </a>
            </div>

            <div class="notification-message">
                <a class="normal" href="{$link_actor}">{$actor_username}</a> created an album named
                 <a class="normal" href="/{$album_owner_info['username']}/{$album_info['handle']}">{$album_info['handle']}</a>
            </div>

HTML;
            break;

        case ACTION_ADD_ALBUMPHOTO:

            $html .= <<<HTML

            <div class="notification-photo">
                <a class="normal" href="{$link_albumphoto_one_up}">
                    <img class="notification-photo-image" src="{$img_albumphoto_cropped}">
                </a>
            </div>

            <div class="notification-message">
                <a class="normal" href="{$link_actor}">{$actor_username}</a> added a photo to
                {$album_owner_username} <a class="normal" href="/{$album_owner_info['username']}/{$album_info['handle']}">{$album_info['handle']} album</a>
            </div>


HTML;
            break;

        case ACTION_ADD_COMMENT:
            $html .= <<<HTML

            <div class="notification-photo">
                <a class="normal" href="{$link_albumphoto_one_up}">
                    <img class="notification-photo-image" src="{$img_albumphoto_cropped}">
                </a>
            </div>

            <div class="notification-message">
                <a class="normal" href="{$link_actor}">{$actor_username}</a> commented on
                <a href="$link_actor" class="normal">{$object_owner_username}</a> photo:
                <a class="normal" href="{$link_albumphoto_one_up}" style="color:#999999">{$truncated_comment}</a>
            </div>

HTML;
            break;

        case ACTION_LIKE_ALBUM:
            $html .= <<<HTML

HTML;
            break;

        case ACTION_LIKE_ALBUMPHOTO:
            $html .= <<<HTML

            <div class="notification-photo">
                <a class="normal" href="{$link_albumphoto_one_up}">
                    <img class="notification-photo-image" src="{$img_albumphoto_cropped}">
                </a>
            </div>

            <div class="notification-message">
                <a class="normal" href="{$link_actor}">{$actor_username}</a> liked
                <a href="$link_actor" class="normal">{$object_owner_username}</a> photo
            </div>


HTML;
            break;

        case ACTION_LIKE_COMMENT:
            $html .= <<<HTML

            <div class="notification-photo">
                <a class="normal" href="{$link_albumphoto_one_up}">
                    <img class="notification-photo-image" src="{$img_albumphoto_cropped}">
                </a>
            </div>

            <div class="notification-message">
                <a class="normal" href="{$link_actor}">{$actor_username}</a> liked
                <a href="$link_actor" class="normal">{$object_owner_username}</a> comment:
                <a class="normal" href="{$link_albumphoto_one_up}" style="color:#999999">{$truncated_comment}</a>
            </div>


HTML;
            break;

        case ACTION_EDIT_CAPTION:
            $html .= <<<HTML

            <div class="notification-photo">
                <a class="normal" href="{$link_albumphoto_one_up}">
                    <img class="notification-photo-image" src="{$img_albumphoto_cropped}">
                </a>
            </div>

            <div class="notification-message">
                <a class="normal" href="{$link_actor}">{$actor_username}</a> added a caption to
                <a href="$link_actor" class="normal">{$object_owner_username}</a> photo:
                <a class="normal" href="{$link_albumphoto_one_up}" style="color:#999999">{$truncated_caption}</a>
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

            <div class="notification-photo">
                <a class="normal" href="{$link_albumphoto_one_up}">
                    <img class="notification-photo-image" src="{$img_albumphoto_cropped}">
                </a>
            </div>

            <div class="notification-message">
                <a class="normal" href="{$link_actor}">{$actor_username}</a> filtered a photo in {$album_owner_username}
                {$album_info['handle']} <a class="normal" href="/{$album_owner_info['username']}/{$album_info['handle']}"> album</a>
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


    $divider = "<li style='clear:both; margin:3px 1px' class='divider'></li>";

    $html .= <<<HTML
            <div style="clear:both; height:10px;"></div>
        </li>
        {$divider}
HTML;
}

$html = substr($html, 0, -1 * strlen($divider));

print($html);

update_last_notified($user_id);


?>
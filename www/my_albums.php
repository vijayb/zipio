<?php
// |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
require("static_supertop.php");
// |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||

if (!isset($_GET["owner_username"])) {
    exit();
} else {
    $owner_id = get_user_id_from_username($_GET["owner_username"]);
    $owner_username = get_username_from_user_id($owner_id);
    $owner_info = get_user_info($owner_id);

    if ($g_debug) {
        print("<!-- owner_id: $owner_id -->\n");
        print("<!-- owner_username: $owner_username -->\n");
        print("<!-- owner_info: " . print_r($owner_info, true) . "-->");
    }
}

// |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||| //
// |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||| //
// |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||| //

if ($owner_id == 0) {
    goto_homepage("#alert=5&username=" . $_GET["owner_username"]);
}

$page_title = "$owner_username";
$page_subtitle = "";


?>
<?php require("static_top.php"); ?>
<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->


<?php

$albums_array_where_owner = get_albums_info_where_owner($owner_id);
$albums_array_where_collaborator = get_albums_info_where_collaborator($owner_id);

$albums_to_display = array();
array_push($albums_to_display, $albums_array_where_owner);

if (is_logged_in() && $_SESSION["user_id"] == $owner_id) {
    array_push($albums_to_display, $albums_array_where_collaborator);
}



for ($k = 0; $k < count($albums_to_display); $k++) {

    $albums_array = $albums_to_display[$k];


    if ($k == 0) {
        $section_title = "My albums";
    } else if ($k == 1) {
        $section_title = "Others' albums";
    }


    $html = <<<HTML
    <div class="row">

        <div class="span12" style="margin-bottom:20px">
            <h2>$section_title</h2>
        </div>

HTML;


    for ($i = 0; $i < count($albums_array); $i++) {

        $is_owner = 0;
        $is_collaborator = 0;

        if (is_logged_in() && is_collaborator($_SESSION["user_id"], $albums_array[$i]["id"])) {
            $is_collaborator = 1;
        } else if (is_logged_in() && $albums_array[$i]["user_id"] == $_SESSION["user_id"]) {
            $is_owner = 1;
        }


        if ($albums_array[$i]["read_permissions"] == 1 && !($is_collaborator || $is_owner)) {
            continue;
        }

        $cover_albumphoto_info = get_albumphoto_info($albums_array[$i]["cover_albumphoto_id"], $albums_array[$i]["id"]);
        $album_owner_info = get_user_info($albums_array[$i]["user_id"]);
        $upper_left = "owner: <b>" . $album_owner_info["username"] . "</b>";


       if ($cover_albumphoto_info["filtered"] > 0) {
           $is_filtered = "_filtered";
       } else {
           $is_filtered = "";
       }

        $html .= <<<HTML
        <!-- ---------------------------------------------------------------- -->
        <!-- TILE BEGIN ----------------------------------------------------- -->

        <div class="tile span3" id="album-{$albums_array[$i]["id"]}">
            <a href="/{$album_owner_info["username"]}/{$albums_array[$i]["handle"]}">
                <img src='{$g_s3_root}/{$cover_albumphoto_info["s3_url"]}_cropped{$is_filtered}'>
                <div class="album-details"></div>
                <div class="album-title">{$albums_array[$i]["handle"]}</div>
                <div class="albumphoto-owner">{$upper_left}</div>
            </a>

HTML;

        if (!isset($_GET["following"]) && is_logged_in() && $_SESSION["user_id"] == $albums_array[$i]["user_id"]) {
            $html .= <<<HTML

            <div class="tile-options" style="display:none; padding:10px; position:absolute; bottom:0px; right:0px;">
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

        <!-- TILE END --------------------------------------------------------->
        <!--------------------------------------------------------------------->



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

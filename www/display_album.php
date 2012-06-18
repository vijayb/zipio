<?php
session_start();
ini_set("display_errors", 1);
error_reporting(E_ALL | E_STRICT);

require("db.php");
require("helpers.php");

check_request_for_login($_GET);
print("<!--" . print_r($_SESSION, true) . "-->");

if (!isset($_GET["username"]) || !isset($_GET["album_handle"])) {
    exit();
} else {
    $album_to_display = album_exists($_GET["album_handle"], $_GET["username"]);
    $album_info = get_album_info($album_to_display);
    $username = $_GET["username"];
    $username = get_username_from_userstring($username);
    print("<!-- album_id: $album_to_display -->\n");
    print("<!-- username: $username -->\n");
}

// |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||| //
// |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||| //
// |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||| //

$page_title = <<<HTML
    <a href="/{$username}">{$username}</a> &rsaquo; {$album_info["handle"]}
HTML;



// checks for follow-this-album
// 1. is logged_in_user already following the album?
// 2. not following the album?
// 3. is logged_in_user viewing his own album?
// 4. user not logged in

if (!is_logged_in()) { // user not logged in
    $page_title_right = <<<HTML
<button class="btn btn-large btn-primary" onclick="$('#follow-modal').modal('show')" id = "follow-this-album">Follow this album</button>
HTML;
        
    } else { // user logged in
        $user_id = is_logged_in();
        $logged_in_username = get_username_from_user_id($user_id);
        
        if ($logged_in_username == $username) { // logged in user same as album owner?
            $page_title_right = <<<HTML
HTML;
        } else { // logged in user viewing someone else's album
            if (isset($album_info) && is_following($user_id, $album_info["id"]) == 1) { // logged in user already following this album?
               $page_title_right = <<<HTML
                    <button onclick="unfollowAlbum();" class="btn enabled" id="unfollow-submit" data-loading-text="Please wait...">Unfollow</button>
HTML;
            } else { // logged in user not following this album
                 $page_title_right = <<<HTML
                    <button class="btn btn-large btn-primary" onclick="followAlbum()" id = "follow-this-album">Follow this album</button>
HTML;
            }
        }
    }



?>



<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->
<?php require("static_top.php"); ?>
<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->




<div class="row">
    <div class="span12">
        <div class="accordion" id="accordion2">
            <div class="accordion-group">

                <div class="accordion-heading">
                    <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#collapseThree">
                        Album Settings
                    </a>
                </div>

                <div id="collapseThree" class="accordion-body collapse" style="height: 0px; ">
                    <div class="accordion-inner">
                        Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS.
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>



<div class="row" id="masonry-container">

<?php

$photos_array = get_photos_info($album_to_display);

$photos_array_js = "";

for ($i = 0; $i < count($photos_array); $i++) {
    if ($photos_array[$i]["visible"] == 0) {
        $opacity = "0.4";
    } else {
        $opacity = "1.0";
    }

    $photos_array_js .= "'http://s3.amazonaws.com/zipio_photos/" . $photos_array[$i]["s3_url"] . "_800',";

    $html = <<<HTML
        <div class="item span3" id="photo-{$photos_array[$i]["id"]}">
            <a class="fancybox" data-fancybox-type="image" rel="fancybox" href="http://s3.amazonaws.com/zipio_photos/{$photos_array[$i]["s3_url"]}_800">
                <img style='opacity:{$opacity};' src='http://s3.amazonaws.com/zipio_photos/{$photos_array[$i]["s3_url"]}_cropped'>
            </a>

            <div class="tile-options" style="display:none;">
                <div class="btn-group">
                    <button class="btn btn-inverse dropdown-toggle" data-toggle="dropdown">
                        <i class="icon-chevron-down icon-white"></i>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a href="javascript:void(0);" onclick="deletePhotoFromAlbum({$photos_array[$i]["id"]}, {$album_to_display}, {$album_info["cover_photo_id"]});"><i class="icon-trash"></i> Delete this photo</a></li>
                    </ul>
                </div>
            </div>

        </div>
HTML;

    print($html);
}

$photos_array_js = rtrim($photos_array_js, ",");

?>

</div>

<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->
<?php require("static_scripts.php"); ?>
<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->




<script>



var span3Width = 0;

$(function() {

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

    $(".item").each(function(index) {
        $(this).mouseenter(function() {
            $(this).find(".tile-options").stop(true, true).show();
        });
        $(this).mouseleave(function() {
            $(this).find(".tile-options").stop(true, true).fadeOut();
        });
    });

    // If the user is logged in but has not yet registered (i.e., set a
    // password), then show the registration dialog
    if (isLoggedIn() && gUser["password_hash"] == "" && gUser["username"] != "") {
        $('#register-modal').modal('show');
    }


<?php

$output_js = <<<HTML
    preload([{$photos_array_js}]);
HTML;

print($output_js);

?>

});

</script>




<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->
<?php require("static_bottom.php"); ?>
<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->

<!DOCTYPE html>
<html xmlns:fb="http://ogp.me/ns/fb#">
<head>
<meta charset="utf-8">
<title><?php print($g_Zipio); ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="">
<meta name="author" content="">



<link href="/lib/styles.css" rel="stylesheet" />
<link href="/lib/fonts.css" rel="stylesheet" />












<link href="/lib/bootstrap.css" rel="stylesheet" />
<link href="/lib/bootstrap-responsive.css" rel="stylesheet" />

<!--
<link rel="stylesheet/less" href="/bootstrap/less/bootstrap.less" media="all" />
<link rel="stylesheet/less" href="/bootstrap/less/responsive.less" media="all" />
<script src="/lib/less-1.3.0.min.js"></script>
-->



<?php

// =============================================================================

// Some pages on the site are fluid, like one_up.php. I
$is_fluid = "";

if (strstr($_SERVER["SCRIPT_FILENAME"], "display_album.php")) {

    if (isset($_GET["albumphoto"])) {
        $albumphoto_id = $_GET["albumphoto"];
        $albumphoto_info = get_albumphoto_info($albumphoto_id);
        print("<!-- GET: " . print_r($albumphoto_info, true) . "-->");
        $html = <<<HTML
            <meta property="og:image" content="{$g_s3_root}/{$albumphoto_info["s3_url"]}_big" />
HTML;
        print($html);
    }

} else if (strstr($_SERVER["SCRIPT_FILENAME"], "index.php")) {

    $html = <<<HTML
        <style>

        body {
        	background-color: #004183;
        }
        </style>

HTML;

    print($html);

} else if (strstr($_SERVER["SCRIPT_FILENAME"], "one_up.php")) {
    $is_fluid = "-fluid";
}

?>









<!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
<!--[if lt IE 9]>
<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->

<!-- Le fav and touch icons -->
<link rel="shortcut icon" href="../assets/ico/favicon.ico">
<link rel="apple-touch-icon-precomposed" sizes="144x144" href="../assets/ico/apple-touch-icon-144-precomposed.png">
<link rel="apple-touch-icon-precomposed" sizes="114x114" href="../assets/ico/apple-touch-icon-114-precomposed.png">
<link rel="apple-touch-icon-precomposed" sizes="72x72" href="../assets/ico/apple-touch-icon-72-precomposed.png">
<link rel="apple-touch-icon-precomposed" href="../assets/ico/apple-touch-icon-57-precomposed.png">








</head>


<?php

// Define default values for templates

$brand_name = "<img src='/images/" . $g_zipio . "_white_small.png'>";

if (is_logged_in()) {

    $logged_in_status = <<<HTML
            <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown"> {$_SESSION["user_info"]["email"]} <b class="caret"></b></a>
                <ul class="dropdown-menu">
                    <li><a href="/logout.php"><i class="icon-off"></i> Logout</a></li>
                </ul>
            </li>
HTML;

} else {

    $logged_in_status = <<<HTML
            <li><a href="javascript:void(0);" onclick="showLoginModal();">Log in</a></li>
HTML;

}

// It is the responsibility of the page that uses the template (and therefore
// includes static_top.php) to initialize these variables. If that page has
// not initialized the variables by now, we set defaults below.

if (!isset($page_title)) $page_title = $g_Zipio;
if (!isset($page_subtitle)) $page_subtitle = "Post photos over email";
if (!isset($page_title_right)) $page_title_right = "";

?>






<body>

<div id="fb-root"></div>
<script>

gFB = new Array();
gFB["status"] = -1;
gFB["userID"] = -1;
gFB["accessToken"] = -1;

window.fbAsyncInit = function() {
    FB.init({
        appId      : '<?php print($g_fb_app_id); ?>', // App ID
        channelUrl : '//<?php print($g_zipio); ?>.com/channel.php', // Channel File
        status     : true, // check login status
        cookie     : true, // enable cookies to allow the server to access the session
        xfbml      : true  // parse XFBML
    });

    FB.getLoginStatus(function(response) {
        if (response.status === 'connected') {
            gFB["userID"] = response.authResponse.userID;
            gFB["accessToken"] = response.authResponse.accessToken;
            gFB["status"] = 1;
            debug("1");
        } else if (response.status === 'not_authorized') {
            gFB["status"] = 2;
            debug("2");
        } else {
            gFB["status"] = 3;
            debug("3");
        }
    });

    FB.Event.subscribe('auth.authResponseChange', function(response) {
        if (response.status === 'connected') {
            gFB["userID"] = response.authResponse.userID;
            gFB["accessToken"] = response.authResponse.accessToken;
            gFB["status"] = 1;
            debug("1");
        } else if (response.status === 'not_authorized') {
            gFB["status"] = 2;
            debug("2");
        } else {
            gFB["status"] = 3;
            debug("3");
        }
    });
};

// Load the SDK Asynchronously
(function(d){
    var js, id = 'facebook-jssdk', ref = d.getElementsByTagName('script')[0];
    if (d.getElementById(id)) {return;}
    js = d.createElement('script'); js.id = id; js.async = true;
    js.src = "//connect.facebook.net/en_US/all.js";
    ref.parentNode.insertBefore(js, ref);
}(document));

</script>



<!-- ----------------------------------------------------------------------- -->
<!-- ----------------------------------------------------------------------- -->
<!-- ----------------------------------------------------------------------- -->
<!-- ----------------------------------------------------------------------- -->
<!-- ----------------------------------------------------------------------- -->
<!-- ----------------------------------------------------------------------- -->



<?php if (is_logged_in()) { ?>

<div class="modal hide" id="register-modal">

    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">×</button>
        <h2>Welcome, <?php print($_SESSION["user_info"]["email"]); ?>!</h2>
        <h3>Set a password and change your username (if you want)</h3>
    </div>

    <div class="modal-body">
        <div class="form-horizontal">
            <div class="control-group">
                <label class="control-label" for="input01">Your username</label>
                <div class="controls">
                    <div id="register-username-panel" style="display:none">
                        <input type="text" class="input-xlarge" id="register-username" autocomplete="off">
                        <p class="help-block" id="register-username-check"></p>
                        <p class="help-block"><a href="javascript:void(0);" onclick="flipChangeUsername();">Cancel (I'll stick with <b><?php print($_SESSION["user_info"]["username"]); ?></b>)</a></p>
                    </div>

                    <div id="register-read-only-username-panel">
                        <span class="input-xlarge uneditable-input"><?php print($_SESSION["user_info"]["username"]); ?></span>
                        <p class="help-block"><a href="javascript:void(0);" onclick="flipChangeUsername();">I want to change my username</a></p>
                    </div>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="input01">Pick a password</label>
                <div class="controls">
                    <input type="password" class="input-xlarge" id="register-password">
                </div>
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <a href="#" class="btn" data-dismiss="modal">Not now</a>
        <button onclick="submitUsernamePassword();"
                disabled
                class="btn btn-primary" id="register-submit" data-loading-text="Please wait...">
                Set my password
        </button>
    </div>

</div>


<!-- ----------------------------------------------------------------------- -->


<div class="modal hide" id="caption-modal">

    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">×</button>
        <h2>Edit caption</h2>
    </div>

    <div class="modal-body">
        <div class="form-horizontal">
            <input type="text" class="input-xlarge" id="caption-input" style="width:99%">
        </div>
    </div>

    <div class="modal-footer">
        <a href="#" class="btn" data-dismiss="modal">Cancel</a>
        <button onclick="submitCaption();"
                class="btn btn-primary" id="caption-submit" data-loading-text="Please wait...">
                Save caption
        </button>
    </div>

</div>



<!-- ----------------------------------------------------------------------- -->




<div class="modal hide" id="comment-modal">

    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">×</button>
        <h2>Comments</h2>
    </div>

    <div class="modal-body" id="comment-modal-body">
        <div id="comments">
        </div>
    </div>

    <div class="modal-footer">

        <div class="form-horizontal" style="margin-bottom:20px">
            <input type="text" class="input-xlarge" id="comment-input" style="width:100%; box-sizing:border-box; height:30px;" placeholder="Add a comment...">
        </div>

        <a href="#" class="btn" data-dismiss="modal">Cancel</a>
        <button onclick="submitComment();"
                class="btn btn-primary" id="comment-submit" data-loading-text="Adding comment...">
                Add comment
        </button>
    </div>
</div>




<div class="modal hide" id="likes-modal">

    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">×</button>
        <h2>Likes</h2>
    </div>

    <div class="modal-body" id="likes-modal-body">
        <div id="likes">

        </div>
    </div>
</div>


<?php } ?>

<!-- ----------------------------------------------------------------------- -->

<div class="modal hide" id="map-modal">

    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">×</button>
        <h2>This is where the photo was taken</h2>
    </div>

    <div class="modal-body" id="map-modal-body">
        <img id="map-canvas" style="width:100%">
    </div>
</div>

<!-- ----------------------------------------------------------------------- -->

<div class="modal hide" id="login-modal">

    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">×</button>
        <h2>Login</h2>
    </div>




    <div class="modal-body">
        <div class="alert fade in alert-error" id="login-error" style="display:none;">
            <span id="login-error-message"></span>
        </div>

        <div class="form-horizontal">
            <div class="control-group">
                <label class="control-label" for="input01">Email</label>
                <div class="controls">
                    <input type="text" class="input-xlarge" id="login-email">
                    <p class="help-block" id="login-email-check"></p>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="input01">Password</label>
                <div class="controls">
                    <input type="password" class="input-xlarge" id="login-password">
                    <p class="help-block"><a href="javascript:void(0);" onclick="showForgotPasswordModal();">Forgot or haven't yet set your password?</a></p>
                </div>
            </div>
        </div>
    </div>




    <div class="modal-footer">
        <a href="#" class="btn" data-dismiss="modal">Cancel</a>
        <button onclick="submitLogin();"
                disabled
                class="btn btn-primary" id="login-submit" data-loading-text="Please wait...">
                Login
        </button>
    </div>



</div>

<!-- ----------------------------------------------------------------------- -->

<div class="modal hide" id="password-modal">

    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">×</button>
        <h2>Need a password?</h2>
        <h3>You know the drill</h3>
    </div>

    <div class="modal-body">
        <div class="form-horizontal">
            <div class="control-group">
                <label class="control-label" for="input01">Email</label>
                <div class="controls">
                    <input type="text" class="input-xlarge" id="password-email">
                    <p class="help-block" id="password-email-check"></p>
                </div>
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <a href="#" class="btn" data-dismiss="modal">Cancel</a>
        <button onclick="submitForgotPassword();"
                disabled
                class="btn btn-primary" id="password-submit" data-loading-text="Please wait...">
                Send me a password reset link
        </button>
    </div>

</div>

<!-- ----------------------------------------------------------------------- -->

<div class="modal hide" id="signup-modal">

    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">×</button>
        <h2>Signup for a <?php print($g_Zipio); ?> account</h2>
    </div>

    <div class="modal-body">
        <div class = "hide" id="try-again">
            <div class = "alert alert-error"> Something went wrong. Please try again.</div>
        </div>
        <div class="form-horizontal">
            <div class="control-group">
                <label class="control-label" for="input01">Pick a username</label>
                <div class="controls">
                    <input type="text" class="input-xlarge" id="signup-username" autocomplete="off">
                    <p class="help-block" id="signup-username-check"></p>
                    <!-- <p class="help-block">Supporting help text</p> -->
                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="input01">Your email</label>
                <div class="controls">
                    <input type="text" class="input-xlarge" id="signup-email">
                    <p class="help-block" id="signup-email-check"></p>
                    <!-- <p class="help-block">Supporting help text</p> -->
                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="input01">Create a password</label>
                <div class="controls">
                    <input type="password" class="input-xlarge" id="signup-password">
                </div>
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <a href="#" class="btn" data-dismiss="modal">Cancel</a>
        <button onclick="submitSignup();"
                disabled
                class="btn btn-primary" id="signup-submit" data-loading-text="Please wait...">
                Sign up
        </button>
    </div>

</div>


<!-- ----------------------------------------------------------------------- -->

<div class="modal hide" id="invite-modal">

    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">×</button>
        <h2>Invite people to see and add photos</h2>
        <h3>Photos can be added by email &mdash; <span style="color:red">they need not sign up!</span></h3>
    </div>

    <div class="modal-body">

        <p>
            People you invite can add photos by email. Plus, we'll update them when photos are added to this album.
        </p>

        <p>
            <b>Enter email addresses below</b> (either comma-separated or any way you like; we'll figure it out).
        </p>

        <p>
            <textarea id="invite-emails" class="width-fix" style="width:100%; max-width:100%;"></textarea>
        </p>
    </div>

    <div class="modal-footer">
        <a href="#" class="btn" data-dismiss="modal">Cancel</a>
        <button onclick="submitInvite();"
                class="btn btn-primary" id="invite-submit" data-loading-text="Please wait...">
                Invite
        </button>
    </div>

</div>

<!-- ----------------------------------------------------------------------- -->

<div class="modal hide" id="facebook-modal">

    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">×</button>
        <h2>Post this photo to Facebook</h2>
        <h3></h3>
    </div>

    <div class="modal-body">

        <p>
            Say something about the photo (if you want):
        </p>

        <p>
            <textarea id="facebook-comment" class="width-fix" style="width:100%; max-width:100%;"></textarea>
        </p>

        <img id="facebook-image" style="height:100px;">
    </div>

    <div class="modal-footer">
        <a href="#" class="btn" data-dismiss="modal">Cancel</a>
        <button onclick="postToFacebook();"
                class="btn btn-primary" id="facebook-submit" data-loading-text="Posting to Facebook...">
                Post
        </button>
    </div>

</div>







<!-- ----------------------------------------------------------------------- -->
<!-- ----------------------------------------------------------------------- -->
<!-- ----------------------------------------------------------------------- -->
<!-- ----------------------------------------------------------------------- -->
<!-- ----------------------------------------------------------------------- -->
<!-- ----------------------------------------------------------------------- -->
<!-- ----------------------------------------------------------------------- -->
<!-- ----------------------------------------------------------------------- -->
<!-- ----------------------------------------------------------------------- -->
<!-- ----------------------------------------------------------------------- -->



<?php
    require_once("helpers.php");

    $alert_count = 0;
    $show_alerts_style = "style='display: none;'";
    if (is_logged_in()) {
        $alerts_array = get_events_array($_SESSION["user_info"]["id"]);
        if (count($alerts_array) > 0) {
            $alert_count = count($alerts_array);
            $show_alerts_style = "";
        }
    }


$user_zipio_dir = "/";
if (isset($_SESSION["user_info"]["username"])) {
    $user_zipio_dir = "/" . $_SESSION["user_info"]["username"];
}

$navbar_html = <<<HTML

<div class="navbar navbar-fixed-top navbar-inverse">
    <div class="navbar-inner" style="background-color:initial">
        <div class="container{$is_fluid}">

            <a class="brand" href="{$user_zipio_dir}">
                {$brand_name}
            </a>


            <ul class="nav">
                <li><a href="/news_feed.php?owner_username={$_SESSION["user_info"]["username"]}"><i class="icon-reorder"></i> News Feed</a></li>
                <li><a href="/{$_SESSION["user_info"]["username"]}"><i class="icon-th"></i> My Albums</a></li>
            </ul>


            <ul class="nav pull-right">
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" onclick="showNotificationPanel();">
                        <i class="icon-envelope-alt"></i>
                        <span id="notification-counter" class="badge badge-important" {$show_alerts_style}>{$alert_count}</span>
                    </a>
                    <ul id="notification-items" class="dropdown-menu" style="width:300px;">
                    </ul>
                </li>
                {$logged_in_status}

            </ul>


        </div>
    </div>

    <div id="fb-bar" style="display:none;">
        <span class="highlight">People you know are already using {$g_Zipio}!</span>
        <button class="btn btn-primary" href="#" onclick="FB.login();">Find them with Facebook</button>
    </div>
</div>

HTML;

print($navbar_html);

?>



<div class="container<?php print($is_fluid); ?>">

    <div class="row">
        <div class="span12" style="margin-bottom:10px;">
            <div class="alert" style="display:none; padding:10px 35px 10px 15px;" id="header-alert">
                <button class="close">×</button>
                <h2 class="alert-heading" id="header-alert-title"></h2>
                <span style="margin-top:5px;" id="header-alert-text"></span>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="span9" style="margin-bottom:20px">
            <h1><?php print($page_title); ?></h1>
            <h3 style="color:#888888; margin: 5px 0px 5px 0px;">
                <?php print($page_subtitle); ?>
            </h3>
        </div>
        <div class="span3" style="text-align:right"><?php print($page_title_right); ?></div>
    </div>

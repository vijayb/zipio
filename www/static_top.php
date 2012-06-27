<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Zipio</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="">
<meta name="author" content="">

<link href="/fancybox/helpers/jquery.fancybox-thumbs.css?v=2.0.6" rel="stylesheet" />
<link href="/fancybox/jquery.fancybox.css?v=2.0.6" rel="stylesheet" media="screen" />
<link rel="stylesheet/less" href="/bootstrap/less/bootstrap.less" media="all" />
<link rel="stylesheet/less" href="/bootstrap/less/responsive.less" media="all" />
<link href="/lib/styles.css" rel="stylesheet" />
<link href="/lib/fonts.css" rel="stylesheet" />
<script src="/lib/less-1.3.0.min.js"></script>



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

if (is_logged_in()) {

    $brand_name = "<span style='color:red;'>zipio</span>";

    $logged_in_status = <<<HTML
        <ul class="nav pull-right">
            <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" style=''>
                    <b>{$_SESSION["user_info"]["email"]}</b><b class="caret"></b>
                </a>
                <ul class="dropdown-menu">
                    <li><a href="/{$_SESSION["user_info"]["username"]}"><i class="icon-th"></i> My Albums</a></li>
                    <li><a href="/{$_SESSION["user_info"]["username"]}/_following"><i class="icon-eye-open"></i> Albums I'm Following</a></li>
                    <li><a href="/{$_SESSION["user_info"]["username"]}/_friends"><i class="icon-group"></i> My Friends</a></li>
                    <li><a href="#"><i class="icon-wrench"></i> Account Settings</a></li>
                    <li class="divider"></li>
                    <li><a href="/logout.php"><i class="icon-off"></i> Logout</a></li>
                </ul>
            </li>
        </ul>
HTML;


} else {

    $brand_name = "zipio";

    $logged_in_status = <<<HTML
        <ul class="nav pull-right">
            <li><a href="javascript:void(0);" onclick="showLoginModal();">Login</a></li>
            <li><a href="javascript:void(0);" onclick="$('#signup-modal').modal('show'); $('#signup-username').focus();">Sign up</a></li>
        </ul>
HTML;

}

// It is the responsibility of the page that uses the template (and therefore
// includes static_top.php) to initialize these variables. If that page has
// not initialized the variables by now, we set defaults below.

if (!isset($page_title)) $page_title = "Zipio";
if (!isset($page_subtitle)) $page_subtitle = "Post photos over email";
if (!isset($page_title_right)) $page_title_right = "";

?>






<body>

<div class="navbar navbar-fixed-top">
    <div class="navbar-inner">
        <div class="container">

            <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </a>

            <a class="brand" href="#">
                <?php print($brand_name); ?>
            </a>

            <?php print($logged_in_status); ?>

        </div>
    </div>
</div>

<!----------------------------------------------------------------------------->

<div class="modal hide" id="register-modal">

    <div class="modal-header">
        <a class="close" data-dismiss="modal">×</a>
        <h2>Hi, <?php print($_SESSION["user_info"]["email"]); ?>!</h2>
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
        <a href="#" class="btn" data-dismiss="modal">I'll set up a password later</a>
        <button onclick="submitUsernamePassword();"
                disabled
                class="btn btn-primary" id="register-submit" data-loading-text="Please wait...">
                Set my password
        </button>
    </div>

</div>

<!----------------------------------------------------------------------------->

<div class="modal hide" id="login-modal">

    <div class="modal-header">
        <a class="close" data-dismiss="modal">×</a>
        <h2>Login</h2>
    </div>

    <div class="modal-body">
        <div class="alert fade in alert-error" id="login-error" style="display:none;">
            <button type="button" class="close">×</button>
            <strong>Woops.</strong> That email/password combo is invalid.
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
                    <p class="help-block"><a href="javascript:void(0);" onclick="showForgotPassword();">Forgot or haven't yet set your password?</a></p>
                </div>
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <a href="#" class="btn" data-dismiss="modal">Cancel</a>
        <button onclick="attemptLogin();"
                disabled
                class="btn btn-primary" id="login-submit" data-loading-text="Please wait...">
                Login
        </button>
    </div>

</div>

<!----------------------------------------------------------------------------->

<div class="modal hide" id="password-modal">

    <div class="modal-header">
        <a class="close" data-dismiss="modal">×</a>
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

<!----------------------------------------------------------------------------->

<div class="modal hide" id="signup-modal">

    <div class="modal-header">
        <a class="close" data-dismiss="modal">×</a>
        <h2>Signup for a Zipio account</h2>
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
        <button onclick="signupUser();"
                disabled
                class="btn btn-primary" id="signup-submit" data-loading-text="Please wait...">
                Sign up
        </button>
    </div>

</div>

<!----------------------------------------------------------------------------->

<div class="modal hide" id="follow-modal">

    <div class="modal-header">
        <a class="close" data-dismiss="modal">×</a>
        <h2>Follow this album&#8212;no signup needed!</h2>
        <h3>Enter your email and we'll let you know when photos are added</h3>
    </div>

    <div class="modal-body">
        <div class="form-horizontal">
            <div class="control-group">
                <label class="control-label" for="input01">Your email</label>
                <div class="controls">
                    <input type="text" class="input-xlarge" id="follow-email">
                    <p class="help-block" id="follow-email-check"></p>
                </div>
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <a href="#" class="btn" data-dismiss="modal">Cancel</a>
        <button onclick="submitEmailToFollow(<?php if (isset($album_info)) { print($album_info['id']); } ?>);"
                disabled
                class="btn btn-primary" id="follow-submit" data-loading-text="Please wait...">
                Follow
        </button>
    </div>

</div>

<!----------------------------------------------------------------------------->

<div class="modal hide" id="album-settings-modal">

    <div class="modal-header">
        <a class="close" data-dismiss="modal">×</a>
        <h2>Who can see and add photos to this album?</h2>
    </div>

    <div class="modal-body">

        <label class="radio">
            <input type="radio" name="album-settings-radios" id="album-setting-1" value="1" checked="">
            <b>Private album</b>
        </label>
        <p style="margin-left:20px">
            Only you can see and add photos to this album.
        </p>

        <br>

        <label class="radio">
            <input type="radio" name="album-settings-radios" id="album-setting-2" value="2" checked="">
            <b>Friends album</b>
        </label>
        <p style="margin-left:20px">
            Only you and your friends can see and add photos to this album.
            <br>
            <a href="/<?php print($_SESSION["user_info"]["username"]); ?>/_friends">See a list of my friends</a>
        </p>

        <br>

        <label class="radio">
            <input type="radio" name="album-settings-radios" id="album-setting-3" value="3" checked="">
            <b>Public album</b>
        </label>
        <p style="margin-left:20px">
            Anyone can <i>see</i> this album, but only you and your friends can add photos. If anyone else tries to add a photo, we'll ask you first for your approval.
        </p>


    </div>

    <div class="modal-footer">
        <a href="#" class="btn" data-dismiss="modal">Cancel</a>
        <button onclick="submitAlbumSettings();"
                class="btn btn-primary" id="follow-submit" data-loading-text="Please wait...">
                Save album settings
        </button>
    </div>

</div>

<!----------------------------------------------------------------------------->
<!----------------------------------------------------------------------------->
<!----------------------------------------------------------------------------->

<div class="container">

    <div class="row">
        <div class="span12">
            <div class="alert" style="display:none;" id="header-alert">
                <button class="close">×</button>
                <h3 class="alert-heading" id="header-alert-title" style="font-weight:bold;"></h3>
                <span id="header-alert-text"></span>
            </div>
        </div>
    </div>

    <div class="row" style="margin-bottom:20px;">
        <div class="span9">
            <h1><?php print($page_title); ?></h1>
            <h3 style="color:#999999;"><?php print($page_subtitle); ?></h3>
        </div>
        <div class="span3" style="text-align:right"><?php print($page_title_right); ?></div>
    </div>

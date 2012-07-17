<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title><?php print($g_Zipio); ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="">
<meta name="author" content="">

<link href="/fancybox/helpers/jquery.fancybox-thumbs.css?v=2.0.6" rel="stylesheet" />
<link href="/fancybox/jquery.fancybox.css?v=2.0.6" rel="stylesheet" media="screen" />

<link href="/lib/styles.css" rel="stylesheet" />
<link href="/lib/fonts.css" rel="stylesheet" />










<link href="/lib/bootstrap.css" rel="stylesheet" />
<link href="/lib/bootstrap-responsive.css" rel="stylesheet" />


<!--
rm www/lib/bootstrap.css; lessc www/bootstrap/less/bootstrap.less > www/lib/bootstrap.css
rm www/lib/bootstrap-responsive.css; lessc www/bootstrap/less/responsive.less > www/lib/bootstrap-responsive.css
-->

<!--
<link rel="stylesheet/less" href="/bootstrap/less/bootstrap.less" media="all" />
<link rel="stylesheet/less" href="/bootstrap/less/responsive.less" media="all" />
<script src="/lib/less-1.3.0.min.js"></script>
-->








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

    $brand_name = "<span style='color:red;'>$g_zipio</span> beta";

    $logged_in_status = <<<HTML
        <ul id="right-links" class="nav" style="font-size:18px">
            <li class="divider-vertical"></li>
            <li id="right-links-1"><a href="/{$_SESSION["user_info"]["username"]}"><i class="icon-th"></i> Albums</a></li>
        </ul>


        <ul id="right-links" class="nav pull-right" style="font-size:18px">
            <li><a href="/{$_SESSION["user_info"]["username"]}"><b>{$_SESSION["user_info"]["email"]}</b></a></li>
            <li id="right-links-3"><a href="/logout.php"><i class="icon-off"></i> Logout</a></li>
        </ul>
HTML;


} else {

    $brand_name = "$g_zipio beta";

    $logged_in_status = <<<HTML
        <ul class="nav pull-right" style="font-size:18px">
            <li><a href="javascript:void(0);" onclick="showLoginModal();">Login</a></li>
        </ul>
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
  window.fbAsyncInit = function() {
    FB.init({
      appId      : '255929901188660', // App ID
      channelUrl : '//zipiyo.com/channel.php', // Channel File
      status     : true, // check login status
      cookie     : true, // enable cookies to allow the server to access the session
      xfbml      : true  // parse XFBML
    });

    // Additional initialization code here
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


<!----------------------------------------------------------------------------->

<?php if (is_logged_in()) { ?>

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
        <a href="#" class="btn" data-dismiss="modal">Not now</a>
        <button onclick="submitUsernamePassword();"
                disabled
                class="btn btn-primary" id="register-submit" data-loading-text="Please wait...">
                Set my password
        </button>
    </div>

</div>

<?php } ?>

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


<!----------------------------------------------------------------------------->

<div class="modal hide" id="invite-modal">

    <div class="modal-header">
        <a class="close" data-dismiss="modal">×</a>
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

<!----------------------------------------------------------------------------->


<!----------------------------------------------------------------------------->
<!----------------------------------------------------------------------------->
<!----------------------------------------------------------------------------->



<div class="navbar navbar-fixed-top">
    <div class="navbar-inner">
        <div class="container">

            <a class="brand" href="/<? if (is_logged_in()) { print($_SESSION["user_info"]["username"]); } ?>">
                <?php print($brand_name); ?>
            </a>

            <?php print($logged_in_status); ?>

        </div>
    </div>

    <div id="fb-bar" style="display:none;">
        <span class="highlight">People you know are already using <?php print($g_Zipio); ?>!</span>
        <button class="btn btn-primary" href="#" onclick="FB.login();">Find them with Facebook</button>
    </div>
</div>




<div class="navbar navbar-fixed-bottom" style="z-index:-1000">



<?php

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

if ($g_debug) {

    $html = <<<HTML
    <div style="background-color:black; color:#ffffff; font-weight:700; text-align:center; font-size:24px; padding:10px; opacity:0.3;">
        {$_SERVER["HTTP_HOST"]}
    </div>
HTML;
    print($html);

    $html = <<<HTML
    <div style="background-color:black; color:#ffffff; font-weight:700; text-align:center; font-size:24px; padding:10px; opacity:0.3;">
        DB: {$g_database_to_use} - zipio: {$g_zipio}/{$g_Zipio}
    </div>
HTML;
    print($html);

}



////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

?>

</div>


<div class="container">

    <div class="row">
        <div class="span12">
            <div class="alert" style="display:none;" id="header-alert">
                <button class="close">×</button>
                <h3 class="alert-heading" id="header-alert-title" style="font-weight:bold;"></h3>
                <span style="font-size:16px" id="header-alert-text"></span>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="span9" style="margin-bottom:10px">
            <h1><?php print($page_title); ?></h1>
            <h2 style="color:#666666; margin: 10px 0px 5px 0px; font-weight:normal;"><?php print($page_subtitle); ?></h2>
            <?php // if (isset($third_row)) { print($third_row); } ?>
        </div>
        <div class="span3" style="text-align:right"><?php print($page_title_right); ?></div>
    </div>

    <div class="row" style="height:25px"></div>

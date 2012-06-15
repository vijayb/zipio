<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Bootstrap, from Twitter</title>
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
    $logged_in_status = <<<HTML
        <ul class="nav pull-right">
            <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">You're logged in as <b>{$_SESSION["user_info"]["username"]}</b> <b class="caret"></b></a>
                <ul class="dropdown-menu">
                    <li><a href="#"><i class="icon-wrench"></i> Account settngs</a></li>
                    <li class="divider"></li>
                    <li><a href="/logout.php"><i class="icon-off"></i> Logout</a></li>
                </ul>
            </li>
        </ul>
HTML;
} else {
    $logged_in_status = <<<HTML
        <ul class="nav pull-right">
            <li><a href="javascript:void(0);" onclick="$('#login-modal').modal('show')">Login</a></li>
        </ul>
HTML;

}

if (!isset($page_title)) $page_title = "Zipio";
if (!isset($page_title_right)) $page_title_right = "";
if (!isset($logged_in_status)) $logged_in_status = "";

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

            <a class="brand" href="#" style="font-weight:bold">zipio</a>

            <?php print($logged_in_status); ?>

        </div>
    </div>
</div>


<div class="modal hide" id="register-modal">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">×</button>
        <h2>Hi, <?php print($_SESSION["user_info"]["email"]); ?>!</h2>
        <h3>Set a password and change your username (if you want)</h3>
    </div>
    <div class="modal-body">
        <form class="form-horizontal">
            <fieldset>
                <div class="control-group">
                    <label class="control-label" for="input01">Your username</label>
                    <div class="controls">
                        <div id="change-username" style="display:none">
                            <input type="text" class="input-xlarge" id="username" name="username" autocomplete="off">
                            <p class="help-block" id="username-check"></p>
                            <p class="help-block"><a href="javascript:void(0);" onclick="flipChangeUsername();">Cancel (I'll stick with <b><?php print($_SESSION["user_info"]["username"]); ?></b>)</a></p>
                        </div>

                        <div id="read-only-username">
                            <span class="input-xlarge uneditable-input"><?php print($_SESSION["user_info"]["username"]); ?></span>
                            <p class="help-block"><a href="javascript:void(0);" onclick="flipChangeUsername();">Change my username</a></p>
                        </div>
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label" for="input01">Pick a password</label>
                    <div class="controls">
                        <input type="password" class="input-xlarge" id="password" name="password">
                    </div>
                </div>
            </fieldset>
        </form>
    </div>
    <div class="modal-footer">
        <a href="javascript:void(0);" class="btn" data-dismiss="modal">I'll do this later</a>
        <a href="javascript:void(0);" onclick="saveUsernamePassword();" class="btn btn-primary disabled" id="change-password-submit">Go</a>
    </div>
</div>


<div class="modal hide" id="login-modal">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">×</button>
        <h2>Login</h2>
    </div>
    <div class="modal-body">
        <form class="form-horizontal">
            <fieldset>
                <div class="control-group">
                    <label class="control-label" for="input01">Email</label>
                    <div class="controls">
                        <input type="text" class="input-xlarge" id="username" name="email">
                        <!-- <p class="help-block">Supporting help text</p> -->
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label" for="input01">Password</label>
                    <div class="controls">
                        <input type="password" class="input-xlarge" id="password" name="password">
                    </div>
                </div>
            </fieldset>
        </form>
    </div>
    <div class="modal-footer">
        <a href="#" class="btn" data-dismiss="modal">Cancel</a>
        <a href="#" class="btn btn-primary">Login</a>
    </div>
</div>


<div class="container">

    <div class="row" style="margin-bottom:20px;">
        <div class="span10"><h1><?php print($page_title); ?></h1></div>
        <div class="span2"><?php print($page_title_right); ?></div>
    </div>

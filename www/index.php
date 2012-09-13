<?php
// |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
require("static_supertop.php");
// |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||





////////////////////////////////////////////////////////////////////////////////
// Page-specific PHP goes here


if (is_logged_in()) {
    print("is logged in = true");
}


if (is_logged_in()) {
    header("Location: " . "/" . $_SESSION["user_info"]["username"]);
}


////////////////////////////////////////////////////////////////////////////////

// The following variables should be set

$page_title = "";
$page_subtitle = "";

?>
<?php require("static_top.php"); ?>
<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->




<!-- Main body of the page goes here -->

<div class="row">

    <div class="span12" style="position:absolute; top:-230px; text-align:center; z-index:-10;">
        <img src="/images/glow.png">
    </div>


    <div class="span12" style="text-align:center">
        <img src="/images/<?php print($g_zipio); ?>_white_big.png">

        <div style="margin-top:50px; font-size:30px; font-weight:100; color:#ffffff; line-height:normal">
            An easy way to collaborate on online photo albums.
        </div>

        <div style="font-size:40px; margin-top:50px; color:#ffffff; line-height:1.5; font-weight:100;">
            To begin, send a photo to <b>myphotos@<?php print($g_zipio); ?>.com</b>
            <br>
            (then wait for an email)
        </div>

    </div>



</div>




<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->
<?php require("static_scripts.php"); ?>
<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->




<script>

// Page-specific JS goes here

$(function() {




});

</script>




<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->
<?php require("static_bottom.php"); ?>
<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->

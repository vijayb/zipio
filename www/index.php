<?php
// |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
require("static_supertop.php");
// |||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||





////////////////////////////////////////////////////////////////////////////////
// Page-specific PHP goes here

if (is_logged_in()) {
    header("Location: " . "/" . $_SESSION["user_info"]["username"]);
}


////////////////////////////////////////////////////////////////////////////////

// The following variables should be set

$page_title = "";
$page_subtitle = "";

?>



<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->
<?php require("static_top.php"); ?>
<!--|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||-->




<!-- Main body of the page goes here -->

<div class="row">

    <div class="span12" style="text-align:center">
        <h1 style="font-size:60px; font-weight:700;">
            Online photo album collaboration over email.
        </h1>
        <br><br>
        <h2>
            Share photos and collaborate on albums, all over email.
            <br>
            There's no app to download and no sign-up.
        </h2>
        <br><br><br>
        <h1>
            <span class="highlight" style="font-weight:700">To begin, send a photo to myphotos@<?php print($g_zipio); ?>.com</span>
        </h1>



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

</div>

<script src="/lib/jquery-1.7.2.min.js"></script>
<script src="/lib/jquery-ui-1.8.21.custom.min.js"></script>
<script src="/lib/jquery.typewatch.js"></script>
<script src="/lib/filtrr.js"></script>
<script src="/lib/jquery.cookie.js"></script>

<script src="/helpers.js"></script>

<script type="text/javascript" src="http://assets.pinterest.com/js/pinit.js"></script>

<script src="/bootstrap/js/bootstrap-alert.js"></script>
<script src="/bootstrap/js/bootstrap-modal.js"></script>
<script src="/bootstrap/js/bootstrap-dropdown.js"></script>
<script src="/bootstrap/js/bootstrap-button.js"></script>
<script src="/bootstrap/js/bootstrap-tooltip.js"></script>
<script src="/bootstrap/js/bootstrap-popover.js"></script>


<script src="http://maps.google.com/maps/api/js?sensor=false&callback=initialize"></script>
<!--
<script src="/bootstrap/js/bootstrap-collapse.js"></script>
<script src="/bootstrap/js/bootstrap-scrollspy.js"></script>
<script src="/bootstrap/js/bootstrap-tab.js"></script>
<script src="/bootstrap/js/bootstrap-tooltip.js"></script>
<script src="/bootstrap/js/bootstrap-carousel.js"></script>
<script src="/bootstrap/js/bootstrap-transition.js"></script>
<script src="/bootstrap/js/bootstrap-typeahead.js"></script>
-->

<script src="/fancybox/jquery.fancybox.pack.js?v=2.0.6"></script>
<script src="/fancybox/helpers/jquery.fancybox-thumbs.js?v=2.0.6"></script>




<script>

// The code below (i.e., all the code in this <script> block) is executed for
// every page on zipio.com that uses the standard template.

// gUser is a global that holds the user information IF AND ONLY IF a user is
// logged in. If a user is not logged in, gUser is undefined. Therefore, to
// check whether a user is logged in via JS, gUser is tested.

var gUser;

var gAlerts = new Array();

var hashParams = {};
(function () {
    var match,
        pl     = /\+/g,  // Regex for replacing addition symbol with a space
        search = /([^&=]+)=?([^&]*)/g,
        decode = function (s) { return decodeURIComponent(s.replace(pl, " ")); },
        query  = window.location.hash.substring(1);
    while (match = search.exec(query))
       hashParams[decode(match[1])] = decode(match[2]);
})();

$(function() {

    // This PHP code populates gUser in the case that there is a logged in
    // user. Recall that PHP knows about the login status through session
    // variables.

    <?php

    if (is_logged_in()) {
        print("gUser = " . json_encode($_SESSION["user_info"]));
    }

    ?>

    $(window).resize(function() {
        $("canvas").remove();
        imageFiltered = {};

        $('button').filter(function() {
            return this.id.match(/save-*/);
        }).hide();
    });

    // If there is an alert URL parameter, show the alert
    var alert = getURLHashParameter("alert");
    if (alert != "null") {
        alert = parseInt(alert);
        $("#header-alert-title").html(getAlert(alert)["title"]);
        $("#header-alert-text").html(getAlert(alert)["text"]);
        $("#header-alert").addClass(getAlert(alert)["class"]);
        $("#header-alert").delay(1000).fadeIn();
    }

    // If the user is logged in but has not yet registered (i.e., set a
    // password), AND there's a hash variable register set to true
    if ((isLoggedIn() && gUser["password_hash"] == "" && getURLHashParameter("register") == "true")
        ||
        (isLoggedIn() && getURLHashParameter("register") == "force")) {
        $('#register-modal').modal('show');
    }

    var modal = getURLHashParameter("modal");
    if (modal != null) {
        if (modal == "comment") {
            showCommentsModal(hashParams["albumphoto_id"], hashParams["albumphoto_s3"]);
            $("#comment-input").focus();
        }
    }



    $('.alert .close').live("click", function(e) {
        $(this).parent().slideUp(function() {
            $(this).parent().removeClass("alert-success");
            $(this).parent().removeClass("alert-error");
            $(this).parent().removeClass("alert-info");
        });
    });





    var delayBeforeChecking = 600;

    // -------------------------------------------------------------------------
    // REGISTER

    $("#register-username").typeWatch({
        callback: function(e) { checkUsernameIsUnique("register"); setRegisterSubmitButton() },
        wait: delayBeforeChecking,
        captureLength: 0
    });

    $("#register-password").keyup(function(e) {
        setRegisterSubmitButton();
    });

    $("#register-modal input").keyup(function(e) {
        if (e.keyCode == 13 && !$("#register-submit").attr("disabled")) {
            $("#register-submit").click();
        }
    });

    // -------------------------------------------------------------------------
    // SIGNUP

    $("#signup-username").typeWatch({
        callback: function(e) { checkUsernameIsUnique("signup"); setSignupSubmitButton(); },
        wait: delayBeforeChecking,
        captureLength: 0
    });

    $("#signup-email").typeWatch({
        callback: function(e) { checkEmailIsOkay("signup"); setSignupSubmitButton(); },
        wait: delayBeforeChecking,
        captureLength: 0
    });

    $("#signup-password").keyup(function(e) {
        setSignupSubmitButton();
    });

    $("#signup-modal input").keyup(function(e) {
        if (e.keyCode == 13 && !$("#signup-submit").attr("disabled")) {
            $("#signup-submit").click();
        }
    });

    // -------------------------------------------------------------------------
    // PASSWORD

    $("#password-email").typeWatch({
        callback: function(e) { checkEmailIsOkay("password"); setPasswordSubmitButton(); },
        wait: delayBeforeChecking,
        captureLength: 0
    });

    $("#password-modal input").keyup(function(e) {
        if (e.keyCode == 13 && !$("#password-submit").attr("disabled")) {
            $("#password-submit").click();
        }
    });

    // -------------------------------------------------------------------------
    // CAPTION

    $("#caption-modal input").keyup(function(e) {
        if (e.keyCode == 13 && !$("#caption-submit").attr("disabled")) {
            $("#caption-submit").click();
        }
    });

    // -------------------------------------------------------------------------
    // COMMENT

    $("#comment-input").typeWatch({
        callback: function(e) { setCommentSubmitButton(); },
        wait: 0,
        captureLength: 0
    });

    $("#comment-modal input").keyup(function(e) {
        if (e.keyCode == 13 && !$("#comment-submit").attr("disabled")) {
            $("#comment-submit").click();
        }
    });

    // -------------------------------------------------------------------------
    // INVITE

    $("#invite-modal textarea").keyup(function(e) {
        if (e.keyCode == 13 && !$("#invite-submit").attr("disabled")) {
            $("#invite-submit").click();
        }
    });
    // -------------------------------------------------------------------------
    // LOGIN

    $("#login-email").typeWatch({
        callback: function() { checkEmailIsOkay("login"); setLoginSubmitButton();},
        wait: delayBeforeChecking,
        captureLength: 0
    });

    $("#login-password").keyup(function(e) {
        setLoginSubmitButton();
    });

    $("#login-modal input").keyup(function(e) {
        if (e.keyCode == 13 && !$("#login-submit").attr("disabled")) {
            $("#login-submit").click();
        }
    });


    // Clear the hash if there was one
    window.location.hash = "";

    if (!isLoggedIn()) {
        // $("body").css({"background-color":"#ffdddd"});
    }


});

</script>
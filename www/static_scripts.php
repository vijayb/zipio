</div>

<script src="/lib/jquery-1.7.2.min.js"></script>
<script src="/lib/jquery-ui-1.8.21.custom.min.js"></script>
<script src="/lib/jquery.masonry.min.js"></script>
<script src="/lib/modernizr.js"></script>
<script src="/lib/jquery.typewatch.js"></script>
<script src="/helpers.js"></script>


<script src="/bootstrap/js/bootstrap-alert.js"></script>
<script src="/bootstrap/js/bootstrap-modal.js"></script>
<script src="/bootstrap/js/bootstrap-dropdown.js"></script>
<script src="/bootstrap/js/bootstrap-scrollspy.js"></script>
<script src="/bootstrap/js/bootstrap-tab.js"></script>
<script src="/bootstrap/js/bootstrap-tooltip.js"></script>
<script src="/bootstrap/js/bootstrap-popover.js"></script>
<script src="/bootstrap/js/bootstrap-button.js"></script>
<script src="/bootstrap/js/bootstrap-collapse.js"></script>
<script src="/bootstrap/js/bootstrap-carousel.js"></script>
<script src="/bootstrap/js/bootstrap-transition.js"></script>
<script src="/bootstrap/js/bootstrap-typeahead.js"></script>

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

$(function() {

    // This PHP code populates gUser in the case that there is a logged in
    // user. Recall that PHP knows about the login status through session
    // variables.

    <?php

    if (is_logged_in()) {
        print("gUser = " . json_encode($_SESSION["user_info"]));
    }

    ?>

    $('.modal').on('shown', function(e) {
        var modal = $(this);
        modal.css('margin-top', (modal.outerHeight() / 2) * -1)
             .css('margin-left', (modal.outerWidth() / 2) * -1);
        return this;
    });

    // If there is an alert URL parameter, show the alert
    var alert = getURLHashParameter("alert");
    if (alert != "null") {
        debug("Showing an alert!")
        alert = parseInt(alert);
        $("#header-alert-title").html(getAlert(alert)["title"]);
        $("#header-alert-text").html(getAlert(alert)["text"]);
        $("#header-alert").addClass(getAlert(alert)["class"]);
        $("#header-alert").fadeIn();
    }

    // If the user is logged in but has not yet registered (i.e., set a
    // password), AND there's a hash variable register set to true
    if (isLoggedIn() && gUser["password_hash"] == "" && getURLHashParameter("register") == "true") {
        $('#register-modal').modal('show');
    }

    $('.alert .close').live("click", function(e) {
        $(this).parent().slideUp(function() {
            $(this).parent().removeClass("alert-success");
            $(this).parent().removeClass("alert-error");
            $(this).parent().removeClass("alert-info");
        });
    });



    var delayBeforeChecking = 300;

    // -------------------------------------------------------------------------
    // REGISTER

    $("#register-username").typeWatch({
        callback: function(e) { checkUsernameIsUnique("register") },
        wait: delayBeforeChecking,
        highlight: true,
        captureLength: 0
    });

    $("#register-modal input").keyup(function(e) {
        debug("hello");
        if (e.keyCode == 13 && !$("#register-submit").attr("disabled")) {
            $("#register-submit").click();
        } else {
            setRegisterSubmitButton();
        }
    });

    // -------------------------------------------------------------------------
    // SIGNUP

    $("#signup-username").typeWatch({
        callback: function(e) { checkUsernameIsUnique("signup"); setSignupSubmitButton(); },
        wait: delayBeforeChecking,
        highlight: true,
        captureLength: 0
    });

    $("#signup-email").typeWatch({
        callback: function(e) { checkEmailIsOkay("signup"); setSignupSubmitButton(); },
        wait: delayBeforeChecking,
        highlight: true,
        captureLength: 0
    });

    $("#signup-modal input").keyup(function(e) {
        if (e.keyCode == 13 && !$("#signup-submit").attr("disabled")) {
            $("#signup-submit").click();
        } else {
            setSignupSubmitButton();
        }
    });

    // -------------------------------------------------------------------------
    // FOLLOW

    $("#follow-email").typeWatch({
        callback: function(e) { checkEmailIsOkay("follow");  setFollowSubmitButton(); },
        wait: 0,
        highlight: true,
        captureLength: 0
    });

    $("#follow-modal input").keyup(function(e) {
        if (e.keyCode == 13 && !$("#follow-submit").attr("disabled")) {
            $("#follow-submit").click();
        }
    });

    // -------------------------------------------------------------------------
    // PASSWORD

    $("#password-email").typeWatch({
        callback: function(e) { checkEmailIsOkay("password"); setPasswordSubmitButton(); },
        wait: delayBeforeChecking,
        highlight: true,
        captureLength: 0
    });

    $("#password-modal input").keyup(function(e) {
        if (e.keyCode == 13 && !$("#password-submit").attr("disabled")) {
            $("#password-submit").click();
        }
    });

    // -------------------------------------------------------------------------
    // LOGIN

    $("#login-email").typeWatch({
        callback: function() { checkEmailIsOkay("login"); setLoginSubmitButton();},
        wait: delayBeforeChecking,
        highlight: true,
        captureLength: 0
    });

    $("#login-modal input").keyup(function(e) {
        if (e.keyCode == 13 && !$("#login-submit").attr("disabled")) {
            $("#login-submit").click();
        } else {
            setLoginSubmitButton();
        }
    });


    // Clear the hash if there was one
    window.location.hash = "";

});

</script>
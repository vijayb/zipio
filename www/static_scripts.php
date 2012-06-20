</div>

<script src="/lib/jquery-1.7.2.min.js"></script>
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

    // If there is an alert URL parameter, show the alert
    var alert = getURLHashParameter("alert");
    if (alert != "null") {
        debug("Showing an alert!")
        alert = parseInt(alert);
        $("#header-alert-title").html(getAlert(alert)["title"]);
        $("#header-alert-text").html(getAlert(alert)["text"]);
        $("#header-alert").addClass(getAlert(alert)["class"]);
        $("#header-alert").fadeIn();
        window.location.hash = "";
    }

    // If the user is logged in but has not yet registered (i.e., set a
    // password), AND there's a hash variable register set to true
    if (isLoggedIn() && gUser["password_hash"] == "" && getURLHashParameter("register") == "true") {
        $('#register-modal').modal('show');
    }

    $('.modal').on('shown', function(e) {
        var modal = $(this);
        modal.css('margin-top', (modal.outerHeight() / 2) * -1)
             .css('margin-left', (modal.outerWidth() / 2) * -1);
        return this;
    });

    $('.alert .close').live("click", function(e) {
        $(this).parent().slideUp(function() {
            $(this).parent().removeClass("alert-success");
            $(this).parent().removeClass("alert-error");
            $(this).parent().removeClass("alert-info");
        });
    });

    $("#register-username").typeWatch({
        callback: function() { checkUsernameIsUnique("register") },
        wait: 300,
        highlight: true,
        captureLength: 0
    });

    $("#signup-username").typeWatch({
        callback: function() { checkUsernameIsUnique("signup"); setSignupSubmitButton(); },
        wait: 300,
        highlight: true,
        captureLength: 0
    });

    $("#signup-email").typeWatch({
        callback: function() { checkEmailIsUnique("signup"); setSignupSubmitButton(); },
        wait: 300,
        highlight: true,
        captureLength: 0
    });

    $("#follow-email").typeWatch({
        callback: function() { checkEmailIsUnique("follow");  setFollowSubmitButton(); },
        wait: 300,
        highlight: true,
        captureLength: 0
    });

    $("#follow-modal input").keyup(function() {
        setFollowSubmitButton();
    });

    $("#signup-modal input").keyup(function() {
        setSignupSubmitButton();
    });

});

</script>
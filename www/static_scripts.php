</div>

<script src="/lib/jquery-1.7.2.min.js"></script>
<script src="/lib/jquery.masonry.min.js"></script>
<script src="/lib/modernizr.js"></script>
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

var user;

$(function() {

    <?php

    if (is_logged_in()) {
        print("user = " . json_encode($_SESSION["user_info"]));
    }

    ?>

    $('.modal').on('shown', function(e) {
        var modal = $(this);
        modal.css('margin-top', (modal.outerHeight() / 2) * -1)
             .css('margin-left', (modal.outerWidth() / 2) * -1);
        return this;
    });

    $("#username").keyup(function() {
        $("#username-check").html("<small>Checking...</small>");
        delay(checkUsername, 300);
    });

    $("#password").keyup(checkUsername);
});

</script>
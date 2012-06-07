</div>

<script src="/lib/jquery-1.7.2.min.js"></script>
<script src="/lib/jquery.masonry.min.js"></script>
<script src="/lib/modernizr.js"></script>

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

<script>

$(function(){
    $masonryContainer = $('#masonry-container');
    $masonryContainer.imagesLoaded(function() {
        $('#masonry-container').masonry({
            itemSelector : '.item',
            isAnimated: true
        });
    });
});

</script>

</body>
</html>
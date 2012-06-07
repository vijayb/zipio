<head>

<link href="/lib/styles.css" rel="stylesheet" />

</head>

<body>
<div class="container">

<div class="row">
    <div class="span12">
        <div id="masonry-container">
            <div class="item"><img src="flower.jpg"></div>
            <div class="item"><img src="flower.jpg"></div>
            <div class="item"><img src="flower.jpg"></div>
            <div class="item"><img src="flower.jpg"></div>
            <div class="item"><img src="flower.jpg"></div>
        </div>
    </div>
</div>

</div>


<script src="/lib/jquery-1.7.2.min.js"></script>
<script src="/lib/jquery.masonry.min.js"></script>
<script src="/lib/modernizr.js"></script>

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
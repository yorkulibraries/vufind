if (typeof window.activateCarousels === 'undefined') {
    window.activateCarousels = function() {
        var settings = {
            lazyLoad: 'ondemand',
            slidesToShow: 5,
            slidesToScroll: 5,
            infinite: false,
            autoplay: false,
            responsive: [
                {
                    // up to 320  (iphone 5s - portrait)
                    breakpoint: 321,
                    settings: {
                        slidesToShow: 1,
                        slidesToScroll: 1
                    }
                },
                {
                    // 321 to 414 (iphone 6 plus - portrait)
                    breakpoint: 415,
                    settings: {
                        slidesToShow: 2,
                        slidesToScroll: 2
                    }
                },
                {
                    // 415 to 736 (iphone 6 plus - landscape)
                    breakpoint: 737,
                    settings: {
                        slidesToShow: 3,
                        slidesToScroll: 3
                    }
                },
                {
                    //  737 to 1024 (ipad)
                    breakpoint: 1025,
                    settings: {
                        slidesToShow: 4,
                        slidesToScroll: 4
                    }
                },
            ]
        };

        $('.carousel:not(.slick-slider):visible').each(function(index) {
            settings.autoplay = $(this).data('autoplay');
            settings.infinite = $(this).data('infinite');
            $(this).slick(settings)
            var startIndex = $(this).data('start-index');
            if(startIndex > 0) {
                $(this).slick('slickGoTo', startIndex);
            }
        });
    };
}

$(document).ready(function () {
    var toLoad = [];
    $('.carousel-not-loaded').each(function(){
        var id = $(this).data('carousel-id');
        if ($('#' + id).size() == 1) {
            toLoad.push(id);
        }
    });
    for (var i=0; i<toLoad.length; i++) {
        $('.carousel-container[data-carousel-id="' + toLoad[i] + '"]', '.carousel-not-loaded').detach().appendTo($('#' + toLoad[i]));
    }
    activateCarousels();
});

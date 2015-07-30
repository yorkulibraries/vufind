if (typeof window.activateCarousels === 'undefined') {
    window.activateCarousels = function() {
        var settings = {
            slidesToShow: 5,
            slidesToScroll: 5,
            infinite: false,
            autoplay: false,
            responsive: [
                {
                  breakpoint: 321,
                  settings: {
                    slidesToShow: 3,
                    slidesToScroll: 3
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

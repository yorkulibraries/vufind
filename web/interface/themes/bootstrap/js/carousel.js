function activateCarousels() { 
    var settings = {
        slidesToShow: 5,
        slidesToScroll: 5,
        infinite: false,
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
    
    $('.carousel:not(.slick-slider)').each(function(index) {
        $(this).slick(settings)
        var startIndex = $(this).data('start-index');
        if(startIndex > 0) {
            $(this).slick('slickGoTo', startIndex);
        }
    });
};
activateCarousels();
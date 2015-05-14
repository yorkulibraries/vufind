{literal}
if (typeof window.VuFind_Carousel_carousels == 'undefined') {
  window.VuFind_Carousel_carousels = new Array();
}

if (typeof window.VuFind_Carousel_createCarousel == 'undefined') {
  window.VuFind_Carousel_createCarousel = function(id, count, html) {
    console.log('Creating carousel ' + id + ', ' + count);

    var jqPlaceHolder = jQuery('#' + id);
    
    var title = jqPlaceHolder.attr('title') ;
    console.log('Carousel ' + id + ' has title: ' + title);
    if (title != null && title.replace(/^\s+|\s+$/g, '').length > 0)
      jqPlaceHolder.append('<h2>' + title + '</h2>');    
    
    jqPlaceHolder.addClass('carousel-container');
    
    jqPlaceHolder.append(html);

    var slider = jQuery('ul', jqPlaceHolder).bxSlider({
        preloadImages: 'visible',
        slideWidth: 128,
        slideMargin: 10,
        minSlides: 1,
        maxSlides: count,
        moveSlides: 1,
        auto: true,
        pager: false,
        pause: 2000,
        autoHover: true
    });
    console.log('Finish creating carousel ' + id + ', ' + count);
  };
}

if (typeof window.VuFind_Carousel_createCarousels == 'undefined') {
  window.VuFind_Carousel_createCarousels = function() {
    jQuery(document).ready(function() {
      var carousels = window.VuFind_Carousel_carousels;
      console.log('Creating carousels... there are ' + carousels.length);
      for (var i = 0; i < carousels.length; i++) {
        window.VuFind_Carousel_createCarousel(carousels[i].id, carousels[i].count, carousels[i].html);
      }
    });
  };
}
{/literal}

(function() {literal}{{/literal}
   
  window.onload = function() {literal}{{/literal}

  if (!window.VuFind_Carousel_cssLoaded) {literal}{{/literal}
    console.log('Adding stylesheets...');
    var css = jQuery('{css media="screen, projection" filename="jquery.bxslider.css"}');
    jQuery('head').append(css.attr('href', css.attr('href').replace('{$path}', '{$url}')));
    css = jQuery('{css media="screen, projection" filename="carousel.css"}');
    jQuery('head').append(css.attr('href', css.attr('href').replace('{$path}', '{$url}')));
    window.VuFind_Carousel_cssLoaded = true;
  {literal}}{/literal}
  
  var scriptUrl = jQuery('{js filename="jquery.bxslider.min.js"}').attr('src').replace('{$path}', '{$url}');
  if (!jQuery().bxSlider && !window.VuFind_Carousel_pluginLoading) {literal}{{/literal}
    window.VuFind_Carousel_pluginLoading = true;
    jQuery.ajax({literal}{dataType: 'script', cache: true, url: scriptUrl}{/literal}).done(
      function() {literal}{{/literal}
        window.VuFind_Carousel_createCarousels();
      {literal}}{/literal}
    );
    console.log('Queueing ' + '{$id}');
    window.VuFind_Carousel_carousels.push( 
      {literal}{{/literal}
        id: '{$id}',
        count: {$count},
        html: {$carousel|json_encode}
      {literal}});{/literal}
  {literal}} else {{/literal}
    if (!jQuery().bxSlider) {literal}{{/literal}
      console.log('Queueing ' + '{$id}');
      window.VuFind_Carousel_carousels.push( 
        {literal}{{/literal}
          id: '{$id}',
          count: {$count},
          html: {$carousel|json_encode}
      {literal}});{/literal}
    {literal}} else {{/literal}
      jQuery(document).ready(function() {literal}{{/literal} 
        window.VuFind_Carousel_createCarousel('{$id}', {$count}, {$carousel|json_encode});
      {literal}});{/literal}
    {literal}}{/literal} 
  {literal}}{/literal} 
   
 {literal}}{/literal}  
{literal}})();{/literal}
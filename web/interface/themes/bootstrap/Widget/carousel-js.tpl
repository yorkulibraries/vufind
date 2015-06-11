var VuFind_carousel_{$id} = {$carousel|json_encode};
var VuFind_js = '{$url}/interface/themes/bootstrap/min/f=';

if (!window.loadingCarouselCSS) window.loadingCarouselCSS=true && document.head.insertAdjacentHTML('beforeend', '{minifycss files="slick.css,slick-theme.css.less,carousel.css.less"}');

document.body.insertAdjacentHTML('beforeend', '<div class="carousel-not-loaded" style="display:none" data-carousel-id="{$id}">' + VuFind_carousel_{$id} + '</div>');

{literal}
window.onload = function() 
{  
  var js = [];
  if (!window.loadingJQueryJS && !window.jQuery) {
    window.loadingJQueryJS = true;
    js.push('js/jquery.min.js');
  }
  if (window.loadingJQueryJS || window.jQuery) {
    if ((window.jQuery && typeof jQuery.fn.slick === 'undefined') || (window.loadingJQueryJS && !window.loadingSlickJS)) {
      window.loadingSlickJS = true;
      js.push('js/slick.js');
    }
  }
  if (!window.loadingCarouselJS && typeof window.activateCarousels === 'undefined') {
    window.loadingCarouselJS = true;
    js.push('js/carousel.js');
  }
  if (js.length > 0 && document.getElementById('VuFind_carousel_script') == null) {
    var scriptTag = document.createElement('script');
    scriptTag.setAttribute('type', 'text/javascript');
    scriptTag.setAttribute('src', VuFind_js + js.join());
    scriptTag.setAttribute('id', 'VuFind_carousel_script');
    document.body.appendChild(scriptTag);
  }
};
{/literal}

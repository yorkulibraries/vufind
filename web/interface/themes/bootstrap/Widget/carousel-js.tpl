document.getElementById('VuFind_{$id}_script').insertAdjacentHTML('afterend', {$carousel|json_encode});

var VuFind_css = '{$url}/interface/themes/bootstrap/min/f=';
var VuFind_js = '{$url}/interface/themes/bootstrap/min/f=';

var styleTag = document.createElement('link');
styleTag.setAttribute('rel', 'stylesheet');
styleTag.setAttribute('type', 'text/css');
styleTag.setAttribute('href', VuFind_css + 'css/slick.css,css/slick-theme.css,css/carousel.css');
styleTag.setAttribute('id', 'VuFind_carousel_style');
if (document.getElementById('VuFind_carousel_style') == null) document.head.appendChild(styleTag);

{literal}
window.onload = function() {
  var js = [];
  if (!window.jQuery) {
    js.push('js/jquery.min.js');
  }
  if (typeof $.fn.Slick !== 'undefined') {
    js.push('js/slick.js');
  }
  if (typeof activateCarousels !== 'function') {
    js.push('js/carousel.js');
  }
  if (js.length > 0) {
    var scriptTag = document.createElement('script');
    scriptTag.setAttribute('type', 'text/javascript');
    scriptTag.setAttribute('src', VuFind_js + js.join());
    scriptTag.setAttribute('id', 'VuFind_carousel_script');
    if (document.getElementById('VuFind_carousel_script') == null) document.body.appendChild(scriptTag);
  }
};
{/literal}

<script type="text/javascript" src="{$url|replace:'https:':''|replace:'http:':''}/Widget/Carousel?id=t{$time}&amp;{$searchUrlParams|escape}"></script>
<div id="t{$time}"></div>

<p>Add the following HTML code into your web page.</p>

<div class="zero-clipboard">
  <span class="btn-clipboard">Copy</span>
</div>
<div class="highlight">
  <pre>
  &lt;script type=&quot;text/javascript&quot; src=&quot;{$url|replace:'https:':''|replace:'http:':''}/Widget/Carousel?id=t{$time}&amp;{$searchUrlParams|escape}&quot;&gt;&lt;/script&gt;
  &lt;div id=&quot;t{$time}&quot;&gt;&lt;/div&gt;
  </pre>
</div>

<script type="text/javascript" src="{$path}/interface/themes/bootstrap/min/f=js/ZeroClipboard.min.js"></script>
<script type="text/javascript">
ZeroClipboard.config( {literal}{{/literal} swfPath: "{$path}/interface/themes/bootstrap/js/ZeroClipboard.swf" {literal}}{/literal} );
var client = new ZeroClipboard($(".copy-button"));
</script>
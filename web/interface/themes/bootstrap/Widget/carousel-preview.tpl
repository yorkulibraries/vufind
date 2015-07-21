{$carouselCode}
  
<p>{translate text='Copy the following HTML code'}:</p>
<div class="zero-clipboard">
  <span class="btn-clipboard" data-clipboard-text="{$carouselCode|escape}">{translate text='Copy'}</span>
</div>
<div class="carousel-code">
  <pre>
    <code class="prettyprint lang-html">{$carouselCode|escape}</code>
  </pre>
</div>

<script src="https://cdn.rawgit.com/google/code-prettify/master/loader/run_prettify.js"></script>
{$carouselCode}
  
<p>{translate text='Copy the following HTML code'}:</p>
<div class="zero-clipboard">
  <span class="btn-clipboard" data-clipboard-text="{$carouselCode|escape}">{translate text='Copy'}</span>
</div>
<div class="carousel-code">
  <pre>
    <code class="language-html" data-lang="html">{$carouselCode|escape}</code>
  </pre>
</div>


{$carouselCode}
<p>{translate text='Copy the following HTML code'}:</p>
<div class="zero-clipboard">
  <span  data-placement="left"  data-copied-message="{translate text='Copied!'}" title="{translate text='Copy to clipboard'}" class="btn-clipboard" data-clipboard-text="{$carouselCode|escape}">{translate text='Copy'}</span>
</div>
<div class="html-code">
  <pre>
    <code class="prettyprint lang-html">{$carouselCode|escape}</code>
  </pre>
</div>

<script type="text/javascript">setupZeroClipboard();</script>
<script src="https://cdn.rawgit.com/google/code-prettify/master/loader/run_prettify.js"></script>


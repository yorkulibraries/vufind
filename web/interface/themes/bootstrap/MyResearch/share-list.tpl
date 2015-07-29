{if $publicListURL}

  <div class="zero-clipboard">
    <span class="btn-clipboard" data-clipboard-text="{$publicListURL|escape}">{translate text='Copy'}</span>
  </div>
  <pre>
    <code>{$publicListURL|escape}</code>
  </pre>
  
  <script type="text/javascript">setupZeroClipboard();</script>
{else}
  <p>{translate text='This list is private. You must make it public in order to share.'}</p>
{/if}

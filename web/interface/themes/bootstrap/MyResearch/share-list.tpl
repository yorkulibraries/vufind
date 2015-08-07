{if $publicListURL}

  <div class="zero-clipboard">
    <span id="shareListCopyButton" data-placement="left"  data-copied-message="{translate text='Copied!'}" title="{translate text='Copy to clipboard'}" class="btn-clipboard" data-clipboard-text="{$publicListURL|escape}">{translate text='Copy'}</span>
  </div>
  <div class="html-code">
  <pre>
    <code>{$publicListURL|escape}</code>
  </pre>
  </div>
  <script type="text/javascript">setupZeroClipboard('#shareListCopyButton');</script>
{else}
  <p>{translate text='This list is private. You must make it public in order to share.'}</p>
{/if}

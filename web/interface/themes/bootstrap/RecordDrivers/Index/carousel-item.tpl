{if $summId && $summThumb}
<li>
  <a href="{$path}/Record/{$summId}"><img alt="{translate text='Cover Image'}" src="{$summThumb}" title="{$summTitle|escape}" /></a>
</li>
{/if}
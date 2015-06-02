{if $summId && $summThumb}
<li data-shelf-order="{$shelfOrder}">
  <a href="{$path}/Record/{$summId}"><img alt="{translate text='Cover Image'}" src="{$summThumb}" title="{$summTitle|escape}" /></a>
</li>
{/if}

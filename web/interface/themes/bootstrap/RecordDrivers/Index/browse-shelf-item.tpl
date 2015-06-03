{if $summId && $summThumb}
<li data-shelf-order="{$shelfOrder}">
  <a href="{$path}/Record/{$summId}">
    <div class="slide-image">
      <img alt="{translate text='Cover Image'}" src="{$summThumb}" title="{$summTitle|escape}" />
    </div>
    <div class="slide-title small">
      {$summTitle|truncate:35|trim:' /:'|escape}
    </div>
  </a>
</li>
{/if}

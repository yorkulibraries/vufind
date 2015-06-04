{if $summId && $summThumb}
<div data-shelf-order="{$shelfOrder}">
  <a href="{$path}/Record/{$summId}">
    <div class="slide-image">
      <img alt="{translate text='Cover Image'}" src="{$summThumb}" title="{$summTitle|escape}" />
    </div>
    <div class="slide-title small">
      {$summTitle|truncate:30|trim:' /:'|escape}
    </div>
  </a>
</div>
{/if}

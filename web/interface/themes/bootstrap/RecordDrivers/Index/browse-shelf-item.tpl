{if $summId && $summThumb}
<div class="browse-shelf-item" data-shelf-order="{$shelfOrder}" data-is-last="false">
  <div class="slide-inner">
    <a href="{$path}/Record/{$summId}">
      <img alt="{translate text='Cover Image'}" src="{$summThumb}" title="{$summTitle|escape}" />
      <div class="small">
        {$summTitle|truncate:30|trim:' /:'|escape}
      </div>
    </a>
  </div>
</div>
{/if}

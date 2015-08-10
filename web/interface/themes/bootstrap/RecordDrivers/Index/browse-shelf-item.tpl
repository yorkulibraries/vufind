{if $summId && $summThumb}
<div class="browse-shelf-item" data-shelf-order="{$shelfOrder}" data-is-last="false" data-callnum="{$callnum|escape}">
  <div class="slide-inner">
    <a href="{$url}/Record/{$summId}">
      <img alt="{translate text='Cover Image'}" data-lazy="{$summThumb}" title="{if $yorkTitleWithoutMedium}{$yorkTitleWithoutMedium|trim:' /:'|escape}{else}{$summTitle|trim:' /:'|escape}{/if}" />
      <div class="small title">
        {if $yorkTitleWithoutMedium}{$yorkTitleWithoutMedium|trim:' /:'|escape}{else}{$summTitle|trim:' /:'|escape}{/if}
      </div>
      <div class="small callnum">
        {$callnum|escape}
      </div>
    </a>
  </div>
</div>
{/if}

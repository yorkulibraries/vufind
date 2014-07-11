{if $summId}
{assign var='bbId' value=$summId}
{elseif $listId}
{assign var='bbId' value=$listId}
{elseif $id}
{assign var='bbId' value=$id}
{/if}
<a data-record-id="{$bbId}"
  class="btn {if $module=='Record'}btn-sm{else}btn-xs{/if} {if $viewBookBag}btn-danger{else}add-remove-bookbag btn-default{/if}"
  href="{$path}/BookBag/{if in_array($bbId, $cartContent)}Remove{else}Add{/if}?id={$bbId}"
  
  data-on-icon="fa-check-square-o"
  data-off-icon="fa-square-o"
  data-on-href="{$path}/BookBag/Remove?id={$bbId}"
  data-off-href="{$path}/BookBag/Add?id={$bbId}"
  data-on-title="{translate text='Remove from book bag'}"
  data-off-title="{translate text='Add to book bag'}"
  >
    <span class="fa {if $viewBookBag}fa-times{elseif in_array($bbId, $cartContent)}fa-check-square-o{else}fa-square-o{/if}"></span>     
    <span class="sr-only add-remove-bookbag-label">
      {if in_array($bbId, $cartContent)}{translate text='Remove from book bag'}{else}{translate text='Add to book bag'}{/if}
    </span>
</a>

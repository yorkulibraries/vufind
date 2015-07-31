{if $summId}
{assign var='bbId' value=$summId}
{elseif $listId}
{assign var='bbId' value=$listId}
{elseif $id}
{assign var='bbId' value=$id}
{/if}

<div class="checkbox">
  <label>
    <input class="mark-unmark-record" type="checkbox" id="record_{$bbId}" value="{$bbId}" aria-label="{if in_array($bbId, $cartContent)}{translate text='Unmark'}{else}{translate text='Mark'}{/if}">
  </label>
</div>

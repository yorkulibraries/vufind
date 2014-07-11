{if empty($recordSet)}
  <p>{translate text='You have no item in your book bag'}.</p>
{else}
  {include file="BookBag/result-summary.tpl"}
  <ul class="media-list result-list">
    {foreach from=$recordSet item=record name="recordLoop"}
      {$record}
    {/foreach}
  </ul>
{/if}
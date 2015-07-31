{if empty($recordSet)}
  <p>{translate text='You have no marked items'}.</p>
{else}
  {include file="BookBag/result-summary.tpl"}
  <ul class="media-list result-list">
    {foreach from=$recordSet item=record name="recordLoop"}
      {$record}
    {/foreach}
  </ul>
{/if}
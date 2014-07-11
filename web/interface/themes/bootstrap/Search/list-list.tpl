{include file="Search/result-summary.tpl"}

<ul class="media-list result-list">
  {foreach from=$recordSet item=record name="recordLoop"}
    {$record}
  {/foreach}
</ul>

{include file="Search/result-pager.tpl"}

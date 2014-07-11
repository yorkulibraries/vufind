<div class="side-facets">
{if $sideRecommendations}
  {foreach from=$sideRecommendations item="recommendations"}
	{include file=$recommendations}
  {/foreach}
{/if}
</div>
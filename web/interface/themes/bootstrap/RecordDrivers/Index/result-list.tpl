<li class="media result-container" data-record-id="{$summId}">
  {include file="RecordDrivers/Index/checkbox.tpl"}
  {include file="RecordDrivers/Index/bookcover.tpl"}
  <div class="media-body result-details">
    <h3 class="media-heading">
      <a href="{$path}/Record/{$summId|escape}">
        {if !empty($yorkHighlightedTitleInfo)}{$yorkHighlightedTitleInfo|highlight|trim:'/.- '}{else}{$yorkTitleInfo|trim:'/.- '|escape}{/if}
      </a>
    </h3>
    {assign var=type value=$recordType|ucwords}
    {include file="RecordDrivers/$type/result-list-extended.tpl"}
  </div>
  <abbr class="unapi-id" title="{$summId}"></abbr>
  {if $summCOinS}
    <span class="Z3988" title="{$summCOinS|escape}"></span>
  {/if}
</li>

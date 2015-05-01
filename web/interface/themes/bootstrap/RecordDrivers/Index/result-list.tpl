<li class="media result-container" data-record-id="{$summId}">
  {include file="RecordDrivers/Index/checkbox.tpl"}
  {include file="RecordDrivers/Index/bookcover.tpl"}
  <div class="media-body result-details">
    <h3 class="media-heading">
      <a href="{$path}/Record/{$summId|escape}">
        {if !empty($summHighlightedTitleInfo)}{$summHighlightedTitleInfo|highlight|trim:'/.- '}{else}{$summTitleInfo|trim:'/.- '|escape}{/if}
      </a>
    </h3>
    {assign var=type value=$recordType|ucwords}
    {include file="RecordDrivers/$type/result-list-extended.tpl"}
  </div>
</li>

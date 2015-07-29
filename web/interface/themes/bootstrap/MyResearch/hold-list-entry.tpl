<li class="media result-container" data-record-id="{$summId}">
  <div class="pull-left number-and-checkbox">
    <span class="result-number">{math equation="x + y" x=$recordStart y=$listItemIndex}.</span>
    {if !$ilsDetails.available}
    <div class="select-item-container">
        <input title="{translate text='Select this item'}" type="checkbox" name="cancelSelectedIDS[]" value="{$ilsDetails.cancel_details}" id="checkbox_{$summId|regex_replace:'/[^a-z0-9]/':''|escape}" />
        <label class="sr-only" for="checkbox_{$summId|regex_replace:'/[^a-z0-9]/':''|escape}">{translate text='Select this item'}</label>
    </div>
    {/if}
  </div>
  
  {include file="RecordDrivers/Index/bookcover.tpl"}
  
  <div class="media-body result-details">
    <h3 class="media-heading">
      <a href="{$path}/Record/{$summId|escape}">
        {if !empty($yorkHighlightedTitleInfo)}{$yorkHighlightedTitleInfo|highlight|trim:'/.- '}{else}{$yorkTitleInfo|trim:'/.- '|escape}{/if}
      </a>
    </h3>
    <dl class="dl-horizontal">
      {if $yorkAuthorInfo}
        <dt class="sr-only">{translate text='Author'}:</dt>
        <dd class="author-info">{$yorkAuthorInfo|trim:' *'|escape}</dd>
      {/if}
      {if $yorkPublicationInfo}
        <dt class="sr-only">{translate text='Publication info'}:</dt>
        <dd class="publication-info">{$yorkPublicationInfo|trim:' *,:/'|escape}</dd>
      {/if}
      
    </dl>
    
    <dl class="dl-horizontal hold-details">
      <dt>{translate text='Status'}:</dt>
        <dd>
          {if $ilsDetails.available}
            <span class="available">{translate text='hold_available'}</span>
            {if $ilsDetails.date_available_expires}
              {$ilsDetails.date_available_expires}
            {/if}
          {else}
            <span class="checkedout">{translate text='Not Available'}</span>
          {/if}
        </dd>
        {if $ilsDetails.available}
          <dt>{translate text='Note'}:</dt>
          <dd>{$ilsDetails.comment|replace:'VuFind - Pickup:':'Pick up @'|escape}</dd>
        {/if}
        <dt>{translate text='Date Created'}:</dt>
        <dd>{$ilsDetails.create|escape}</dd>
        <dt>{translate text='Expiry Date'}:</dt>
        <dd>{$ilsDetails.expire|escape}</dd>
    </dl>
  </div>
  
  <abbr class="unapi-id" title="{$summId}"></abbr>
  
  {if $ilsDetails.cancel_details && !$ilsDetails.available}
    <input type="hidden" name="cancelAllIDS[]" value="{$ilsDetails.cancel_details|escape}" />
  {/if}
</li>

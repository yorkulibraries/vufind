<li class="media result-container" data-record-id="{$summId}">
  <div class="pull-left number-and-checkbox">
    <span class="result-number">{math equation="x + y" x=$recordStart y=$listItemIndex}.</span>
    {if $ilsDetails.renewable && $ilsDetails.overdue == 'N' && empty($ilsDetails.recall_duedate)}
    <div class="select-item-container">
      <input type="hidden" name="renewAllIDS[]" value="{$renewKey|escape}" />
      <input type="checkbox" name="renewSelectedIDS[]" value="{$renewKey|escape}" id="checkbox_{$summId|regex_replace:'/[^a-z0-9]/':''|escape}" />
      <label class="sr-only" for="checkbox_{$summId|regex_replace:'/[^a-z0-9]/':''|escape}">{translate text='Select this item'}</label>
    </div>
    {/if}
  </div>
  
  <div class="media-left">
    {include file="RecordDrivers/Index/bookcover.tpl"}
  </div>
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
    </dl>
    
    {if !empty($ilsDetails.recall_duedate)}
    <div class="alert alert-danger">
      <p>{translate text='recalled_item_warning'}</p>
    </div>
    {/if}
    
    {if $ilsDetails.overdue == 'Y'}
    <div class="alert alert-danger">
      <p>{translate text='overdue_item_warning'}</p>
    </div>
    {/if}
    
    {if $renewResult.$renewKey}
      {assign var="itemRenewResult" value=$renewResult.$renewKey}
      <div class="alert alert-{if $itemRenewResult.success}success{else}danger{/if} alert-dismissable" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="{translate text='Close'}"><span aria-hidden="true">&times;</span></button>
        {if $itemRenewResult.success}
          {translate text='Item renewed'}
        {else}
          <strong>{translate text='Error'}!</strong> {$itemRenewResult.sysMessage|translate|escape}
        {/if}
      </div>
    {/if}
    
    <dl class="dl-horizontal checkout-details">
      <dt>{translate text='Call Number'}:</dt>
        <dd>
          {$ilsDetails.callnum|escape}
        </dd>
        <dt>{translate text='Due Date'}:</dt>
        <dd>
          {if !empty($ilsDetails.recall_duedate)}
            {$ilsDetails.recall_duedate|escape}
            {if !empty($ilsDetails.original_duedate)}
              <span class="previous-due-date">({translate text='Original'}: {$ilsDetails.original_duedate|escape})</span>
            {/if}
          {else}
            {if !empty($ilsDetails.duedate)}
              {$ilsDetails.duedate|escape} {if $ilsDetails.dueTime}{$ilsDetails.dueTime|escape}{/if}
              {if $itemRenewResult.success}
                <span class="previous-due-date">({translate text='Original'}: {$ilsDetails.original_duedate|escape})</span>
              {/if}
            {/if}
          {/if}
        </dd>
        <dt>{translate text='Checkout Date'}:</dt>
        <dd>{$ilsDetails.date_charged|escape}</dd>
        <dt>{translate text='Renewals'}:</dt>
        <dd>{$ilsDetails.number_of_renewals}</dd>
    </dl>
    
  </div>
  <abbr class="unapi-id" title="{$summId}"></abbr>
</li>

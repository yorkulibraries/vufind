<li class="media result-container" data-record-id="{$summId}">
  <div class="pull-left number-and-checkbox">
    <span class="result-number">{math equation="x + y" x=$recordStart y=$listItemIndex}.</span>
    {if $renew_details && $ils_details.renewable && $ils_details.overdue == 'N' && empty($ils_details.recall_duedate)}
    <div>
      <input type="hidden" name="renewAllIDS[]" value="{$renew_details|escape}" />
      <input type="checkbox" name="renewSelectedIDS[]" value="{$renew_details|escape}" id="checkbox_{$summId|regex_replace:'/[^a-z0-9]/':''|escape}" />
      <label class="sr-only" for="checkbox_{$summId|regex_replace:'/[^a-z0-9]/':''|escape}">{translate text='Select this item for renew'}</label>
    </div>
    {/if}
  </div>
  {include file="RecordDrivers/Index/bookcover.tpl"}
  <div class="media-body result-details">
    <h3 class="media-heading">
      <a href="{$path}/Record/{$summId|escape}">
        {$summTitleInfo|escape}
      </a>
    </h3>
    <dl class="dl-horizontal">
      {if $summAuthorInfo}
        <dt class="sr-only">{translate text='Author'}:</dt>
        <dd class="author-info">{$summAuthorInfo|trim:' *'|escape}</dd>
      {/if}
      {if $summPublicationInfo}
        <dt class="sr-only">{translate text='Publication info'}:</dt>
        <dd class="publication-info">{$summPublicationInfo|trim:' *,:/'|escape}</dd>
      {/if}
      {if $ils_details.callnum}
        <dt class="sr-only">{translate text='Call Number'}:</dt>
        <dd class="callnum-info">{$ils_details.callnum|escape}</dd>
      {/if}
      {if !empty($summFormats)}
        <dt class="sr-only">{translate text='Format'}:</dt>
        <dd class="format-info">
        {foreach from=$summFormats item=format name=formats}
          <span class="format">{translate text=$format}</span>{if !$smarty.foreach.formats.last},{/if}
        {/foreach}
        </dd>
      {/if}
    </dl>
    
    {if !empty($ils_details.recall_duedate)}
    <div class="alert alert-danger">
      <p>{translate text='recalled_item_warning'}</p>
    </div>
    {/if}
    
    {if $ils_details.overdue == 'Y'}
    <div class="alert alert-danger">
      <p>{translate text='overdue_item_warning'}</p>
    </div>
    {/if}
    
    {if $renewResult.$renew_details}
      {assign var="itemRenewResult" value=$renewResult.$renew_details}
      <div class="alert alert-{if $itemRenewResult.success}success{else}danger{/if} alert-dismissable">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <p>
          <strong>{if $itemRenewResult.success}{translate text='Item renewed'}{else}{$itemRenewResult.sysMessage|translate|escape}{/if}.</strong>
        </p>
      </div>
    {/if}
    
    <p class="due-date">
    {if !empty($ils_details.recall_duedate)}
      {translate text='Due'}: {$ils_details.recall_duedate|escape}
      {if !empty($ils_details.original_duedate)}
        <span class="previous-due-date">({translate text='Original'}: {$ils_details.original_duedate|escape})</span>
      {/if}
    {else}
      {if !empty($ils_details.duedate)}
        {translate text='Due'}: {$ils_details.duedate|escape} {if $ils_details.dueTime}{$ils_details.dueTime|escape}{/if}
        {if $itemRenewResult.success}
          <span class="previous-due-date">({translate text='Original'}: {$ils_details.original_duedate|escape})</span>
        {/if}
      {/if}
    {/if}
    </p>

    {if $ils_details.number_of_renewals}
      <p class="renewals">{translate text='Renewals'}: {$ils_details.number_of_renewals}</p>
    {/if}

  </div>
</li>

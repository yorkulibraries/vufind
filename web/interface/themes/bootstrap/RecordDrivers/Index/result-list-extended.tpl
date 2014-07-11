<dl class="dl-horizontal">
  {if $summAuthorInfo}
    <dt class="sr-only">{translate text='Author'}:</dt>
    <dd class="author-info">{$summAuthorInfo|trim:' *'|escape}</dd>
  {/if}
  {if $summPublicationInfo}
    <dt class="sr-only">{translate text='Publication info'}:</dt>
    <dd class="publication-info">{$summPublicationInfo|trim:' *,:/'|escape}</dd>
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

{if !empty($summURLs) || !empty($summJournalOpenURLs)} 
<div class="online-access-container hidden">
  {if !empty($summURLs)}
  <div class="normal-links-container hidden">
    <ul>
    {foreach from=$summURLs item=notes key=href name=loop}
      <li class="more-less">
        <span class="label bg-success online-label">{translate text='Online'}</span>
       	<a class="online-access" href="{$href|escape}" target="_blank">
       	  {if $isFond}
            {translate text='Click to access complete finding aid'}
          {else}
            {translate text='Click to access this resource'}
          {/if}
          {if !empty($notes) && $notes != $href}<span class="coverage">({$notes|escape})</span>{/if}
        </a>
      </li>
    {/foreach}
    </ul>
    <button data-toggle="more-less" class="btn btn-default btn-xs hidden" data-threshold="5" data-target=".normal-links-container" data-target-name="normal links"><span class="fa fa-plus"></span> <span class="more-less-label" data-alt="{translate text='Less'}">{translate text="More"}</span></button>
    
  </div>
  {/if}
  
  {if !empty($summJournalOpenURLs)}
  <div class="openurl-container hidden">
    {foreach from=$summJournalOpenURLs item=journalOpenURL key=journalISSN}
      <span data-issn="{$journalISSN}" data-openurl="{$journalOpenURL|escape}" class="openurl hidden"></span>
    {/foreach}
  </div>
  {/if}
</div>
{/if}


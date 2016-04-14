  {if !empty($holdingURLs) || !empty($holdingsJournalOpenURLs)} 
  <div class="online-access-container hidden">
    <h4>{translate text='Online Access'}</h4>
        {if !empty($holdingsJournalOpenURLs)}
          <div class="openurl-container hidden">
            {foreach from=$holdingsJournalOpenURLs item=journalOpenURL key=journalISSN}
              <span data-issn="{$journalISSN}" data-openurl="{$journalOpenURL|escape}" class="openurl hidden"></span>
            {/foreach}
          </div>
        {/if}
        
        {if !empty($holdingURLs)}
          <div class="normal-links-container hidden">
            {foreach from=$allItemRecords item=row name="summaryloop"}
              {if !empty($row.textual_holdings) && $row.location_code=='INTERNET'}
              <div class="well well-sm">
                {if $row.marc852->getSubfield('z')}
                  <p>{translate text='Note'}: {$row.marc852|getvalue:'z'|escape}</p>
                {/if}
                {foreach from=$row.textual_holdings item=textual}
                <p>{translate text='Holdings'}: {$textual|escape}</p>
                {/foreach}
              </div>
              {/if}
            {/foreach}
        
            <ul>
            {foreach from=$holdingURLs item=notes key=href}
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
          </div>
        {/if}
        
        <button data-toggle="more-less" class="btn btn-default btn-xs hidden" data-threshold="10" data-target=".online-access-container" data-target-name="normal links"><span class="fa fa-plus"></span> <span class="more-less-label" data-alt="{translate text='Less'}">{translate text="More"}</span></button>
  </div>
  {/if}
  
  {foreach from=$holdings item=statusItems key=library name="outerloop"}
  {if !empty($library) && !empty($statusItems)}
        {include file="RecordDrivers/Index/holdings-table.tpl"}
        
        <button data-toggle="more-less" class="btn btn-default btn-xs hidden" data-threshold="5" data-target=".panel-body" data-target-name="holdings"><span class="fa fa-plus"></span> <span class="more-less-label" data-alt="{translate text='Less'}">{translate text="More"}</span></button>
  {/if}
  {/foreach}

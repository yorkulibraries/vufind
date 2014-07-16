<div class="panel-group accordion" id="holdingsAccordion">
  {if !empty($holdingURLs) || !empty($holdingsJournalOpenURLs)} 
  <div class="panel panel-default online-access-container hidden">
    <div class="panel-heading">
      <a class="accordion-toggle" data-toggle="collapse" data-parent="#holdingsAccordion" href="#collapseOnlineAccess"><div class="panel-title"><span class="fa fa-chevron-down"></span> {translate text='Online Access'}</div></a>
    </div>
    <div id="collapseOnlineAccess" class="panel-collapse collapse in">
      <div class="panel-body">
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
            <button data-toggle="more-less" class="btn btn-default btn-xs hidden" data-threshold="5" data-target=".normal-links-container" data-target-name="normal links"><span class="fa fa-plus"></span> <span class="more-less-label" data-alt="{translate text='Less'}">{translate text="More"}</span></button>
          </div>
        {/if}
        {if !empty($holdingsJournalOpenURLs)}
          <div class="openurl-container hidden">
            {foreach from=$holdingsJournalOpenURLs item=journalOpenURL key=journalISSN}
              <span data-issn="{$journalISSN}" data-openurl="{$journalOpenURL|escape}" class="openurl hidden"></span>
            {/foreach}
          </div>
        {/if}
      </div>
    </div>
  </div>
  {/if}
  
  {foreach from=$holdings item=statusItems key=library name="outerloop"}
  {if !empty($library) && !empty($statusItems)}
  <div class="panel panel-default">
    <div class="panel-heading">
      <a class="accordion-toggle" data-toggle="collapse" data-parent="#holdingsAccordion" href="#collapse{$smarty.foreach.outerloop.index}"><div class="panel-title"><span class="fa fa-chevron-down"></span> {translate text=$library}</div></a>
    </div>
    <div id="collapse{$smarty.foreach.outerloop.index}" class="panel-collapse collapse in">
      <div class="panel-body">
        {foreach from=$statusItems item=row name="summaryloop"}
        {if !empty($row.textual_holdings)}
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
        
        <div class="table-responsive">
          {include file="RecordDrivers/Index/holdings-table.tpl"}
        </div>
        <button data-toggle="more-less" class="btn btn-default btn-xs hidden" data-threshold="5" data-target=".panel-body" data-target-name="holdings"><span class="fa fa-plus"></span> <span class="more-less-label" data-alt="{translate text='Less'}">{translate text="More"}</span></button>
      </div>
    </div>
  </div>
  {/if}
  {/foreach}
</div>

    {if $recordCount == 0}
      <p class="alert alert-error">{translate text='nohit_prefix'} - <strong>{$reservesLookfor|escape:"html"}</strong> - {translate text='nohit_suffix'}</p>

      {if $parseError}
        <p class="alert alert-error">{translate text='nohit_parse_error'}</p>
      {/if}

      {if $spellingSuggestions}
      <div class="alert alert-info">{translate text='nohit_spelling'}:<br/>
        {foreach from=$spellingSuggestions item=details key=term name=termLoop}
          {$term|escape} &raquo; {foreach from=$details.suggestions item=data key=word name=suggestLoop}<a href="{$data.replace_url|escape}">{$word|escape}</a>{if $data.expand_url} <a href="{$data.expand_url|escape}"><img src="{$path}/images/silk/expand.png" alt="{translate text='spell_expand_alt'}"/></a> {/if}{if !$smarty.foreach.suggestLoop.last}, {/if}{/foreach}{if !$smarty.foreach.termLoop.last}<br/>{/if}
        {/foreach}
      </div>
      {/if}
    {else}
      {include file="Search/reserves-summary.tpl"}
      <ul class="media-list result-list">
        {foreach from=$recordSet item=record name="recordLoop"}
          <li class="media result-container">
            <div class="media-body result-details">
              <h3 class="media-heading">
                <a href="{$path}/Search/Reserves?inst={$record.instructor_id|escape:'url'}&amp;course={$record.course_id|escape:'url'}&amp;lookfor={$lookfor|escape:'url'}">
                  {$record.course|escape}
                </a>
              </h3>
              <p class="reserves-inst-info">{$record.bib_id|@array_unique|@count} {translate text='item(s) reserved by'} {$record.instructor|escape}</p>
            </div>
          </li>
        {/foreach}
      </ul>
      
      
      {include file="Search/result-pager.tpl"}
    {/if}

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
      {include file="Search/result-summary.tpl"}
      
      <table class="table table-condensed table-responsive table-hover">
      <caption class="sr-only">{translate text='Courses and Instructors'}</caption>
      <tr>
        <th>{translate text='Course'}</th>
        <th>{translate text='Instructor'}</th>
        <th>{translate text='Items'}</th>
      </tr>
      {foreach from=$recordSet item=record}
      <tr>
        <td><a href="{$url}/Search/Reserves?inst={$record.instructor_id|escape:'url'}&amp;course={$record.course_id|escape:'url'}&amp;dept={$record.department_id|escape:'url'}">{$record.course|escape}</a></td>
        <td><a href="{$url}/Search/Reserves?inst={$record.instructor_id|escape:'url'}&amp;course={$record.course_id|escape:'url'}&amp;dept={$record.department_id|escape:'url'}">{$record.instructor|escape}</a></td>
        <td>{$record.bib_id|@count}</td>
      </tr>
      {/foreach}
      </table>
      
      {include file="Search/result-pager.tpl"}
    {/if}

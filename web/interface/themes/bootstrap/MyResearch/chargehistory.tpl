<div class="span-19{if $sidebarOnLeft} push-5 last{/if}">
  {if $user->cat_username}
    <h3>{translate text='Your Charge History'}</h3>
    {if $blocks}
      {foreach from=$blocks item=block}
        <p class="info">{translate text=$block}</p>
      {/foreach}
    {/if}

    {if $transList}

      {if $errorMsg}
        <p class="error">{translate text=$errorMsg}</p>
      {/if}

    <div class="resulthead">
      <div class="floatleft">
      {if $recordCount}
        {translate text="Showing"}
        <strong>{$recordStart}</strong> - <strong>{$recordEnd}</strong>
        {translate text='of'} <strong>{$recordCount}</strong>
      {/if}
      </div>
      <div class="floatright">
        <form action="{$path}/Search/SortResults" method="post">
          <label for="sort_options_1">{translate text='Sort'}</label>
          <select id="sort_options_1" name="sort" class="jumpMenu">
          {foreach from=$sortList item=sortData key=sortLabel}
            {if $sortData.desc != 'sort_year' && $sortData.desc != 'sort_year asc' && $sortData.desc != 'sort_callnumber'}
            <option value="{$sortData.sortUrl|escape}"{if $sortData.selected} selected="selected"{/if}>
            {if $sortData.desc != 'sort_relevance'}
              {translate text=$sortData.desc}
            {/if}
            </option>
            {/if}
          {/foreach}
          </select>
          <noscript><input type="submit" value="{translate text="Set"}" /></noscript>
        </form>
      </div>
      <div class="clear"></div>
    </div>
    <ul class="recordSet">
    {foreach from=$transList item=resource name="recordLoop"}
      <li class="result{if ($smarty.foreach.recordLoop.iteration % 2) == 0} alt{/if}">
        <div id="record{$resource.id|escape}">
          <div class="span-2">
            {if $resource.isbn}
              <img src="{$path}/bookcover.php?isn={$resource.isbn|@formatISBN}&amp;size=small" class="summcover" alt="{translate text='Cover Image'}"/>
            {else}
              <img src="{$path}/bookcover.php" class="summcover" alt="{translate text='No Cover Image'}"/>
            {/if}
          </div>
          <div class="span-10">
            {* If $resource.id is set, we have the full Solr record loaded and should display a link... *}
            {if !empty($resource.id)}
              <a href="{$url}/Record/{$resource.id|escape:"url"}" class="title">{$resource.title|escape}</a>
            {* If the record is not available in Solr, perhaps the ILS driver sent us a title we can show... *}
            {elseif !empty($resource.ils_details.title)}
              {$resource.ils_details.title|escape}
            {* Last resort -- indicate that no title could be found. *}
            {else}
              {translate text='Title not available'}
            {/if}
            <br/>
            {if $resource.author}
              {translate text='by'}: <a href="{$url}/Author/Home?author={$resource.author|escape:"url"}">{$resource.author|escape}</a><br/>
            {/if}
            {if $resource.tags}
              {translate text='Your Tags'}:
              {foreach from=$resource.tags item=tag name=tagLoop}
                <a href="{$url}/Search/Results?tag={$tag->tag|escape:"url"}">{$tag->tag|escape}</a>{if !$smarty.foreach.tagLoop.last},{/if}
              {/foreach}
              <br/>
            {/if}
            {if $resource.notes}
              {translate text='Notes'}: {$resource.notes|escape}<br/>
            {/if}
            {if is_array($resource.format)}
              {foreach from=$resource.format item=format}
                <span class="iconlabel {$format|lower|regex_replace:"/[^a-z0-9]/":""}">{translate text=$format}</span>
              {/foreach}
              <br/>
            {elseif isset($resource.format)}
              <span class="iconlabel {$resource.format|lower|regex_replace:"/[^a-z0-9]/":""}">{translate text=$resource.format}</span>
              <br/>
            {/if}
            {if $resource.ils_details.volume}
              <strong>{translate text='Volume'}:</strong> {$resource.ils_details.volume|escape}
              <br />
            {/if}

            {if $resource.ils_details.publication_year}
              <strong>{translate text='Year of Publication'}:</strong> {$resource.ils_details.publication_year|escape}
              <br />
            {/if}

            <strong>{translate text='Call Number'}:</strong> {$resource.ils_details.callnum|escape}
            <br/>
            <strong>{translate text='Date Charged'}:</strong> {$resource.ils_details.date_charged|escape}
            <br/>
            <strong>{translate text='Date Discharged'}:</strong> {$resource.ils_details.date_discharged|escape}
          </div>
          <div class="clear"></div>
        </div>
      </li>
    {/foreach}
    </ul>
    {if $pageLinks.all}<div class="pagination">{$pageLinks.all}</div>{/if}
    {else}
      {translate text='You do not have any items checked out'}.
    {/if}
  {else}
    {include file="MyResearch/catalog-login.tpl"}
  {/if}
</div>

<div class="span-5 {if $sidebarOnLeft}pull-19 sidebarOnLeft{else}last{/if}">
  {include file="MyResearch/menu.tpl"}
</div>

<div class="clear"></div>
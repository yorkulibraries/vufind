{if $pageTemplate != 'advanced.tpl' && $pageTemplate != 'login.tpl'}
  {if $searchType=='advanced'}
    <h1>{translate text='Advanced Search'}</h1>
    <div class="change-search-link">
      <span class="fa fa-search"></span> <a href="{$path}/Search/Home?mylang={$userLang}">{translate text='Basic Search'}</a>
    </div>
    {include file="Search/advanced-search-form.tpl"}
  {else}
    <form class="search-form basic-search" role="form" method="get" action="{$path}/Search/Results">
      {if empty($searchIndex)}
        {assign var='currentSearchType' value='AllFields'}
      {else}
        {assign var='currentSearchType' value=$searchIndex}
      {/if}
      {if $pageTemplate=='reserves.tpl' or $pageTemplate=='reserves-list.tpl'}
        {assign var='currentSearchType' value='Reserves'}
        {assign var='searchIndex' value='Reserves'}
      {/if}
      <input type="hidden" name="type" value="{$currentSearchType}" />
      <label class="sr-only" for="search_form_lookfor">{translate text='Search Terms'}</label>
      <div class="input-group">
        <input autofocus="autofocus" value="{$lookfor|escape}" class="form-control"
        id="search_form_lookfor" type="search" name="lookfor"
        placeholder="{'basic_search_placeholder_'|cat:$currentSearchType|lower|translate|escape}"
        {foreach from=$basicSearchTypes item=searchDesc key=searchVal name=typeloop}
          data-placeholder-{$searchVal|lower}="{'basic_search_placeholder_'|cat:$searchVal|lower|translate|escape}"
        {/foreach}
        >
        <div class="input-group-btn">
          <button type="submit" class="btn btn-default" tabindex="-1" title="{translate text='Find'}"><span class="fa fa-search"></span></button>
          <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" tabindex="-1">
            <div class="visible-xs">
              <span class="fa fa-bars"></span>
              <span class="caret"></span>
            </div>
            <div class="hidden-xs">
              <span class="basic-search-type-menu-label">{$basicSearchTypes.$currentSearchType|translate|escape}</span>
              <span class="caret"></span>
            </div>
            <span class="sr-only">{translate text='Search Type'}</span>
          </button>
          <ul class="dropdown-menu pull-right" role="menu">
            {foreach from=$basicSearchTypes item=searchDesc key=searchVal name=typeloop}
              <li role="presentation" {if (empty($searchIndex) && $searchVal == 'AllFields') || $searchIndex == $searchVal}class="active"{/if}>
                <a data-search-type-label="{$searchDesc|translate|escape}" data-search-type="{$searchVal}" class="basic-search-type-menu-item" role="menuitem" tabindex="-1" href="#">{translate text=$searchDesc}</a>
              </li>
            {/foreach}
          </ul>
        </div>
      </div>
      <div class="change-search-link">
        {if $module=='Record' && $pageTemplate=='view.tpl' && $lastsearch}
        <span class="fa fa-long-arrow-left"></span> <a href="{$lastsearch|escape}">{translate text='Results'}</a>
        {/if}
        <span class="fa fa-search"></span> <a href="{$path}/Search/Advanced?mylang={$userLang}">{translate text='Advanced Search'}</a>
      </div>
      <input type="hidden" name="mylang" value="{$userLang}" />
    </form>
  {/if}
{/if}

{if $pageTemplate != 'list-none.tpl'}
<div class="panel panel-default" id="refineSearch" class="refine-search">
  <div class="panel-heading">
    <h4 class="panel-title">{translate text='Refine search'}</h4>
  </div>
  
  <div class="panel-body">
    <h5 class="current-search-heading">{translate text='Current search'}</h5>
    <ul class="current-search-filters">
      {if $filterList}
        {* Display the filters in the same order as the facets *}
        {foreach from=$sideFacetSet item=cluster key=title name=facetloop}
          {assign var=field value=$cluster.label}
          {assign var=filters value=$filterList[$cluster.label]}
          {if !empty($filters)}
            {foreach from=$filters item=filter}
              <li>
                <div class="search-parameter"><span class="search-field">{translate text=$field}:</span> <span class="search-value">{$filter.display|escape}</span></div>
                {if count($filterList.Source) > 1 || $field !='Source' || $filter.display != 'Catalogue'}
                  <a title="{translate text='Remove this filter'}" class="btn btn-danger btn-xs pull-right" href="{$filter.removalUrl|escape}"><span class="fa fa-times"></span><span class="sr-only">{translate text='Remove this filter'}</span></a>
                {/if}
              </li>
            {/foreach}
          {/if}
        {/foreach}
      {/if}
    </ul>
  </div>
  
  {foreach from=$sideFacetSet item=cluster key=title name=facetloop}
    {assign var='hasUnAppliedFacet' value=0}
    {foreach from=$cluster.list item=facet}
      {if !$facet.isApplied || isset($dateFacets.$title) || in_array($title, $multiSelectFacets)}
        {assign var='hasUnAppliedFacet' value=1}
      {/if}
    {/foreach}
    {assign var=filters value=$filterList[$cluster.label]}
    
    {if $hasUnAppliedFacet}
      {if $cluster.label != 'Call Number (Subclass)' || $displayCallNumberSubclass}
        <h5 class="facet-name">{translate text=$cluster.label}</h5>
      {/if}

      {if isset($dateFacets.$title) || in_array($title, $multiSelectFacets)}
      <div class="panel-body">
        <form class="facet {if isset($dateFacets.$title)}form-inline{/if}" role="form" action="{$path}/{$module}/{$action}" name="{$title|escape}Filter" id="{$title|escape}Filter">
          {* keep existing search parameters as hidden inputs *}
          {foreach from=$smarty.get item=paramValue key=paramName}
            {if is_array($smarty.get.$paramName)}
              {foreach from=$smarty.get.$paramName item=paramValue2}
                {if strpos($paramValue2, $title) !== 0}
                  <input type="hidden" name="{$paramName}[]" value="{$paramValue2|escape}" />
                {/if}
              {/foreach}
            {else}
              {if (strpos($paramName, $title)   !== 0)
                  && (strpos($paramName, 'module') !== 0)
                  && (strpos($paramName, 'action') !== 0)
                  && (strpos($paramName, 'page')   !== 0)}
                {if $paramName != 'submit'}
                <input type="hidden" name="{$paramName}" value="{$paramValue|escape}" />
                {/if}
              {/if}
            {/if}
          {/foreach}
  
          {* form fields for the date range facets *}
          {if isset($dateFacets.$title)}
          <div class="form-group date-range-facet">
              <label for="{$title|escape}from" class="sr-only">{translate text='date_from'}:</label>
              <input maxlength="4" placeholder="{translate text='date_from'}" class="form-control input-sm" type="number" min="1" name="{$title|escape}from" id="{$title|escape}from" value="{if $dateFacets.$title.0}{$dateFacets.$title.0|escape}{/if}" />
              <label for="{$title|escape}to" class="sr-only">{translate text='date_to'}:</label>
              <input maxlength="4" placeholder="{translate text='date_to'}" class="form-control input-sm"  type="number" min="1" name="{$title|escape}to" id="{$title|escape}to" value="{if $dateFacets.$title.1}{$dateFacets.$title.1|escape}{/if}" />
              <input type="hidden" name="daterange[]" value="{$title|escape}"/>
              <input type="submit" class="btn btn-default input-sm" value="{translate text='Set'}" />
          </div>
          {/if}
      
          {* form fields for the multi-select facets *}
          {if in_array($title, $multiSelectFacets)}
            <div class="form-group checkbox-group">
              {foreach from=$cluster.list item=thisFacet name="narrowLoop"}
                <div class="checkbox facet more-less">
                  <input id="{$title}_{$thisFacet.untranslated|lower|regex_replace:'/[^a-z0-9]/':''}" name="filter[]" value="{$title}:&quot;{$thisFacet.untranslated|escape}&quot;" type="checkbox" {if $thisFacet.isApplied}checked="checked"{/if}/>
                  <label for="{$title}_{$thisFacet.untranslated|lower|regex_replace:'/[^a-z0-9]/':''}"><span class="facet-value">{$thisFacet.value|escape}</span>  <span class="facet-count pull-right">{$thisFacet.count}</span></label>
                </div>
              {/foreach}
            </div>
            {if $cluster.label != 'Location'}
            <a data-toggle="more-less" class="btn btn-default btn-xs pull-right hidden" data-threshold="5" data-target=".panel-body" data-target-name="{$cluster.label|escape} facet" href="#"><span class="fa fa-plus"></span> <span class="more-less-label" data-alt="{translate text='Less'}">{translate text="More"}</span></a>
            {/if}
            <input class="sr-only" type="submit" value="{translate text='Set'}"/>
          {/if}
        </form>
      </div>
      {else}
        {* normal "links" facets *}
          {if $cluster.label != 'Call Number (Subclass)' || $displayCallNumberSubclass}
          <div class="panel-body">
            <ul class="facet">
            {foreach from=$cluster.list item=thisFacet name="narrowLoop"}
            {if !$thisFacet.isApplied}
              <li class="more-less">
                <a class="facet" href="{$thisFacet.url|escape}" data-val="{$title}:&quot;{$thisFacet.untranslated|escape}&quot;">
                  <span class="facet-value">{$thisFacet.value|escape}</span> <span class="facet-count pull-right">{$thisFacet.count}</span>
                </a>
              </li>
            {/if}
            {/foreach}
            </ul>
            <a data-toggle="more-less" class="btn btn-default btn-xs pull-right hidden" data-threshold="5" data-target=".panel-body" data-target-name="{$cluster.label|escape} facet" href="#"><span class="fa fa-plus"></span> <span class="more-less-label" data-alt="{translate text='Less'}">{translate text="More"}</span></a>
          </div>
          {/if}
      {/if}
    {/if}
  {/foreach}
</div>
{/if}

<form class="search-form adv-search-form" role="form" method="get" action="{$path}/Search/Results">
  <div class="adv-search-fields">
  {assign var=rowsToStart value=2}
  {assign var=group value=0}
  {assign var=join value=''}
  {if $searchDetails}
    {foreach from=$searchDetails item=searchGroup name=grouploop}
      {assign var=join value=$searchGroup.join}
      {assign var=searchType value=$searchGroup.group.0.field}
      {assign var=lookfor0 value=$searchGroup.group.0.lookfor}
      {assign var=lookfor1 value=$searchGroup.group.1.lookfor}
      {include file="Search/advanced-search-row.tpl"}
      {assign var=group value=$group+1}
    {/foreach}
  {/if}
  
  {section name=grouploop start=$group loop=$rowsToStart step=1}
    {assign var=searchGroup value=false}
    {assign var=join value=''}
    {assign var=searchType value=''}
    {assign var=lookfor0 value=''}
    {assign var=lookfor1 value=''}
    {include file="Search/advanced-search-row.tpl"}
    {assign var=group value=$group+1}
  {/section}
  </div>
  
  <div class="btn-group">
    <button type="submit" class="btn btn-sm btn-primary"><span class="fa fa-search"></span> {translate text='Find'}</button>
  </div>
  <div class="btn-group">
    <button type="button" title="{translate text='Add Search Field'}" class="btn btn-link btn-sm adv-search-add-field">
      <span class="fa fa-plus"></span>
      {translate text='Add Search Field'}
    </button>
  </div>
  
  {if $facetList && $pageTemplate=='advanced.tpl'}
    {if $searchFilters}
      <div class="adv-search-other-filters">
      <ul>
      {foreach from=$searchFilters item=filters key=field name="advFilterLoop"}
        {foreach from=$filters item=filter}
          <li>
            <input id="advFilter_{$smarty.foreach.advFilterLoop.iteration}" type="checkbox" checked="checked" name="filter[]" value="{$filter.field|escape}:&quot;{$filter.value|escape}&quot;" />
            <label for="advFilter_{$smarty.foreach.advFilterLoop.iteration}"><span class="filterLabel">{translate text=$field}:</span> <span class="filterValue">{$filter.display|escape}</span></label>
          </li>
        {/foreach}
      {/foreach}
      </ul>
      </div>
    {/if}

    <div class="row adv-search-limits">
      <div class="col-sm-4 col-lg-3">
        {if $dateRangeLimit}
          <input type="hidden" name="daterange[]" value="publishDate"/>
          <fieldset class="publishDateLimit" id="publishDate">
            <legend>{translate text='adv_search_year'}</legend>
            <div class="adv-search-limit-container">
              <div class="form-group adv-search-date-range">
                <label class="sr-only" for="publishDatefrom">{translate text='date_from'}:</label>
                <input placeholder="{translate text='date_from'}" maxlength="4" class="form-control input-sm" type="number" min="1" name="publishDatefrom" id="publishDatefrom" value="{if $dateFacets.publishDate.0}{$dateFacets.publishDate.0|escape}{/if}" />
                <label class="sr-only" for="publishDateto">{translate text='date_to'}:</label>
                <input placeholder="{translate text='date_to'}" maxlength="4" class="form-control input-sm"  type="number" min="1" name="publishDateto" id="publishDateto" value="{if $dateFacets.publishDate.1}{$dateFacets.publishDate.1|escape}{/if}" />
              </div>
            </div>
          </fieldset>
        {/if}
        {assign var=label value='Format'}
        <fieldset>
          <legend>{translate text=$label}</legend>
          <div class="adv-search-limit-container">
            <div class="form-group">
              {foreach from=$facetList.$label item="value" key="display" name="loop"}
              <div class="radio">
                <input name="filter[]" value="{$value.filter|escape}" id="format_{$display|lower|regex_replace:'/[^a-z0-9]/':''}" type="radio" {if $value.selected}checked="checked"{/if}/>
                <label for="format_{$display|lower|regex_replace:'/[^a-z0-9]/':''}">{$display|translate|escape}</label>
              </div>
              {/foreach}
            </div>
          </div>
        </fieldset>
        {assign var=label value='Source'}
        <fieldset>
        <legend>{translate text=$label}</legend>
        <div class="adv-search-limit-container">
          <div class="form-group">
            {foreach from=$facetList.$label item="value" key="display" name="loop"}
            <div class="checkbox">
              <input name="filter[]" value="{$value.filter|escape}" id="source_{$display|lower|regex_replace:'/[^a-z0-9]/':''}" type="checkbox" {if $value.selected}checked="checked"{/if}/>
              <label for="source_{$display|lower|regex_replace:'/[^a-z0-9]/':''}">{$display|translate|escape}</label>
            </div>
            {/foreach}
          </div>
        </div>
        </fieldset>
      </div>
      <div class="col-sm-4 col-lg-5">
        {assign var=label value='Location'}
        <fieldset>
        <legend>{translate text=$label}</legend>
        <div class="adv-search-limit-container">
          <div class="form-group">
            {foreach from=$facetList.$label item="value" key="display" name="loop"}
              {if ($display == 'Online Access')}
              <div class="checkbox">
                <input name="filter[]" value="{$value.filter|escape}" id="location_{$display|lower|regex_replace:'/[^a-z0-9]/':''}" type="checkbox" {if $value.selected}checked="checked"{/if}/>
                <label for="location_{$display|lower|regex_replace:'/[^a-z0-9]/':''}">{$display|translate|escape}</label>
              </div>
              {/if}
            {/foreach}
            {foreach from=$facetList.$label item="value" key="display" name="loop"}
              {if !($display == 'Online Access')}
              <div class="checkbox">
                <input name="filter[]" value="{$value.filter|escape}" id="location_{$display|lower|regex_replace:'/[^a-z0-9]/':''}" type="checkbox" {if $value.selected}checked="checked"{/if}/>
                <label for="location_{$display|lower|regex_replace:'/[^a-z0-9]/':''}">{$display|translate|escape}</label>
              </div>
              {/if}
            {/foreach}
          </div>
        </div>
        </fieldset>
      </div>
      <div class="col-sm-4 col-lg-4">
        {assign var=label value='Language'}
        <fieldset>
        <legend>{translate text=$label}</legend>
        <div class="adv-search-limit-container">
          <div class="form-group">
            {foreach from=$facetList.$label item="value" key="display" name="loop"}
              {if ($display == 'English' or $display == 'French' or $display == 'German' or $display == 'Italian' or $display == 'Spanish')}
                <div class="checkbox">
                  <input name="filter[]" value="{$value.filter|escape}" id="language_{$display|lower|regex_replace:'/[^a-z0-9]/':''}" type="checkbox" {if $value.selected}checked="checked"{/if}/>
                  <label for="language_{$display|lower|regex_replace:'/[^a-z0-9]/':''}">{$display|translate|escape}</label>
                </div>
              {/if}
            {/foreach}
            {foreach from=$facetList.$label item="value" key="display" name="loop"}
              {if !($display == 'English' or $display == 'French' or $display == 'German' or $display == 'Italian' or $display == 'Spanish')}
                <div class="checkbox">
                  <input name="filter[]" value="{$value.filter|escape}" id="language_{$display|lower|regex_replace:'/[^a-z0-9]/':''}" type="checkbox" {if $value.selected}checked="checked"{/if}/>
                  <label for="language_{$display|lower|regex_replace:'/[^a-z0-9]/':''}">{$display|translate|escape}</label>
                </div>
              {/if}
            {/foreach}
          </div>
        </div>
        </fieldset>
      </div>
    </div>
  {/if}
  <input type="hidden" name="mylang" value="{$userLang}" />
</form>

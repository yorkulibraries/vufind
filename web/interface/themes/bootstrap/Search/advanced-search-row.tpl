<div class="row" data-row-index="{$group}">
  {if $group > 0}
  <div class="col-sm-1">
      <div class="adv-search-join-container">
        <input class="join-menu-value" type="hidden" name="join{$group}" value="{if $join}{$join}{else}AND{/if}" data-default="AND"/>
        <button title="{translate text='Select join boolean operator'}" type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" tabindex="-1">
          <span class="join-menu-label">{if empty($join)}{translate text='AND'}{else}{$join|translate}{/if}</span>
          <span class="caret"></span>
        </button>
        <ul class="dropdown-menu" role="menu">
          <li role="presentation" {if empty($join) || $join == 'AND'}class="active"{/if}>
            <a data-value="AND" data-label="{translate text='AND'}" class="adv-search-join-menu-item"  role="menuitem" tabindex="-1" href="#">{translate text='AND'}</a>
          </li>
          <li role="presentation" {if $join == 'OR'}class="active"{/if}>
            <a data-value="OR" data-label="{translate text='OR'}" class="adv-search-join-menu-item" role="menuitem" tabindex="-1" href="#">{translate text='OR'}</a>
          </li>
          <li role="presentation" {if $join == 'NOT'}class="active"{/if}>
            <a data-value="NOT" data-label="{translate text='NOT'}" class="adv-search-join-menu-item" role="menuitem" tabindex="-1" href="#">{translate text='NOT'}</a>
          </li>
        </ul>
      </div>
  </div>
  {/if}
  <div class="{if $group > 0}col-sm-4{else}col-sm-5{/if}">
    <div class="adv-search-lookfor-container">
      <input autofocus="autofocus" class="form-control input-sm" type="text" id="adv_search_lookfor{$group}0" name="lookfor{$group}[]" value="{$lookfor0|escape}" />
    </div>
  </div>
  <div class="col-sm-4">
    <div class="input-group adv-search-lookfor-container">
      <span class="input-group-addon">{translate text='OR'}</span>
      <input class="form-control input-sm" type="text" id="adv_search_lookfor{$group}1" name="lookfor{$group}[]" value="{$lookfor1|escape}" />
    </div>
  </div>
  <div class="col-sm-3">
    <div class="adv-search-type-container">
      <input class="type-menu-value" type="hidden" name="type{$group}[]" value="{if $searchType}{$searchType}{else}AllFields{/if}" data-default="AllFields" />
      <div class="btn-group">
        <button title="{translate text='Select search type'}" type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" tabindex="-1">
          <span class="type-menu-label">{if empty($searchType)}{$advSearchTypes.AllFields|translate}{else}{$advSearchTypes.$searchType|translate}{/if}</span>
          <span class="caret"></span>
        </button>
        <ul class="dropdown-menu" role="menu">
          {foreach from=$advSearchTypes item=searchDesc key=searchVal name=typeloop}
            <li role="presentation" {if (empty($searchType) && $searchVal == 'AllFields') || $searchType == $searchVal}class="active"{/if}>
              <a data-value="{$searchVal}" data-label="{translate text=$searchDesc}" class="adv-search-type-menu-item" role="menuitem" tabindex="-1" href="#">{translate text=$searchDesc}</a>
            </li>
          {/foreach}
        </ul>
      </div>
      <div class="btn-group">
        <button type="button" title="{translate text='Add row'}" class="btn btn-default btn-sm adv-search-add-field {if $group < $numrows - 1}hidden{/if}">
          <span class="fa fa-plus"></span>
          <span class="sr-only">{translate text='Add row'}</span>
        </button>
      </div>
    </div>
    
  </div>
</div>

<a class="sr-only" href="#record-tabs">{translate text='Skip to holdings'}</a>
<div class="print-hidden">
  <div class="pull-left">
    {if $previousRecord}
    <div class="btn-group">
      <a title="{translate text='Prev'}" class="btn btn-default btn-sm" href="{$path}/Record/{$previousRecord}"><span class="fa fa-arrow-left"></span> <span class="hidden-xs">{translate text='Prev'}</span></a>
    </div>
    {/if}
    {if $nextRecord}
    <div class="btn-group">
      <a title="{translate text='Next'}" class="btn btn-default btn-sm" href="{$path}/Record/{$nextRecord}"><span class="hidden-xs">{translate text='Next'}</span> <span class="fa fa-arrow-right"></span></a>
    </div>
    {/if}
  </div>
  <div class="pull-right">
    <div class="btn-group">
      <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown">
        <div class="visible-xs"><span class="fa fa-bars"></span> <span class="caret"></span></div><div class="hidden-xs"><span class="hidden-xs">{translate text='Options'}</span> <span class="caret"></span></div>
      </button>
      <ul class="dropdown-menu" role="menu">
        <li role="presentation"><a role="menuitem" tabindex="-1" data-toggle="modal" data-target="#modal" href="{$path}/Record/{$id}/Cite"><span class="fa fa-quote-left"></span> {translate text='Cite this'}</a></li>
        <li role="presentation"><a role="menuitem" tabindex="-1" data-toggle="modal" data-target="#modal" href="{$path}/Record/{$id}/SMS"><span class="fa fa-mobile-phone"></span> {translate text='Text this'}</a></li>
        <li role="presentation"><a role="menuitem" tabindex="-1" data-toggle="modal" data-target="#modal" href="{$path}/Record/{$id}/Email"><span class="fa fa-envelope-o"></span> {translate text='Email this'}</a></li>
        <li role="presentation"><a role="menuitem" tabindex="-1" target="_blank" href="{$path}/Record/{$id}/Export?style=refworks"><span class="fa fa-folder-open-o"></span> {translate text='RefWorks'}</a></li>
        <li role="presentation"><a role="menuitem" tabindex="-1" data-toggle="modal" data-target="#modal" title="{translate text='Save this record permanently in your account'}" href="{$path}/Record/{$id}/Save"><span class="fa fa-save"></span> {translate text='Save'}</a></li>
      </ul>
    </div>
    <div class="btn-group">
      {include file="RecordDrivers/Index/add-remove-bookbag.tpl"}
      {include file="Search/bookbag.tpl"}
    </div>
  </div>
  
  <div class="clearfix"></div>
</div>

<div class="alert-container"></div>

<div class="record-container" data-record-id="{$id}">
  
  {include file=$coreMetadata}

  <ul class="nav nav-tabs responsive record-view-tabs" id="record-tabs">
    <li {if $tab == 'Holdings'}class="active"{/if}>
      <a data-toggle="tab" data-target="#Holdings" href="{$path}/Record/{$id}/Holdings">{translate text='Holdings'}</a>
    </li>
    {if $hasTOC}
    <li {if $tab == 'TOC'}class="active"{/if}>
      <a data-toggle="tab" data-target="#TOC" href="{$path}/Record/{$id}/TOC">{translate text='Table of Contents'}</a>
    </li>
    {/if}
    <li {if $tab == 'UserComments'}class="active"{/if}>
      <a data-toggle="tab" data-target="#UserComments" href="{$path}/Record/{$id}/UserComments">{translate text='Comments'}</a>
    </li>
    {if $hasReviews}
    <li {if $tab == 'Reviews'}class="active"{/if}>
      <a data-toggle="tab" data-target="#Reviews" href="{$path}/Record/{$id}/Reviews">{translate text='Reviews'}</a>
    </li>
    {/if}
    {if $hasExcerpt}
    <li {if $tab == 'Excerpt'}class="active"{/if}>
      <a data-toggle="tab" data-target="#Excerpt" href="{$path}/Record/{$id}/Excerpt">{translate text='Excerpt'}</a>
    </li>
    {/if}
    {if $hasMap}
    <li {if $tab == 'Map'}class="active"{/if}>
      <a data-toggle="tab" data-target="#Map" href="{$path}/Record/{$id}/Map">{translate text='Map View'}</a>
    </li>
    {/if}
    {if $hasStaffView}
    <li {if $tab == 'Details'}class="active"{/if}>
      <a data-toggle="tab" data-target="#Details" href="{$path}/Record/{$id}/Details">{translate text='Staff View'}</a>
    </li>
    {/if}
  </ul>
      
  <div class="tab-content responsive">
    <div class="{if $tab == 'Holdings' || $tab == 'Hold'}tab-pane active{/if}" id="Holdings">
    {if $tab == 'Holdings' || $tab == 'Hold'}
      <div id="Holdings-tab-content">
        {include file="Record/$subTemplate"}
      </div>
    {/if}
    </div>
    
    <div class="tab-pane {if $tab == 'TOC'}active{/if}" id="TOC">
    {if $hasTOC && $tab == 'TOC'}
      <div id="TOC-tab-content">
        {include file="Record/$subTemplate"}
      </div>
    {/if}
    </div>
    
    <div class="tab-pane {if $tab == 'UserComments'}active{/if}" id="UserComments">
    {if $tab == 'UserComments'}
      <div id="UserComments-tab-content">
        {include file="Record/$subTemplate"}
      </div>
    {/if}
    </div>

    <div class="tab-pane {if $tab == 'Reviews'}active{/if}" id="Reviews">
    {if $hasReviews && $tab == 'Reviews'}
      <div id="Reviews-tab-content">
        {include file="Record/$subTemplate"}
      </div>
    {/if}
    </div>
    
    <div class="tab-pane {if $tab == 'Exerpt'}active{/if}" id="Excerpt">
    {if $hasExcerpt && $tab == 'Excerpt'}
      <div id="Excerpt-tab-content">
        {include file="Record/$subTemplate"}
      </div>
    {/if}
    </div>
    
    <div class="tab-pane {if $tab == 'Map'}active{/if}" id="Map">
    {if $hasMap && $tab == 'Map'}
      <div id="Map-tab-content">
        {include file="Record/$subTemplate"}
      </div>
    {/if}
    </div>
    
    {if $hasStaffView}
    <div class="tab-pane {if $tab == 'Details'}active{/if}" id="Details">
    {if $tab == 'Details'}
      <div id="Details-tab-content">
        {include file="Record/$subTemplate"}
      </div>
    {/if}
    </div>
    {/if}
  </div>
</div>
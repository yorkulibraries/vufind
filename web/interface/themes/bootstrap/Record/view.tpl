

<div class="alert-container"></div>

<div class="record-container" data-record-id="{$id}">
  <abbr class="unapi-id hidden" title="{$id}"></abbr>
  
  <div class="media">
    {include file="RecordDrivers/Index/bookcover.tpl"}
    <div class="media-body">
      <h2 class="media-heading">
        {if $fullTitle}
          {$fullTitle|escape}
        {else}
          {$coreShortTitle|escape}
          {if $coreSubtitle}{$coreSubtitle|escape}{/if}
          {if $coreTitleSection}{$coreTitleSection|escape}{/if}
        {/if}
      </h2>
      
      <dl class="brief-details">
        {if $yorkAuthorInfo}
          <dt class="sr-only">{translate text='Author'}:</dt>
          <dd class="author-info">{$yorkAuthorInfo|trim:' *'|escape}</dd>
        {/if}
        {if $yorkPublicationInfo}
          <dt class="sr-only">{translate text='Publication info'}:</dt>
          <dd class="publication-info">{$yorkPublicationInfo|trim:' *,:/'|escape}</dd>
        {/if}
        {if !empty($recordFormat)}
          <dt class="sr-only">{translate text='Format'}:</dt>
          <dd class="format-info">
          {foreach from=$recordFormat item=format name=formats}
            <span class="format">{translate text=$format}</span>{if !$smarty.foreach.formats.last},{/if}
          {/foreach}
          </dd>
        {/if}
      </dl>
      
    </div>
  </div>
  
  <div class="record-navbar print-hidden">
    <div class="pull-left hidden-xs">
      <div class="btn-group">
      {if $lastsearch}
        <a title="{translate text='Go back to search results'}" class="btn btn-link btn-sm" href="{$lastsearch|escape}"><span class="fa fa-search"></span> {translate text='Search Results'}</a>
      {else}
        <a title="{translate text='New search'}" class="btn btn-link btn-sm" href="{$path}"><span class="fa fa-search"></span> {translate text='New Search'}</a>
      {/if}
      {if $previousRecord}
        <a title="{translate text='Go to previous result'}" class="btn btn-link btn-sm" href="{$path}/Record/{$previousRecord}"><span class="fa fa-arrow-left"></span> {translate text='Prev'}</a>
      {/if}
      {if $nextRecord}
        <a title="{translate text='Go to next result'}" class="btn btn-link btn-sm" href="{$path}/Record/{$nextRecord}">{translate text='Next'} <span class="fa fa-arrow-right"></span></a>
      {/if}
      </div>
    </div>
    <div class="pull-right">
      <div class="btn-group">
        <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown">
          {translate text='Options'} <span class="caret"></span>
        </button>
        <ul class="dropdown-menu" role="menu">
          <li role="presentation"><a role="menuitem" tabindex="-1" data-toggle="modal" data-target="#modal" href="{$path}/Record/{$id}/Cite"><span class="fa fa-quote-left"></span> {translate text='Cite this'}</a></li>
          <li role="presentation"><a role="menuitem" tabindex="-1" data-toggle="modal" data-target="#modal" href="{$path}/Record/{$id}/SMS"><span class="fa fa-mobile-phone"></span> {translate text='Text this'}</a></li>
          <li role="presentation"><a role="menuitem" tabindex="-1" data-toggle="modal" data-target="#modal" href="{$path}/Record/{$id}/Email"><span class="fa fa-envelope-o"></span> {translate text='Email this'}</a></li>
          <li role="presentation"><a role="menuitem" tabindex="-1" data-toggle="modal" data-target="#modal" title="{translate text='Save this record permanently in your account'}" href="{$path}/Record/{$id}/Save"><span class="fa fa-save"></span> {translate text='Save'}</a></li>
        </ul>
      </div>
      <div class="btn-group">
        {include file="Search/bookbag.tpl"}
      </div>
    </div>

    <div class="clearfix"></div>
  </div>

  <ul aria-hidden="true" class="nav nav-tabs responsive record-view-tabs" id="record-tabs">
    <li {if $tab == 'Holdings'}class="active"{/if}>
      <a data-toggle="tab" data-target="#Holdings" href="{$path}/Record/{$id}/Holdings">{translate text='Details'}</a>
    </li>
    <li {if $tab == 'UserComments'}class="active"{/if}>
      <a data-toggle="tab" data-target="#UserComments" href="{$path}/Record/{$id}/UserComments">{translate text='Comments'}</a>
    </li>
    {if $hasStaffView}
    <li class="hidden-xs {if $tab == 'Details'}active{/if}">
      <a data-toggle="tab" data-target="#Details" href="{$path}/Record/{$id}/Details">{translate text='Staff View'}</a>
    </li>
    {/if}
  </ul>
      
  <div class="tab-content responsive">
    <div class="{if $tab == 'Holdings' || $tab == 'Hold'}tab-pane active{/if}" id="Holdings">
    {if $tab == 'Holdings' || $tab == 'Hold'}
      <div id="Holdings-tab-content">
        <h3 class="pull-left">{translate text="Holdings"}</h3>
        <div class="pull-right">
          <div class="btn-group">
            <a href="{$path}/Record/{$id}/Hold" class="btn btn-primary btn-toggle" data-toggle="modal" data-target="#modal">{translate text='Place Hold'}</a>
          </div>
          <div class="btn-group">
            <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
              {translate text='Request'} <span class="caret"></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-right" role="menu">
              <li role="presentation"><a role="menuitem" tabindex="-1" data-toggle="modal" data-target="#modal" href="{$path}/Record/{$id}/ICB">{translate text='Inter-campus Borrowing'}</a></li>
              <li role="presentation"><a role="menuitem" tabindex="-1" data-toggle="modal" data-target="#modal" href="{$path}/Record/{$id}/InProcess">{translate text='In-Process/On-Order'}</a></li>
              <li role="presentation"><a role="menuitem" tabindex="-1" data-toggle="modal" data-target="#modal" href="{$path}/Record/{$id}/Storage">{translate text='Storage/Special Collections'}</a></li>
            </ul>
          </div>
        </div>
        
        <div class="clearfix"></div>
        
        <div class="section">
          {include file="Record/$subTemplate"}
        </div>
        
        {if !empty($browseShelf)}
        <div class="section">
          <h3>{translate text="On the Shelf"}</h3>
          {$browseShelf}
        </div>
        {/if}
        
        {if !empty($coreSubjects)}
          <h3>{translate text='Subjects'}</h3>
          <div class="section">
            <dl class="dl-horizontal">
              {foreach from=$coreSubjects key=fieldName item=field name=loop}
              <dt>{translate text=$fieldName}:</dt>
              <dd>
              <div class="subject-line">
                {assign var=subject value=""}
                {foreach from=$field item=subfield name=subloop}
                  {if !$smarty.foreach.subloop.first} &raquo; {/if}
                  {assign var=subject value="$subject $subfield"}
                  <a title="{$subject|escape}" href="{$url}/Search/Results?lookfor=%22{$subject|escape:'url'}%22&amp;type=Subject">{$subfield|escape}</a>
                {/foreach}
              </div>
              </dd>
              {/foreach}
            </dl>
          </div>
        {/if}
        
        <h3>{translate text="More Details"}</h3>
        <div class="section">
        {include file=$coreMetadata}
        </div>
        
        {if $hasTOC && $tocTemplate}
          <h3>{translate text='Table of Contents'}</h3>
          <div class="section">
            {include file=$tocTemplate}
          </div>
        {/if}
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
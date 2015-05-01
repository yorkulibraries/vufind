<li class="media result-container" data-record-id="{$listId}">
  {include file="RecordDrivers/Index/checkbox.tpl"}
  {include file="RecordDrivers/Index/bookcover.tpl"}
  <div class="media-body result-details">
    <h3 class="media-heading">
      <a href="{$path}/Record/{$listId|escape}">
        {$listTitleInfo|escape}
      </a>
    </h3>
    <dl class="dl-horizontal result-fields">
      {if $listAuthorInfo}
        <dt class="sr-only">{translate text='Author'}:</dt>
        <dd class="author-info">{$listAuthorInfo|trim:' *'|escape}</dd>
      {/if}
      {if $listPublicationInfo}
        <dt class="sr-only">{translate text='Publication info'}:</dt>
        <dd class="publication-info">{$listPublicationInfo|trim:' *,:/'|escape}</dd>
      {/if}
      {if !empty($listFormats)}
        <dt class="sr-only">{translate text='Format'}:</dt>
        <dd class="format-info">
        {foreach from=$listFormats item=format name=formats}
          <span class="format">{translate text=$format}</span>{if !$smarty.foreach.formats.last},{/if}
        {/foreach}
        </dd>
      {/if}
      {if $listNotes}
        <dt class="sr-only">{translate text='Your Notes'}:</dt>
        <dd>
          {foreach from=$listNotes item=note}
            <div class="notes">
              {$note|escape}
            </div>
          {/foreach}
        </dd>
      {/if}
    </dl>
    {if $isJournal}
      {if !empty($listJournalOpenURLs)}
      <div class="online-access-container hidden">
        <div class="openurl-container hidden">
          {foreach from=$listJournalOpenURLs item=journalOpenURL}
            <span data-openurl="{$journalOpenURL|escape}" class="openurl hidden"></span>
          {/foreach}
        </div>
      </div>
      {/if}
    {/if}
    <div class="ajax-availability"></div>
    
    {if $listEditAllowed}
    <div class="alert-container"></div>
    <div data-backdrop="false" class="modal" id="modal{$listId}" aria-labelledby="modalTitle" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
        </div>
      </div>
    </div>
    <div class="action-container print-hidden">
      <a data-toggle="modal" data-target="#modal{$listId}" class="btn btn-primary btn-xs" title="{translate text='Edit this item'}" href="{$path}/MyResearch/Edit?id={$listId|escape:"url"}{if !is_null($listSelected)}&amp;list_id={$listSelected|escape:'url'}{/if}"><span class="fa fa-edit"></span> {translate text='Edit'}</a>
      <a data-confirm="{translate text='Delete this item'}?" {if $listSelected}data-list-id="{$listSelected}"{/if} data-record-id="{$listId}" class="btn btn-danger btn-xs delete-list-item" title="{translate text='Delete this item'}" 
        {if is_null($listSelected)}
          href="{$path}/MyResearch/Favorites?delete={$listId}"
        {else}
          href="{$path}/MyResearch/MyList/{$listSelected}?delete={$listId}"
        {/if}><span class="fa fa-trash-o"></span> {translate text='Delete'}</a>
    </div>
    {/if}
  </div>
  <abbr class="unapi-id" title="{$listId}"></abbr>
</li>

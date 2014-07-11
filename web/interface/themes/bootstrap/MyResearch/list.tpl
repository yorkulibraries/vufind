{if $listList}
<ul class="nav nav-tabs my-list-tabs">
  {if $pageTemplate=="favorites.tpl"}
  <li class="active"><a href="{$path}/MyResearch/Favorites">{translate text="All Saved Items"}</a></li>
  {/if}
  {foreach from=$listList item=listItem}
  <li {if $list && $listItem->id == $list->id}class="active"{/if}>
    <a title="{$listItem->title|escape}" href="{$path}/MyResearch/MyList/{$listItem->id}">{$listItem->title|escape}</a>
  </li>
  {/foreach}
</ul>
{/if}

{if $list}
  {if $list->description}
    <div class="well well-sm">
      {$list->description|escape}
    </div>
  {/if}
{/if}

{if $errorMsg}
<div class="alert alert-danger">
  {$errorMsg|translate}
</div>
{/if}

{if $infoMsg}
<div class="alert alert-info">
  {$infoMsg|translate}
</div>
{/if}

{if $list}
  {if !$recordCount}{assign var=recordStart value=0}{/if}

  {include file="MyResearch/result-summary.tpl"}

    {if $resourceList}
    <ul class="media-list result-list">
      {foreach from=$resourceList item=resource name="recordLoop"}
        {$resource}
      {/foreach}
    </ul>
      {include file="Search/result-pager.tpl"}
    {else}
      <p>{translate text='You do not have any saved resources'}</p>
    {/if}
{/if}
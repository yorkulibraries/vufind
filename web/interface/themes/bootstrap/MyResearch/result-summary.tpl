<div class="result-summary">
  <h2 class="pull-left">
    {translate text='Items'} {$recordStart} - {$recordEnd} {translate text='of'} {$recordCount}
  </h2>
  <div class="pull-right print-hidden">
    <div class="btn-group">
      {include file="Search/sort.tpl"}
    </div>
    <div class="btn-group">
      <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown">
        {translate text='Options'} <span class="caret"></span>
      </button>
      <ul class="dropdown-menu" role="menu">
      {if $recordCount}
        {if $list->public}
        <li><a data-toggle="modal" data-target="#modal" role="menuitem" tabindex="-1" href="{$path}/MyResearch/ShareList/{$list->id}"><span class="fa fa-link"></span> {translate text='Share'}</a></li>
        <li><a target="_blank" role="menuitem" tabindex="-1" href="{$path}/MyResearch/ExportList/{$list->id}?style=refworks"><span class="fa fa-folder-open-o"></span> {translate text='RefWorks'}</a></li>
        {/if}
        <li><a data-toggle="modal" data-target="#modal" role="menuitem" tabindex="-1" href="{$path}/MyResearch/EmailList/{$list->id}"><span class="fa fa-envelope-o"></span> {translate text='Email'}</a></li>
        {if $list->public}
          <li><a role="menuitem" tabindex="-1" href="{$path}/MyResearch/MyList/{$list->id}?view=rss"><span class="fa fa-rss"></span> {translate text='Get RSS Feed'}</a></li>
          <li><a role="menuitem" tabindex="-1" href="{$path}/Widget/Carousel?list={$list->id}&preview=1"><span class="fa fa-rss"></span> {translate text='Get Carousel'}</a></li>
        {/if}
      {/if}
      {if $user && $user->id == $list->user_id}
        {if $pageTemplate != "favorites.tpl" && $action != 'Favorites'}
          <li class="divider"></li>
          <li><a data-toggle="modal" data-target="#modal"  role="menuitem" tabindex="-1" href="{$path}/MyResearch/EditList/{$list->id}"><span class="fa fa-edit"></span> {translate text='Edit This List'}</a></li>
          <li><a class="delete-list" data-confirm="{translate text='Delete this list and all items in it'}?" role="menuitem" tabindex="-1" href="{$path}/MyResearch/DeleteList/{$list->id}"><span class="fa fa-trash-o"></span> {translate text='Delete This List'}</a></li>
        {/if}
      {/if}
      </ul>
    </div>
    <div class="btn-group">
      {include file="Search/bookbag.tpl"}
    </div>
  </div>

  <div class="clearfix"></div>
</div>
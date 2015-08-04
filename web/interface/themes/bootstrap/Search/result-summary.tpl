<div class="result-summary">
  <h2 class="pull-left">
    {translate text='Results'} {$recordStart} - {$recordEnd} {translate text='of'} {$recordCount}
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
        <li class="visible-xs visible-sm" role="presentation"><a role="menuitem" tabindex="-1" href="#" data-toggle="offcanvas"><span class="fa fa-filter"></span> {translate text='Refine search'}</a></li>
        <li role="presentation"><a role="menuitem" tabindex="-1" href="{$rssLink|escape}"><span class="fa fa-rss"></span> {translate text='RSS Feed'}</a></li>
        <li role="presentation"><a role="menuitem" tabindex="-1" href="{$rssLink|replace:'/Search/Results?':'/Widget/Carousel?preview=1&'|replace:'view=rss':'view=list'|escape}"><span class="fa fa-rss"></span> {translate text='Carousel'}</a></li>
        <li role="presentation"><a data-toggle="modal" data-target="#modal" role="menuitem" tabindex="-1" href="{$path}/Search/Email"><span class="fa fa-envelope-o"></span> {translate text='Email Search'}</a></li>
        <li role="presentation"><a role="menuitem" tabindex="-1" href="{$path}/MyResearch/SaveSearch?save={$searchId}"><span class="fa fa-save"></span> {translate text='save_search'}</a></li>
      </ul>
    </div>
    <div class="btn-group">
      {include file="Search/bookbag.tpl"}
    </div>
  </div>

  <div class="clearfix"></div>
</div>
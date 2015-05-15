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
        <li><a data-toggle="modal" data-target="#modal" role="menuitem" tabindex="-1" href="{$path}/BookBag/Email"><span class="icon-envelope-alt"></span> {translate text='Email Items'}</a></li>
        <li><a data-toggle="modal" data-target="#modal" role="menuitem" tabindex="-1" href="{$path}/BookBag/Save"><span class="icon-save"></span> {translate text='Save to My Account'}</a></li>
        <li class="divider"></li>
        <li><a class="empty-book-bag" href="{$path}/BookBag/Home?action=empty"><span class="icon-trash"></span> {translate text='Empty Book Bag'}</a></li>
      </ul>
    </div>
    <div class="btn-group">
      {include file="Search/bookbag.tpl"}
    </div>
  </div>

  <div class="clearfix"></div>
</div>
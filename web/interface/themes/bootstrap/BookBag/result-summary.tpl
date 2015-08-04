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
        <li role="presentation"><a data-toggle="modal" data-target="#modal" role="menuitem" tabindex="-1" href="{$path}/BookBag/Email"><span class="fa fa-envelope-o"></span> {translate text='Email Marked Items'}</a></li>
        <li role="presentation"><a data-toggle="modal" data-target="#modal" role="menuitem" tabindex="-1" href="{$path}/BookBag/Save"><span class="fa fa-save"></span> {translate text='Save to My Account'}</a></li>
        <li role="presentation"><a target="_blank" role="menuitem" tabindex="-1" href="{$path}/BookBag/Export?style=endnoteweb"><span class="fa fa-download"></span> {translate text='Export to Endnote'}</a></li>
        <li class="divider" role="presentation"></li>
        <li role="presentation"><a class="empty-book-bag" href="{$path}/BookBag/Home?action=empty"><span class="fa fa-trash"></span> {translate text='Clear All Marked Items'}</a></li>
      </ul>
    </div>
  </div>

  <div class="clearfix"></div>
</div>
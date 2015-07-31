<button type="button" class="btn btn-default btn-sm dropdown-toggle bookbag" data-toggle="dropdown"  data-count="{$cartContent|@count}" data-content="{if !empty($cartContent)}{$cartContent|@implode:' '}{/if}">
 <span class="badge {if !empty($cartContent)}bg-success{/if} bookbag-count">{$cartContent|@count}</span> {translate text='Marked'} <span class="caret"></span>
</button>
<ul class="dropdown-menu dropdown-menu-right" role="menu">
  <li role="presentation" {if empty($cartContent)}class="disabled"{/if}><a href="{$path}/BookBag/Home"><span class="fa fa-list-ol"></span> {translate text='View Marked Items'}</a></li>
  <li role="presentation" {if empty($cartContent)}class="disabled"{/if}><a data-toggle="modal" data-target="#modal" role="menuitem" tabindex="-1" href="{$path}/BookBag/Email"><span class="fa fa-envelope-o"></span> {translate text='Email Marked Items'}</a></li>
  <li role="presentation" {if empty($cartContent)}class="disabled"{/if}><a data-toggle="modal" data-target="#modal" role="menuitem" tabindex="-1" href="{$path}/BookBag/Save"><span class="fa fa-save"></span> {translate text='Save to My Account'}</a></li>
  <li role="presentation" class="divider"></li>
  <li role="presentation" {if empty($cartContent)}class="disabled"{/if}><a class="empty-book-bag" href="{$path}/BookBag/Home?action=empty"><span class="fa fa-trash-o"></span> {translate text='Clear All Marked Items'}</a></li>
</ul>

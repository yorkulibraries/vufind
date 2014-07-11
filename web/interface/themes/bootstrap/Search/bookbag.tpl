<button type="button" class="btn btn-default btn-sm dropdown-toggle bookbag" data-toggle="dropdown"  data-count="{$cartContent|@count}" data-content="{if !empty($cartContent)}{$cartContent|@implode:' '}{/if}">
  {translate text='Book Bag'} <span class="badge bg-{if empty($cartContent)}danger{else}success{/if} bookbag-count">{$cartContent|@count}</span> <span class="caret"></span>
</button>
<ul class="dropdown-menu dropdown-menu-right" role="menu">
  <li role="presentation" {if empty($cartContent)}class="disabled"{/if}><a href="{$path}/BookBag/Home"><span class="fa fa-list-ol"></span> {translate text='View Items'}</a></li>
  <li role="presentation" {if empty($cartContent)}class="disabled"{/if}><a role="menuitem" tabindex="-1" href="{$path}/BookBag/Export?style=refworks"><span class="fa fa-folder-open-o"></span> {translate text='Export to RefWorks'}</a></li>
  <li role="presentation" {if empty($cartContent)}class="disabled"{/if}><a data-toggle="modal" data-target="#modal" role="menuitem" tabindex="-1" href="{$path}/BookBag/Email"><span class="fa fa-envelope-o"></span> {translate text='Email Items'}</a></li>
  <li role="presentation" {if empty($cartContent)}class="disabled"{/if}><a data-toggle="modal" data-target="#modal" role="menuitem" tabindex="-1" href="{$path}/BookBag/Save"><span class="fa fa-save"></span> {translate text='Save to My Account'}</a></li>
  <li role="presentation" {if empty($cartContent)}class="disabled"{/if}><a role="menuitem" tabindex="-1" href="#"><span class="fa fa-pinterest"></span> {translate text='Pinterest'}</a></li>
  <li role="presentation" class="divider"></li>
  <li role="presentation" {if empty($cartContent)}class="disabled"{/if}><a class="empty-book-bag" href="{$path}/BookBag/Home?action=empty"><span class="fa fa-trash-o"></span> {translate text='Empty Book Bag'}</a></li>
</ul>

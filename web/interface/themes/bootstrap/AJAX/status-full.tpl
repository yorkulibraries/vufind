<div class="pull-right print-hidden">
  {if $allowHold}
  <div class="btn-group">
    <a href="{$path}/Record/{$requestRecordId}/Hold" class="btn btn-sm btn-primary btn-toggle" data-toggle="modal" data-target="#modal">{translate text='Place Hold'}</a>
  </div>
  {/if}
  {if $allowICB || $allowInProcess || $allowStorage}
  <div class="btn-group">
    <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-toggle="dropdown">
      {translate text='Request'} <span class="caret"></span>
    </button>
    <ul class="dropdown-menu dropdown-menu-right" role="menu">
      {if $allowICB}
      <li role="presentation"><a role="menuitem" tabindex="-1" data-toggle="modal" data-target="#modal" href="{$path}/Record/{$requestRecordId}/ICB">{translate text='Inter-campus Borrowing'}</a></li>
      {/if}
      {if $allowInProcess}
      <li role="presentation"><a role="menuitem" tabindex="-1" data-toggle="modal" data-target="#modal" href="{$path}/Record/{$requestRecordId}/InProcess">{translate text='In-Process/On-Order'}</a></li>
      {/if}
      {if $allowStorage}
      <li role="presentation"><a role="menuitem" tabindex="-1" data-toggle="modal" data-target="#modal" href="{$path}/Record/{$requestRecordId}/Storage">{translate text='Storage/Special Collections'}</a></li>
      {/if}
    </ul>
  </div>
  {/if}
</div>

<div class="clearfix"></div>

<div class="hidden-xs">
  {include file="RecordDrivers/Index/holdings-table.tpl"}
</div>
<button data-toggle="more-less" class="btn btn-default btn-xs hidden hidden-xs" data-target=".ajax-availability" data-target-name="ajax holdings"><span class="fa fa-plus"></span> <span class="more-less-label" data-alt="{translate text='Less'}">{translate text="More"}</span></button>

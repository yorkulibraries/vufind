{if $allowHold}
  <div class="btn-group request-buttons-container">
    <a data-toggle="modal" data-target="#modal" class="btn btn-primary btn-small" title="{translate text='Hold/Recall'}" href="{$path}/Record/{$id}/Hold">{translate text='Hold Request'}</a>
  </div>
{/if}
{if $allowICB}
  <div class="btn-group request-buttons-container">
    <a data-toggle="modal" data-target="#modal" class="btn btn-primary btn-small" title="{translate text='Inter-Campus Borrowing'}" href="{$path}/Record/{$id}/ICB">{translate text='ICB Request'}</a>
  </div>
{/if}
{if $allowInProcess}
  <div class="btn-group request-buttons-container">
    <a data-toggle="modal" data-target="#modal" class="btn btn-primary btn-small" title="{translate text='In-Process/On-Order'}" href="{$path}/Record/{$id}/InProcess">{translate text='InProcess Request'}</a>
  </div>
{/if}
{if $allowStorage}
  <div class="btn-group request-buttons-container">
    <a data-toggle="modal" data-target="#modal" class="btn btn-primary btn-small" title="{translate text='Request From Storage'}" href="{$path}/Record/{$id}/Storage">{translate text='Storage Request'}</a></span>
  </div>
{/if}
{include file=$holdingsMetadata}
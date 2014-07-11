<div class="modal-header">
  <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
  <h4 class="modal-title">
    {if $subTemplate=='login.tpl' || $pageTemplate=='login.tpl'}
      {translate text='Library User Authentication'}
    {else}
      {$pageTitle|translate}
    {/if}
  </h4>
</div>
<div class="modal-body">
  {if $error}
    <div class="alert alert-danger">
    {if $isFatal}
      {translate text="fatal_error_staff_notified"}
    {else}
      {$error->getMessage()}
    {/if}
    </div>
  {else}
    {if $subTemplate}
      {include file="$module/$subTemplate"}
    {else}
      {include file="$module/$pageTemplate"}
    {/if}
  {/if}
</div>

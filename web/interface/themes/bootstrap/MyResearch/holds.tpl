{if $user->cat_username}
  <h1>{translate text='Your Holds'}</h1>
    
  {if $holdResults.success}
      <div class="alert alert-success alert-dismissable">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        {translate text=$holdResults.status}
      </div>
  {/if}
    
  {if $cancelResults.count > 0}
      <div class="alert alert-success alert-dismissable">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        {$cancelResults.count|escape} {translate text="hold_cancel_success_items"}
      </div>
  {/if}
    
  {if $errorMsg}
      <div class="alert alert-danger alert-dismissable">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        {translate text=$errorMsg}
      </div>
  {/if}

  {if is_array($recordList)}
    <form role="form" action="{$path}/MyResearch/Holds" method="post">
      {if $cancelable}
      <div class="btn-group cancel-selected-holds">
        <input class="btn btn-default btn-sm cancel-hold-button" type="submit" name="cancelSelected" value="{translate text='hold_cancel_selected'}" />
      </div>
      <div class="btn-group cancel-all-holds">
        <input class="btn btn-default btn-sm cancel-hold-button" type="submit" name="cancelAll" value="{translate text='hold_cancel_all'}" />
      </div>
      {/if}
      
      <ul class="media-list result-list my-account-list">
        {foreach from=$recordListHTML item=resource name="recordLoop"}
          {$resource}
        {/foreach}
      </ul>      
      
      {if $cancelable}
      <div class="btn-group cancel-selected-holds">
        <input class="btn btn-default btn-sm cancel-hold-button" type="submit" name="cancelSelected" value="{translate text='hold_cancel_selected'}" />
      </div>
      <div class="btn-group cancel-all-holds">
        <input class="btn btn-default btn-sm cancel-hold-button" type="submit" name="cancelAll" value="{translate text='hold_cancel_all'}" />
      </div>
      {/if}
    </form>
    
  {else}
      {translate text='You do not have any holds or recalls placed'}.
  {/if}
{else}
  {include file="MyResearch/catalog-login.tpl"}
{/if}

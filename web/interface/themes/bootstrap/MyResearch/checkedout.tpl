{if $user->cat_username}
  {if $blocks}
    {foreach from=$blocks item=block}
      <div class="alert alert-info alert-dismissable">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        {translate text=$block}
      </div>
    {/foreach}
  {/if}
  
  {if $errorMsg}
    <div class="alert alert-danger alert-dismissable">
      <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
      {translate text=$errorMsg}
    </div>
  {/if}    

  <h1>{translate text='Checkouts'}: {$recordList|@count}</h1>
  {if !empty($recordList)}
    <form role="form" action="{$path}/MyResearch/CheckedOut" method="post">
      <div class="btn-group renew-selected-items">
        <input class="btn btn-default btn-sm renew-button" type="submit" name="renewSelected" value="{translate text='renew_selected'}" />
      </div>
      <div class="btn-group renew-all-items">
        <input class="btn btn-default btn-sm renew-button" type="submit" name="renewAll" value="{translate text='renew_all'}" />
      </div>
      <ul class="media-list result-list checkedout-list">
        {foreach from=$recordListHTML item=resource name="recordLoop"}
          {$resource}
        {/foreach}
      </ul>
      <div class="btn-group renew-selected-items">
        <input class="btn btn-default btn-sm renew-button" type="submit" name="renewSelected" value="{translate text='renew_selected'}" />
      </div>
      <div class="btn-group renew-all-items">
        <input class="btn btn-default btn-sm renew-button" type="submit" name="renewAll" value="{translate text='renew_all'}" />
      </div>
    </form>
  {else}
    <p>{translate text='You do not have any items checked out'}.</p>
  {/if}
{/if}

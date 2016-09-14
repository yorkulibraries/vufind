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

  <h1>{translate text='Checkouts'}</h1>
  
  {include file="MyResearch/fines-summary.tpl"}
  
  {if !empty($recordList)}
    <p>{'you_have_xxx_items_checked_out'|translate|replace:'###NUMBER###':$recordCount}.</p>
    
    <form {if !$renewForm}disabled="disabled"{/if} class="renewal-form long-wait" role="form" action="{$path}/MyResearch/CheckedOut" method="post">
      {if $renewForm}
      <div class="btn-group renew-selected-items">
        <input class="btn btn-default btn-sm renew-button" type="submit" name="renewSelected" value="{translate text='renew_selected'}" />
      </div>
      <div class="btn-group renew-all-items">
        <input class="btn btn-default btn-sm renew-button" type="submit" name="renewAll" value="{translate text='renew_all'}" />
      </div>
      {/if}
      <ul class="media-list result-list my-account-list">
        {foreach from=$recordListHTML item=resource name="recordLoop"}
          {$resource}
        {/foreach}
      </ul>
      {if $renewForm}
      <div class="btn-group renew-selected-items">
        <input class="btn btn-default btn-sm renew-button" type="submit" name="renewSelected" value="{translate text='renew_selected'}" />
      </div>
      <div class="btn-group renew-all-items">
        <input class="btn btn-default btn-sm renew-button" type="submit" name="renewAll" value="{translate text='renew_all'}" />
      </div>
      {/if}
    </form>
  {else}
    <p>{translate text='You do not have any items checked out'}.</p>
  {/if}
{/if}

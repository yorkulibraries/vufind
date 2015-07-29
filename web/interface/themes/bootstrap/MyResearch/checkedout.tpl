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

  <h1>{translate text='Checkouts'}: {$transList|@count}</h1>
  {if !empty($transList)}
    <form role="form" action="{$path}/MyResearch/CheckedOut" method="post">
      <div class="print-hidden pull-right">
        <div class="btn-group renew-selected-items">
          <input class="btn btn-default btn-sm renew-button" type="submit" name="renewSelected" value="{translate text='renew_selected'}" />
        </div>
        <div class="btn-group renew-all-items">
          <input class="btn btn-default btn-sm renew-button" type="submit" name="renewAll" value="{translate text='renew_all'}" />
        </div>
      </div>
      <div class="clearfix"></div>
      <ul class="media-list result-list">
        {foreach from=$transList item=resource}
          {$resource.html}
        {/foreach}
      </ul>
    </form>
    
  {else}
    <p>{translate text='You do not have any items checked out'}.</p>
  {/if}
{/if}

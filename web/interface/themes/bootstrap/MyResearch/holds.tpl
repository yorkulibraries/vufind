{if $user->cat_username}
  <h1>{translate text='Holds/Requests'}</h1>
  
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
  
  {include file="MyResearch/fines-summary.tpl"}

  {if is_array($recordList) && !empty($recordList)}
    <p>{'You have ###NUMBER### hold request(s)'|translate|replace:'###NUMBER###':$recordCount}.</p>
  
    <form class="cancel-hold-form" role="form" action="{$path}/MyResearch/Holds" method="post">
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
  {/if}
  
  {if is_array($unprocessedRequests) && !empty($unprocessedRequests)}
  <p>{translate text='The following requests have been received.'}</p>
  <table class="table table-striped table-condensed table-bordered">
    <caption class="sr-only">{translate text='Your Requests'}</caption>
    <thead>
      <tr>
        <th>{translate text="Request Date"}</th>
        <th>{translate text="Type"}</th>
        <th>{translate text="Call Number"}</th>
        <th>{translate text="Pickup"}</th>
        {* <th>{translate text="Expiry Date"}</th> *}
      </tr>
    </thead>
    <tbody class="rowlink">
    {foreach from=$unprocessedRequests item=request}
      <tr>
        <td>{$request->created}</a></td>
        <td>{$request->request_type}</td>
        <td><a class="rowlink"target="_blank" href="{$path}/Record/{$request->item_id}">{$request->item_callnum}</a></td>
        <td>{$request->pickup_location}</td>
        {* <td>{$request->expiry}</td> *}
      </tr>
    {/foreach}
    </tbody>
  </table>
  {/if}
  
  {if empty($recordList) && empty($unprocessedRequests)}
  <p>{translate text='You have no holds or requests.'}</p>
  {/if}
  
{else}
  {include file="MyResearch/catalog-login.tpl"}
{/if}

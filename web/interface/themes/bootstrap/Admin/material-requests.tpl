<h1>{translate text="Material Requests"}</h1>

<form class="form-inline" method="get" action="{$path}/Admin/MaterialRequests">
  <div class="form-group">
    
    <label for="emailedRequestsForm_type">{translate text="Request Type"}:</label>
    <select class="form-control" id="emailedRequestsForm_type" name="request_type">
      <option value="">{translate text="All"}</option>
      <option value="HOLD"{if $request_type=='HOLD'} selected="selected"{/if}>{translate text="Hold"}</option>
      <option value="ICB"{if $request_type=='ICB'} selected="selected"{/if}>{translate text="ICB"}</option>
      <option value="InProcess"{if $request_type=='InProcess'} selected="selected"{/if}>{translate text="In Process"}</option>
      <option value="Storage"{if $request_type=='Storage'} selected="selected"{/if}>{translate text="Storage"}</option>
    </select>

    <label for="emailedRequestsForm_from">{translate text="From"}:</label>
    <input class="form-control" id="emailedRequestsForm_from" type="text" name="from" value="{if $from}{$from|escape}{/if}" size="10" />
    
    <label for="emailedRequestsForm_to">{translate text="To"}:</label>
    <input class="form-control" id="emailedRequestsForm_to" type="text" name="to" value="{if $to}{$to|escape}{/if}" size="10" />
  
    <input type="submit" name="submit" value="{translate text='View'}"/>
  </div>
</form>

  <p>There were {$count} {$request_type|escape} request(s) from {$fromDateDisplay|escape} to {$toDateDisplay|escape}.</p>

{if !empty($requests)}
<table class="table table-striped table-condensed table-bordered">
<tr>
  <th>{translate text="Request Date"}</th>
  <th>{translate text="Type"}</th>
  <th>{translate text="Call Number"}</th>
  <th>{translate text="Pickup"}</th>
  <th>{translate text="Expiry Date"}</th>
  <th>{translate text="User ID"}</th>
  <th>{translate text="Hold Created"}</th>
</tr>
{foreach from=$requests item=request}
  <tr>
    <td>{$request->created}</a></td>
    <td>{$request->request_type}</td>
    <td><a target="_blank" href="{$path}/Record/{$request->item_id}">{$request->item_callnum}</a></td>
    <td>{$request->pickup_location}</td>
    <td>{$request->expiry}</td>
    <td>{$request->user_barcode}</td>
    <td>{if $request->ils_hold_created}{translate text="Yes"}{else}{translate text="No"}{/if}</td>
  </tr>
{/foreach}
</table>  
{/if}

{include file="Search/result-pager.tpl"}

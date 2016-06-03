<h1>{translate text="Online Payments"}</h1>

<form class="form-inline" method="get" action="{$path}/Admin/OnlinePayments">
  <div class="form-group">
    
    <label for="fines_group">{translate text="Library"}:</label>
    <select class="form-control" id="fines_group" name="fines_group">
      <option value="">{translate text="All"}</option>
      <option value="fines_group_YORK"{if $fines_group=='fines_group_YORK'} selected="selected"{/if}>{translate text="fines_group_YORK"}</option>
      <option value="fines_group_LAW"{if $fines_group=='fines_group_LAW'} selected="selected"{/if}>{translate text="fines_group_LAW"}</option>
    </select>
    
    <label for="payment_status">{translate text="Status"}:</label>
    <select class="form-control" id="payment_status" name="payment_status">
      <option value="">{translate text="All"}</option>
      <option value="CANCELLED"{if $payment_status=='CANCELLED'} selected="selected"{/if}>{translate text="CANCELLED"}</option>
      <option value="COMPLETE"{if $payment_status=='COMPLETE'} selected="selected"{/if}>{translate text="COMPLETE"}</option>
      <option value="APPROVED"{if $payment_status=='APPROVED'} selected="selected"{/if}>{translate text="APPROVED"}</option>
      <option value="INITIATED"{if $payment_status=='INITIATED'} selected="selected"{/if}>{translate text="INITIATED"}</option>
    </select>

    <label for="from">{translate text="From"}:</label>
    <input class="form-control" id="from" type="text" name="from" value="{if $from}{$from|escape}{/if}" size="10" />
    
    <label for="to">{translate text="To"}:</label>
    <input class="form-control" id="to" type="text" name="to" value="{if $to}{$to|escape}{/if}" size="10" />
  
    <input type="submit" name="submit" value="{translate text='View'}"/>
  </div>
</form>

<br>

<div class="alert alert-success" role="alert">
  <p>There were <strong>{$count}</strong> {$payment_status|escape} payment(s) from <strong>{$fromDateDisplay|escape}</strong> to <strong>{$toDateDisplay|escape}</strong>.</p>
  {if $payment_status == 'COMPLETE' || $payment_status == 'APPROVED'}
  <p>Total amount is: <strong>{$total|safe_money_format|escape}</strong></p>
  {/if}
</div>

{if !empty($payments)}
<table class="table table-striped table-condensed table-bordered">
<tbody class="rowlink">
<tr>
  <th>{translate text="Date"}</th>
  <th>{translate text="Amount"}</th>
  <th>{translate text="User ID"}</th>
  <th>{translate text="YPB Order ID"}</th>
  <th>{translate text="Authcode"}</th>
  <th>{translate text="Refnum"}</th>
  <th>{translate text="Receipt"}</th>
</tr>
{foreach from=$payments item=payment}
  <tr>
    <td>{$payment->payment_date}</td>
    <td class="text-right">{$payment->amount|safe_money_format|escape}</td>
    <td>{$payment->user_barcode}</td>
    <td>{$payment->ypborderid}</td>
    <td>{$payment->authcode}</td>
    <td>{$payment->refnum}</td>
    <td><a class="rowlink" target="_blank" href="{$receiptBaseURL}{$payment->tokenid}">{translate text="View"}</a></td>
  </tr>
{/foreach}
</tbody>
</table>  
{/if}

{include file="Search/result-pager.tpl"}

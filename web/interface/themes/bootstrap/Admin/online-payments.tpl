<h1>{translate text="Online Payments"}</h1>

<form class="form-inline" method="get" action="{$path}/Admin/OnlinePayments">
  <div class="form-group">
    
    <label for="fines_group">{translate text="Library"}:</label>
    <select class="form-control" id="fines_group" name="fines_group">
      <option value="">{translate text="All"}</option>
      {foreach from=$finesGroups item=fg}
      <option value="{$fg}"{if $fines_group==$fg} selected="selected"{/if}>{$fg|translate|escape}</option>
      {/foreach}
    </select>
    
    <label for="payment_status">{translate text="Status"}:</label>
    <select class="form-control" id="payment_status" name="payment_status">
      <option value="">{translate text="All"}</option>
      {foreach from=$paymentStatuses item=ps}
      <option value="{$ps}"{if $payment_status==$ps} selected="selected"{/if}>{$ps|translate|escape}</option>
      {/foreach}
    </select>

    <label for="from">{translate text="From"}:</label>
    <input size="10" data-date-language="{$userLang}" data-date-disable-touch-keyboard="true" data-provide="datepicker" placeholder="mm/dd/yyyy" data-date-format="mm/dd/yyyy"  data-date-autoclose="true" class="form-control" type="text" id="from" name="from" value="{if $from}{$from|escape}{/if}" />
    
    <label for="to">{translate text="To"}:</label>
    <input size="10" data-date-language="{$userLang}" data-date-disable-touch-keyboard="true" data-provide="datepicker" placeholder="mm/dd/yyyy" data-date-format="mm/dd/yyyy"  data-date-autoclose="true" class="form-control" type="text" id="to" name="to" value="{if $to}{$to|escape}{/if}" />
  
    <input type="submit" name="submit" value="{translate text='View'}"/>
  </div>
</form>

<br>

<div class="alert alert-success" role="alert">
  <p>There were <strong>{$count}</strong> {$payment_status|escape} payment(s) from <strong>{$fromDateDisplay|escape}</strong> to <strong>{$toDateDisplay|escape}</strong>.</p>
  
  <p>Total INITIATED transaction amount is: <strong>{$totalInitiated|safe_money_format|escape}</strong></p>
  <p>Total APPROVED (but not yet COMPLETE) transaction amount is: <strong>{$totalApproved|safe_money_format|escape}</strong></p>
  <p>Total COMPLETE transaction amount is: <strong>{$totalComplete|safe_money_format|escape}</strong></p>
  <p>Total CANCELLED transaction amount is: <strong>{$totalCancelled|safe_money_format|escape}</strong></p>
</div>

{if !empty($payments)}
<table class="table table-striped table-condensed table-bordered">
<tbody class="rowlink">
<tr>
  <th>{translate text="Date"}</th>
  <th>{translate text="Amount"}</th>
  <th>{translate text="User ID"}</th>
  <th>{translate text="Library"}</th>
  <th>{translate text="YPB Order ID"}</th>
  <th>{translate text="Receipt"}</th>
</tr>
{foreach from=$payments item=payment}
  <tr>
    <td>{$payment->payment_date}</td>
    <td class="text-right">{$payment->amount|safe_money_format|escape}</td>
    <td>{$payment->user_barcode}</td>
    <td>{$payment->fines_group|translate|escape}</td>
    <td>{$payment->ypborderid}</td>
    <td><a class="rowlink" target="_blank" href="{$receiptBaseURL}{$payment->tokenid}">{translate text="View"}</a></td>
  </tr>
{/foreach}
</tbody>
</table>  
{/if}

{include file="Search/result-pager.tpl"}

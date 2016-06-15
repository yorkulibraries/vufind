{if $user->cat_username}
  <h1>{translate text='Fines'}</h1>  
  
  {if !empty($paymentNotifications)}
  {foreach from=$paymentNotifications item=p}
    {if $p->payment_status == 'COMPLETE'}
    <div class="alert alert-success" role="alert">
      <p>{translate text='payment_complete_message'} <a target="_blank" class="alert-link" href="{$receiptBaseURL}{$p->tokenid}">{translate text='view_transaction_receipt'}</a>.</p>
    </div>
    {/if}
    {if $p->payment_status == 'APPROVED'}
    <div class="alert alert-success" role="alert">
      <p>{translate text='payment_approved_message'}</p>
    </div>
    {/if}
    {if $p->payment_status == 'PROCESSING'}
    <div class="alert alert-info" role="alert">
      <p>{translate text='payment_processing_message'}</p>
    </div>
    {/if}
    {if $p->payment_status == 'CANCELLED'}
    <div class="alert alert-danger" role="alert">
      <p>{translate text='payment_cancelled_message'}</p>
      {if !empty($p->status)}
        <p>{translate text='credit_card_auth_status'}: <strong>{$p->status|escape}</strong></p>
      {/if}
      {if !empty($p->message)}
        <p>{translate text='credit_card_auth_message'}: <strong>{$p->message|escape}</strong></p>
      {/if}
    </div>
    {/if}
    {if $p->payment_status == 'INITIATED'}
    <div class="alert alert-warning" role="alert">
      <p>{translate text='payment_initiated_message'}</p>
      <p>{translate text='payment_will_be_cancelled_message'} <a class="alert-link" href="{$paymentBaseURL}{$p->tokenid}">{translate text='complete_this_payment'}</a>.</p>
    </div>
    {/if}
  {/foreach}
  {/if}
  
  {if !empty($finesData)}
    {include file="MyResearch/fines-summary.tpl"}
    
    {foreach from=$finesData key=group item=groupData}
    {if !empty($groupData.items)}
    <h2>{translate text=$group}</h2>
    <div class="table-responsive">
      <table class="table table-condensed table-striped table-bordered table-hover">
      <caption class="sr-only">{translate text='Bills'}</caption>
      <thead>
      <tr>
        {if $showBillKey}
        <th>{translate text='ID'}</th>
        {/if}
        <th>{translate text='Date'}</th>
        {*<th>{translate text='Amount'}</th>*}
        <th>{translate text='Balance'}</th>
        <th>{translate text='Reason'}</th>
        <th>{translate text='Title'}</th>
      </tr>
      </thead>
      <tbody class="rowlink">
        {foreach from=$groupData.items item=record}
        <tr>
          {if $showBillKey}
          <td>{$record.bill_key|escape}</td>
          {/if}
          <td>{$record.date_billed|escape}</td>
          {*<td>{$record.amount|safe_money_format|escape}</td>*}
          <td>{$record.balance|safe_money_format|escape}</td>
          <td>{$record.fine|translate|escape}</td>
          <td>
            {if empty($record.title)}
              {translate text='not_applicable'}
            {else}
              <a class="rowlink" title="{$record.title|trim:'/:'|escape}" href="{$path}/Record/{$record.id|escape}">{$record.title|trim:'/:'|truncate:80:'...'|escape}</a>
            {/if}
          </td>
        </tr>
        {/foreach}
      </tbody>
      </table>
    </div>
    {/if}
    {/foreach}
  {else}
    <div class="alert alert-success" role="alert">
      <p>{translate text='You do not have any outstanding bills/fines'}.</p>
    </div>
  {/if}
  
  {if !empty($payments)}
    <h2>{translate text='Your Online Payments'}</h2>
    <p class="help-block">{translate text='Click on each item to view/print receipt.'}</p>
    <div class="table-responsive">
      <table class="table table-condensed table-striped table-bordered table-hover">
      <caption class="sr-only">{translate text='Your Online Payments'}</caption>
      <thead>
      <tr>
        <th>{translate text='ID'}</th>
        <th>{translate text='Date'}</th>
        <th>{translate text='Amount'}</th>
        <th>{translate text='Library'}</th>
        <th>{translate text='Status'}</th>
      </tr>
      </thead>
      <tbody class="rowlink">
        {foreach from=$payments item=p}
        <tr {if $p->payment_status=='COMPLETE' || $p->payment_status=='APPROVED'}class="success"{elseif $p->payment_status=='PROCESSING'}class="info"{elseif $p->payment_status=='CANCELLED'}class="danger"{elseif $p->payment_status=='INITIATED'}class="warning"{/if}>
          <td>{$p->id|escape}</td>
          <td>{$p->payment_date|strtotime|date_format:'%b %d, %Y'|escape}</td>
          <td>{$p->amount|safe_money_format|escape}</td>
          <td>{$p->fines_group|translate|escape}</td>
          <td><a class="rowlink" target="_blank" title="{translate text='view_transaction_receipt'}" href="{$receiptBaseURL}{$p->tokenid}">{$p->payment_status|escape}</a></td>
        </tr>
        {/foreach}
      </tbody>
      </table>
    </div>
  {/if}
{/if}

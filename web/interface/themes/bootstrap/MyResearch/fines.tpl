{if $user->cat_username}
  <h1>{translate text='Fines'}</h1>  
  
  {if !empty($paymentNotifications)}
  {foreach from=$paymentNotifications item=p}
    {if $p->payment_status == 'COMPLETE'}
    <div class="alert alert-success" role="alert">
      <p>{translate text='Your payment is complete.'} {translate text='Thank you!'}</strong>
    </div>
    {/if}
    {if $p->payment_status == 'APPROVED'}
    <div class="alert alert-success" role="alert">
      <p>{translate text='Your payment is approved.'} {translate text='Thank you!'}</strong>
    </div>
    {/if}
    {if $p->payment_status == 'PROCESSING'}
    <div class="alert alert-info" role="alert">
      <p>{translate text='Your payment is being processed.'}</strong>
    </div>
    {/if}
    {if $p->payment_status == 'CANCELLED'}
    <div class="alert alert-danger" role="alert">
      <p>{translate text='Your payment is cancelled.'}</strong>
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
      <strong>{translate text='You do not have any outstanding bills/fines'}.</strong>
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
        <tr {if $p->payment_status=='COMPLETE'}class="success"{elseif $p->payment_status=='APPROVED'}class="info"{elseif $p->payment_status=='CANCELLED'}class="danger"{/if}>
          <td>{$p->id|escape}</td>
          <td>{$p->payment_date|strtotime|date_format:'%b %d, %Y'|escape}</td>
          <td>{$p->amount|safe_money_format|escape}</td>
          <td>{$p->fines_group|translate|escape}</td>
          <td><a class="rowlink" target="_blank" title="{translate text='View Receipt'}" href="{$receiptBaseURL}{$p->tokenid}">{$p->payment_status|escape}</a></td>
        </tr>
        {/foreach}
      </tbody>
      </table>
    </div>
  {/if}
{/if}

{if $user->cat_username}
  <h1>{translate text='Fines'}</h1>  
  
  {if !empty($finesData)}
    {foreach from=$finesData key=group item=groupData}
      {if !empty($groupData.items)}
        {assign var=groupTotal value=$groupData.groupTotal|safe_money_format}
        {assign var=message value='you_owe_xxx_in_fines_to_library'|translate}
        {assign var=library value=$group|translate}
        <p class="text-danger">
          <strong>{$message|replace:'###NUMBER###':$groupTotal|replace:'###LIBRARY###':$library}</strong>
          <a class="btn btn-default" href="{$path}/MyResearch/PayFines?g={$group|escape}" role="button"><i class="fa fa-credit-card" aria-hidden="true"></i> {translate text='Pay Online'}</a>
        </p>
      {/if}
    {/foreach}
    
    {foreach from=$finesData key=group item=groupData}
    {if !empty($groupData.items)}
    <h2>{translate text=$group}</h2>
    <div class="table-responsive">
      <table class="table table-condensed table-striped table-bordered table-hover">
      <caption class="sr-only">{translate text='Bills'}</caption>
      <thead>
      <tr>
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
    <div class="table-responsive">
      <table class="table table-condensed table-striped table-bordered table-hover">
      <caption class="sr-only">{translate text='Your Online Payments'}</caption>
      <thead>
      <tr>
        <th>{translate text='Date'}</th>
        <th>{translate text='Amount'}</th>
        <th>{translate text='Library'}</th>
        <th>{translate text='Status'}</th>
      </tr>
      </thead>
      <tbody class="rowlink">
        {foreach from=$payments item=p}
        <tr>
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

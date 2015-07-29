{if $user->cat_username}
  <h1>{translate text='Fines'}</h1>
  
  {if $totalBalance > 0.0}
    {assign var=sTotalBalance value=$totalBalance|safe_money_format}
    <div class="alert alert-danger" role="alert">
      <strong>{translate text='You owe ###NUMBER### in bills/fines'|replace:'###NUMBER###':$sTotalBalance}.</strong>
    </div>
  {else}
    <div class="alert alert-success" role="alert">
      <strong>{translate text='You do not have any outstanding bills/fines'}.</strong>
    </div>
  {/if}
  
  {if !empty($rawFinesData)}
  <div class="table-responsive">
    <table class="table table-condensed">
    <caption class="sr-only">{translate text='Bills'}</caption>
    <thead>
    <tr>
      <th>{translate text='Date'}</th>
      <th>{translate text='Amount'}</th>
      <th>{translate text='Reason'}</th>
      <th>{translate text='Title'}</th>
    </tr>
    </thead>
    <tbody>
      {foreach from=$rawFinesData item=record}
      <tr>
        <td>{$record.date_billed|escape}</td>
        <td>{$record.amount|safe_money_format|escape}</td>
        <td>{$record.fine|translate|escape}</td>
        <td>
          {if empty($record.title)}
            {translate text='not_applicable'}
          {else}
            <a href="{$path}/Record/{$record.id|escape}">{$record.title|trim:'/:'|escape}</a>
          {/if}
        </td>
      </tr>
      {/foreach}
    </tbody>
    </table>
  </div>
  {/if}
{/if}

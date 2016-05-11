{if $user->cat_username}
  <h1>{translate text='Fines'}</h1>  
  
  {if !empty($finesData)}
    {foreach from=$finesData key=group item=groupData}
      {assign var=groupTotal value=$groupData.groupTotal|safe_money_format}
      {assign var=message value='you_owe_xxx_in_fines_to_library'|translate}
      {assign var=library value=$group|translate}
      <p class="text-danger"><strong>{$message|replace:'###NUMBER###':$groupTotal|replace:'###LIBRARY###':$library}</strong>
      <a class="btn btn-default" href="{$path}/MyResearch/PayFines?g={$group|escape}" role="button"><i class="fa fa-credit-card" aria-hidden="true"></i> {translate text='Pay Online'}</a></p>
    {/foreach}
    
    {foreach from=$finesData key=group item=groupData}
    <h2>{translate text=$group}</h2>
    <div class="table-responsive">
      <table class="table table-condensed">
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
      <tbody>
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
              <a href="{$path}/Record/{$record.id|escape}">{$record.title|trim:'/:'|escape}</a>
            {/if}
          </td>
        </tr>
        {/foreach}
      </tbody>
      </table>
    </div>
    {/foreach}
  {else}
    <div class="alert alert-success" role="alert">
      <strong>{translate text='You do not have any outstanding bills/fines'}.</strong>
    </div>
  {/if}
{/if}

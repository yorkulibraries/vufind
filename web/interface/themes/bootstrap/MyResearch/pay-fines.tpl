{if $user->cat_username}
  {assign var=heading value='pay_fines_to_library'|translate}
  {assign var=library value=$group|translate}
  <h1>{$heading|replace:'###LIBRARY###':$library}</h1>  

  {if !empty($items)}
    <form class="pay-fines-form" role="form" action="{$path}/MyResearch/PayFines" method="post">
      <div class="table-responsive">
        <table class="table table-condensed">
        <caption class="sr-only">{translate text='Bills'}</caption>
        <thead>
        <tr>
          <th>{translate text='Paying'}</th>
          <th class="text-right">{translate text='Balance'}</th>
          <th>{translate text='Reason'}</th>
          <th>{translate text='Title'}</th>
        </tr>
        </thead>
        <tbody>
          {foreach from=$items item=record}
          <tr>
            <td>
              <div class="checkbox">
                <label>
                  <input type="checkbox" {if $confirming}disabled{/if} checked="checked" name="{if $confirming}disabled{/if}selected[]" value="{$record.bill_key|escape}" aria-label="{translate text='Remove'}">
                </label>
              </div>
              {if $confirming}
                <input type="hidden" name="selected[]" value="{$record.bill_key|escape}">
              {/if}
            </td>
            <td class="text-right">{$record.balance|safe_money_format|escape}</td>
            <td>
              {$record.fine|translate|escape}
            </td>
            <td>
              {if empty($record.title)}
                {translate text='not_applicable'}
              {else}
                {$record.title|trim:'/:'|escape}
              {/if}
            </td>
            
          </tr>
          {/foreach}
          <tr class="pay-fines-total">
            <td class="text-right"><strong>{translate text='Total'}</strong></td>
            <td class="text-right"><strong>{$total|safe_money_format|escape}</strong></td>
            <td colspan="2">&nbsp;</td>
          </tr>
        </tbody>
        </table>
      </div>
      
      <div class="form-actions pull-left">
        {if !$confirming}
          <button class="btn btn-default btn-sm" type="submit" name="update" value="update"><i class="fa fa-calculator" aria-hidden="true"></i> {translate text='Update Total'}</button>
          <button class="btn btn-primary btn-sm" type="submit" name="confirm" value="confirm"><i class="fa fa-check" aria-hidden="true"></i> {translate text='Confirm'}</button>
        {else}
          <button class="btn btn-success btn-sm" type="submit" name="pay" value="pay"><i class="fa fa-credit-card" aria-hidden="true"></i> {translate text='Pay Now'}</button>
        {/if}
      </div>
      
      <input type="hidden" name="g" value="{$group|escape}">
      
      <div class="form-actions pull-right">
        <a class="btn btn-danger btn-sm" href="{$path}/MyResearch/Fines" role="button"><i class="fa fa-times" aria-hidden="true"></i> {translate text='Cancel'}</a>
      </div>
      
    </form>
  {else}
    <div class="alert alert-success" role="alert">
      <strong>{translate text='You do not have any outstanding bills/fines'}.</strong>
    </div>
  {/if}
{/if}

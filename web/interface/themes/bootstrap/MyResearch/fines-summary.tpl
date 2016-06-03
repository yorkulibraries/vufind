{if !empty($finesData)}
{foreach from=$finesData key=group item=groupData}
  {if !empty($groupData.items)}
    {assign var=groupTotal value=$groupData.groupTotal|safe_money_format}
    {assign var=message value='you_owe_xxx_in_fines_to_library'|translate}
    {assign var=library value=$group|translate}
    <p class="text-danger">
      <strong>{$message|replace:'###NUMBER###':$groupTotal|replace:'###LIBRARY###':$library}</strong>
      <a class="btn btn-success btn-sm" href="{$path}/MyResearch/PayFines?g={$group|escape}" role="button"><i class="fa fa-credit-card" aria-hidden="true"></i> {translate text='Pay Online'}</a>
    </p>
  {/if}
{/foreach}
{/if}
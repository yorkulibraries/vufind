<html lang="{$userLang}">
<![CDATA[
<script type="text/javascript">
  {literal}
    jQuery(document).ready(function() {
      jQuery('.yul-tabs>li').click(function(e) {
        e.preventDefault();
        jQuery('.yul-tab-content').addClass('hidden');
        jQuery(jQuery(this).attr('data-target')).removeClass('hidden');
        jQuery(this).addClass('menuSelected').siblings().removeClass('menuSelected');
      });
    });
  {/literal}
</script>
<style type="text/css">
  {literal}
  .yul-portlet>ul.yul-links {
    list-style-type: none;
    margin: 0;
    padding: 0;
  }
  span.yul-label {
    display: inline-block;
    padding: 2px 5px;
    border-radius: 5px;
    font-size: 12px;
  }
  span.yul-label-danger {
    background-color: #E31837;
    color: white;
  }
  span.yul-label-success {
    background-color: #449D44;
    color: white;
  }
  .yul-tab-content {
    border-top: 2px solid #999999;
    padding-top: 10px;
    margin-bottom: 20px;
  }
  .yul-tab-content .yul-table {
    width: 100%;
  }
  .yul-tab-content .yul-table td {
    padding: 2px 0;
  }
  .yul-tab-content .fines-group-heading {
    font-size: 14pt;
    margin-bottom: 0;
  }
  {/literal}
</style>
<div class="yul-portlet yul-my-library-account">
  <ul class="nav menu yul-tabs">
    <li data-target="#yul-checkouts" class="menuSelected"><a href="#yul-checkouts"><span>{translate text='Checkouts'}</span></a></li>
    <li data-target="#yul-holds"><a href="#yul-holds"><span>{translate text='Holds'}</span></a></li>
    <li data-target="#yul-fines"><a href="#yul-fines"><span>{translate text='Fines'}</span></a></li>
  </ul>
  <div id="yul-checkouts" class="yul-tab-content clear">
    {if  !empty($loans)}
      <h2>{translate text='Checkouts'}: {$loans|@count}</h2>
      <div class="yul-table-responsive">
        <table class="yul-table">
        <thead>
          <tr>
            <th>{translate text='Item'}</th>
            <th>{translate text='Due Date'}</th>
          </tr>
        </thead>
        <tbody>
          {foreach from=$loans item=item}
            <tr {if $item.overdue=='Y'}class="yul-overdue"{/if}>
              <td>
                {if $item.overdue=='Y'}
                  <span class="yul-label yul-label-danger">{translate text="Overdue"}</span>
                {/if}
                {$item.title|escape}</td>
              <td>
                {if !empty($item.recall_duedate)}{$item.recall_duedate|strtotime|date_format:'%b %d, %Y'}{else}{$item.duedate|strtotime|date_format:'%b %d, %Y'}{/if}
              </td>
            <tr>
          {/foreach}
        </tbody>
        </table>
      </div>
    {else}
      <p>{translate text='You do not have any items checked out'}.</p>
    {/if}
  </div>
  <div id="yul-holds" class="yul-tab-content clear hidden">
    {if  !empty($holds)}
      <h2>{translate text='Holds'}: {$holds|@count}</h2>
      <div class="yul-table-responsive">
        <table class="yul-table">
        <thead>
          <tr>
            <th>{translate text='Item'}</th>
            <th>{translate text='Held Until'}</th>
          </tr>
        </thead>
        <tbody>
          {foreach from=$holds item=item}
            <tr>
              <td>
                {if $item.ils_details.available=='Y'}<span class="yul-label yul-label-success">{translate text="Available"}</span>{/if}
                {$item.title|escape}
                {if $item.ils_details.comment}
                  <div>
                    {translate text='Note'}: {$item.ils_details.comment|escape}
                  </div>
                {/if}
              </td>
              <td>
                {if $item.ils_details.available=='Y'}
                  {$item.ils_details.date_available_expires|strtotime|date_format:'%b %d, %Y'}
                {else}
                  {translate text='Not Available'}
                {/if}
              </td>
            <tr>
          {/foreach}
        </tbody>
        </table>
      </div>
    {else}
      <p>{translate text='You do not have any holds or recalls placed'}.</p>
    {/if}
  </div>
  <div id="yul-fines" class="yul-tab-content clear hidden">
    {if  !empty($fines)}
      {foreach from=$fines key=group item=groupData}
        {if !empty($groupData.items)}
          {assign var=groupTotal value=$groupData.groupTotal|safe_money_format}
          {assign var=message value='you_owe_xxx_in_fines_to_library'|translate}
          {assign var=library value=$group|translate}
          <p>
            <span class="text-danger">{$message|replace:'###NUMBER###':$groupTotal|replace:'###LIBRARY###':$library}</span>
            <a target="_blank" href="{$url}/MyResearch/PayFines?g={$group|escape}">{translate text='Pay Online'}</a>
          </p>
        {/if}
      {/foreach}
      
      {foreach from=$fines key=group item=groupData}
        {if !empty($groupData.items)}
        <h2 class="fines-group-heading">{translate text=$group}</h2>
        <div class="yul-table-responsive">
          <table class="yul-table">
          <thead>
          <tr>
            <th>{translate text='Date'}</th>
            <th>{translate text='Balance'}</th>
            <th>{translate text='Reason'}</th>
            <th>{translate text='Title'}</th>
          </tr>
          </thead>
          <tbody class="rowlink">
            {foreach from=$groupData.items item=record}
            <tr>
              <td>{$record.date_billed|strtotime|date_format:'%b %d, %Y'}</td>
              <td>{$record.balance|safe_money_format|escape}</td>
              <td>{$record.fine|translate|escape}</td>
              <td>
                {if empty($record.title)}
                  {translate text='not_applicable'}
                {else}
                  <a target="_blank" title="{$record.title|trim:'/:'|escape}" href="{$url}/Record/{$record.id|escape}">{$record.title|trim:'/:'|truncate:80:'...'|escape}</a>
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
      <p>{translate text='You do not have any fines'}.</p>
    {/if}
  </div>
  <ul class="yul-links">
    <li><a target="_blank" href="http://www.library.yorku.ca/web/ask-services/borrow-renew-return/">{translate text='Borrow and Renew Items'} &gt;&gt;</a></li>
    <li><a target="_blank" href="http://www.library.yorku.ca/">{translate text='Library Homepage'} &gt;&gt;</a></li>
  </ul>
</div>
]]>
</html>
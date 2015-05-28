<table class="table table-condensed holdings">
{if empty($library)}
  <caption class="sr-only">{translate text="Location and Availability"}</caption>
{else}
  <caption>{translate text=$library}</caption>
{/if}
<thead>
<tr>
  <th class="hidden-xs">{translate text='Location'}</th>
  <th>{translate text='Call Number'}</th>
  <th>{translate text='Status'}</th>
  <th class="hidden-xs">{translate text='Holds'}</th>
  <th class="hidden-xs">{translate text='Material'}</th>
</tr>
</thead>
<tbody>
{foreach from=$statusItems item=item name="itemLoop"}
  {if $item.current_location && $item.item_type}
  <tr class="more-less">
    <td class="hidden-xs">
        {$item.current_location|replace:'- 204 Founders College':''|translate|escape}
    </td>
    <td>{$item.callnumber|escape}</td>
    <td>
      {if $item.availability}
        {if $item.recirculate_flag == 'N'}
     	 		<span class="checkedout">Non-circulating</span>
      	{else}
          <span class="available">{if $item.reserve=='Y' && $item.current_location != 'Osgoode Core Collection'}{translate text="On Reserve"} - {$item.circulation_rule|escape}{else}{translate text="Available"}{/if}</span>
        {/if}	
      {else}
        <span class="checkedout">{if $item.duedate}{translate text="Due"}: {$item.duedate|escape}{else}{translate text=$item.status}{/if}</span>
      {/if}
    </td>
    <td class="hidden-xs">
      {if $item.requests_placed}{$item.requests_placed|escape}{/if}
    </td>
    <td class="hidden-xs">
      {if $item.item_type}{$item.item_type|escape}{/if}
    </td>
  </tr>
  {/if}
{/foreach}
</tbody>
</table>

{if !empty($library) && !empty($statusItems)}
  <h4>{translate text=$library}</h4>
  {foreach from=$statusItems item=row name="summaryloop"}
    {if !empty($row.textual_holdings)}
      {if $row.marc852->getSubfield('z')}
        <p class="text-muted">{translate text='Note'}: {$row.marc852|getvalue:'z'|escape}</p>
      {/if}
      {foreach from=$row.textual_holdings item=textual}
      <p class="text-muted">{translate text='Holdings'}: {$row.location_code|escape} {$textual|escape}</p>
      {/foreach}
      {if !empty($row.notes)}
        {foreach from=$row.notes item=note name="notesLoop"}
          <p class="text-muted">{translate text='Note'}: {$note|escape}</p>
        {/foreach}
      {/if}
    {/if}
  {/foreach}
{/if}

<table class="table table-condensed holdings">
  <caption class="sr-only">{translate text="Location and Availability"}</caption>
<thead>
<tr>
  <th>{translate text='Location'}</th>
  <th>{translate text='Call Number'}</th>
  <th class="hidden-xs">{translate text='Status'}</th>
  <th class="hidden-xs">{translate text='Holds'}</th>
  <th class="hidden-xs">{translate text='Material'}</th>
</tr>
</thead>
<tbody>
{foreach from=$statusItems item=item name="itemLoop"}
  {if $item.current_location && $item.item_type}
  <tr class="more-less">
    <td>
      <div class="btn-group">
        <a title="{translate text='Locate this item'}" class="btn btn-default btn-xs print-hidden" href="{$path}/Record/{$item.id}/Location?location={$item.current_location|escape:'url'}&amp;location_code={$item.location_code|escape:'url'}&amp;callnumber={$item.callnumber|escape:'url'}" role="button"><i class="yul-map-icon"></i></a>
      </div>
      {assign var=curloc value=$item.current_location|replace:'- 204 Founders College':''}
      {if $subTemplate=='view-holdings.tpl'}
        {assign var=curloc value=$curloc|replace:'Osgoode':''}
        {assign var=curloc value=$curloc|replace:'Law':''}
        {assign var=curloc value=$curloc|replace:'Bronfman':''}
        {assign var=curloc value=$curloc|replace:'Scott':''}
        {assign var=curloc value=$curloc|replace:'Steacie':''}
        {assign var=curloc value=$curloc|replace:'Frost':''}
      {/if}
      {$curloc|translate|escape}
    </td>
    <td>
      <span class="hidden-xs">{$item.callnumber|escape}</span>
      
      {* display a "label" for in/out status on xs screens instead of a full status column *}
      {if $item.availability}
        {if $item.recirculate_flag == 'N'}
     	 		<span class="visible-xs text-warning">{$item.callnumber|escape}</span>
      	{else}
          <span class="visible-xs text-success">{$item.callnumber|escape}</span>
        {/if}	
      {else}
        <span class="visible-xs text-danger">{$item.callnumber|escape}</span>
      {/if}
    </td>
    <td class="hidden-xs">
      {if $item.availability}
        {if $item.recirculate_flag == 'N'}
     	 		<span class="checkedout">{translate text='Non-circulating'|escape}</span>
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

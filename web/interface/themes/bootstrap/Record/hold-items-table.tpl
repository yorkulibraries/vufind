{if !empty($eligibleItems.Glendon)}
<div class="panel panel-default">
  <div class="panel-heading">
    <div class="panel-title">{translate text='Items on Glendon Campus'}</div>
  </div>
  <div class="panel-body">  
    <table class="table table-condensed table-responsive">
      <caption class="sr-only">{translate text='Holdings details from'} {translate text=$location}</caption>
      <thead>
      <tr>
        <th>{translate text='Call Number'}</th>
        <th>{translate text='Material'}</th>
        <th>{translate text='Status'}</th>
      </tr>
      </thead>
      <tbody>
      {foreach from=$eligibleItems.Glendon item=row}
        {if $row.barcode != ""}
        <tr>
          <td>
            <div class="radio">
              <input type="radio" class="selectedItemForHold {$requestType} {$row.item_type} {$row.library_code} {$row.home_location_code} {$row.current_location_code}" name="copies[]" id="b{$row.barcode}" value="{$row.barcode|trim|escape}" {if in_array($row.barcode, $copies)}checked="checked"{/if}/>
              <label for="b{$row.barcode}">{$row.callnumber|escape}</label>
            </div>
          </td>
          <td>{$row.item_type|escape}</td>
          <td>
            {if $row.availability}
               <span class="available">{if $row.reserve=='Y'}{translate text="On Reserve"} - {$row.circulation_rule|escape}{else}{translate text="Available"}{/if}</span>
            {else}
              <span class="checkedout">{if $row.duedate}{translate text="Due"}: {$row.duedate|escape}{else}{translate text=$row.status}{/if}</span>
            {/if}
          </td>
        </tr>
        {/if}
      {/foreach}
      </tbody>
    </table>
  </div>
</div>
{/if}

{if !empty($eligibleItems.Keele)}
<div class="panel panel-default">
  <div class="panel-heading">
    <div class="panel-title">{translate text='Items on Keele Campus'}</div>
  </div>
  <div class="panel-body">
    <table class="table table-condensed table-responsive">
      <caption class="sr-only">{translate text='Holdings details from'} {translate text=$location}</caption>
      <thead>
      <tr>
        <th>{translate text='Call Number'}</th>
        <th>{translate text='Material'}</th>
        <th>{translate text='Status'}</th>
      </tr>
      </thead>
      <tbody>
      {foreach from=$eligibleItems.Keele item=row}
        {if $row.barcode != ""}
        <tr>
          <td>
            <div class="radio">
              <input type="radio" class="selectedItemForHold {$requestType} {$row.item_type} {$row.library_code} {$row.home_location_code} {$row.current_location_code}" name="copies[]" id="b{$row.barcode}" value="{$row.barcode|trim|escape}" {if in_array($row.barcode, $copies)}checked="checked"{/if}/>
              <label for="b{$row.barcode}">{$row.callnumber|escape}</label>
            </div>
          </td>
          <td>{$row.item_type|escape}</td>
          <td>
            {if $row.availability}
              {if $row.recirculate_flag == 'N'}
         	 		  <span class="checkedout">Non-circulating</span>
         	 		{else}
               <span class="available">{if $row.reserve=='Y'}{translate text="On Reserve"} - {$row.circulation_rule|escape}{else}{translate text="Available"}{/if}</span>
              {/if}
            {else}
              <span class="checkedout">{if $row.duedate}{translate text="Due"}: {$row.duedate|escape}{else}{translate text=$row.status}{/if}</span>
            {/if}
          </td>
        </tr>
        {/if}
      {/foreach}
      </tbody>
    </table>
  </div>
</div>
{/if}

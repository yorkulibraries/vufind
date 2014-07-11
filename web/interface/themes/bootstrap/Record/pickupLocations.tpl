{if count($pickup) > 0}
  <label class="col-sm-3 control-label" for="pickUpLocation">{translate text='pick_up_location'}</label>
  <div class="col-sm-9">
    <select id="pickUpLocation" name="pickUpLocation" class="form-control">
      <option value="" {if empty($pickUpLocation)} selected="selected"{/if}></option>
      {foreach from=$pickup item="lib"}
        <option value="{$lib.locationID|escape}" {if $pickUpLocation == $lib.locationID}selected="selected"{/if}>{$lib.locationDisplay|escape}</option>
      {/foreach}
    </select>
  </div>
{/if}
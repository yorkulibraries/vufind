<form data-json="{$path}/AJAX/JSON?method=smsRecord" class="form-horizontal" role="form" action="{$path}/Record/{$id}/SMS" method="post">
  <input type="hidden" name="id" value="{$id}" />
  
  <div class="alert-container">
    {if $errorMsg}
    <div class="alert alert-danger alert-dismissable">
      <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
      {if $error}{$errorMsg|translate}{/if}
    </div>
    {/if}
    {if $infoMsg}
    <div class="alert alert-info alert-dismissable">
      <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
      {if $error}{$infoMsg|translate}{/if}
    </div>
    {/if}
  </div>
  <div class="form-group">
    <label class="col-sm-3 control-label" for="sms_to">{translate text='Number'}</label>
    <div class="col-sm-9">
      <input type="number" class="form-control" name="to" id="sms_to" value="{$to|escape}" />
    </div>
  </div>
  <div class="form-group">
    <label class="col-sm-3 control-label" for="email_from">{translate text='Provider'}</label>
    <div class="col-sm-9">
      <select id="sms_provider" name="provider" class="form-control">
        <option {if !$provider}selected="selected"{/if} value="">{translate text="Select your carrier"}</option>
        {foreach from=$carriers key=val item=details}
          <option value="{$val}" {if $provider==$val}selected="selected"{/if}>{$details.name|escape}</option>
        {/foreach}
      </select>
    </div>
  </div>
  <div class="form-group">
    <div class="col-sm-offset-3 col-sm-9">
      <input type="submit" class="btn btn-default" data-dismiss="modal" name="cancel" value="{translate text='Cancel'}" />
      <input type="submit" class="btn btn-primary" name="submit" value="{translate text='Save'}" />
    </div>
  </div>
</form>


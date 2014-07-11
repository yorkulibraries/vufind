<form data-json="{$path}/AJAX/JSON?method=emailSearch" class="form-horizontal" role="form" action="{$path}/Search/Email" method="post">
  <input type="hidden" name="url" value="{$searchURL|escape}" />
  
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
    <label class="col-sm-3 control-label" for="email_to">{translate text='To'}</label>
    <div class="col-sm-9">
      <input type="email" class="form-control" name="to" id="email_to" value="{$to|escape}" />
    </div>
  </div>
  <div class="form-group">
    <label class="col-sm-3 control-label" for="email_from">{translate text='From'}</label>
    <div class="col-sm-9">
      <input type="text" class="form-control" name="from_disabled" id="email_from" value="&quot;{$user->firstname} {$user->lastname}&quot; &lt;{$from|escape}&gt;" disabled="disabled"/>
      <input type="hidden" name="from" value="&quot;{$user->firstname} {$user->lastname}&quot; &lt;{$from|escape}&gt;" />
    </div>
  </div>
  <div class="form-group">
    <label class="col-sm-3 control-label" for="email_message">{translate text='Message'}</label>
    <div class="col-sm-9">
      <textarea class="form-control" id="email_message" name="message" rows="3"></textarea>
    </div>
  </div>
  <div class="form-group">
    <div class="col-sm-offset-3 col-sm-9">
      <input type="submit" name="cancel" value="{translate text='Cancel'}" class="btn btn-default" data-dismiss="modal" />
      <input type="submit" name="submit" value="{translate text='Send'}" class="btn btn-primary" />
    </div>
  </div>
</form>


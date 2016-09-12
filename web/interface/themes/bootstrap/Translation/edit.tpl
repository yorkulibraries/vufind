<h1>{'Edit Translation'|translate|escape}</h1>

<form class="form-horizontal" role="form" action="{$path}/Translation/Edit" method="post">
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
    <label class="col-sm-3 control-label" for="key">{translate text='Language'}</label>
    <div class="col-sm-9">
      <input type="text" class="form-control" name="lang" id="lang" value="{$lang|escape}" disabled="disabled" />
      <input type="hidden" name="lang" value="{$lang|escape}" />
    </div>
  </div>
  <div class="form-group">
    <label class="col-sm-3 control-label" for="key">{translate text='Key'}</label>
    <div class="col-sm-9">
      <input type="text" class="form-control" name="key" id="key" value="{$key|escape}" disabled="disabled" />
      <input type="hidden" name="key" value="{$key|escape}" />
    </div>
  </div>
  <div class="form-group">
    <label class="col-sm-3 control-label" for="value">{translate text='Value'}</label>
    <div class="col-sm-9">
      <textarea class="form-control" id="value" name="value" rows="3">{$value|escape}</textarea>
    </div>
  </div>
  <div class="form-group">
    <div class="col-sm-offset-3 col-sm-9">
      <input type="submit" name="cancel" value="{translate text='Cancel'}" class="btn btn-default" />
      <input type="submit" name="save" value="{translate text='Save'}" class="btn btn-primary" />
    </div>
  </div>
</form>


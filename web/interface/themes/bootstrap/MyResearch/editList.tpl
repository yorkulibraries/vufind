<form class="form-horizontal" role="form" action="{$path}/MyResearch/EditList/{$list->id}" method="post">  
  <div class="alert-container">
    {if $infoMsg}
    <div class="alert alert-info alert-dismissable">
      <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
      {if $infoMsg}{$message|translate}{/if}
    </div>
    {/if}
    {if $errorMsg}
    <div class="alert alert-danger alert-dismissable">
      <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
      {if $errorMsg}{$error|translate}{/if}
    </div>
    {/if}
  </div>
  
  <div class="form-group">
    <label class="col-sm-3 control-label" for="list_title">{translate text='List'}</label>
    <div class="col-sm-9">
      <input type="text" class="form-control" name="title" id="list_title" value="{$list->title|escape}"/>
    </div>
  </div>
  <div class="form-group">
    <label class="col-sm-3 control-label" for="list_desc">{translate text='Description'}</label>
    <div class="col-sm-9">
      <textarea class="form-control" id="list_desc" name="desc" rows="3">{$list->description|escape}</textarea>
    </div>
  </div>
  <div class="form-group">
    <div class="col-sm-offset-3 col-sm-9">
      <fieldset>
        <legend>{translate text="Access"}:</legend>
      <div class="radio">
        <input id="list_public_1" type="radio" name="public" value="1" {if $list->public == 1}checked="checked"{/if}/> <label for="list_public_1">{translate text="Public"}</label>
      </div>
      <div class="radio">
        <input id="list_public_0" type="radio" name="public" value="0" {if $list->public == 0}checked="checked"{/if}/> <label for="list_public_0">{translate text="Private"}</label>
      </div>
      </fieldset>
    </div>
  </div>
  <div class="form-group">
    <div class="col-sm-offset-3 col-sm-9">
      <input type="submit" class="btn btn-default" data-dismiss="modal" name="cancel" value="{translate text='Cancel'}" />
      <input type="submit" class="btn btn-primary" name="submit" value="{translate text='Save'}" />
    </div>
  </div>
</form>


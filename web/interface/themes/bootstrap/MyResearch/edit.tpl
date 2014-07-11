{if empty($savedData)}
  {if isset($listFilter)}
    <div class="alert alert-danger">
      {translate text='The record you selected is not part of the selected list.'}
    </div>
  {else}
    <div class="alert alert-danger">
      {translate text='The record you selected is not part of any of your lists.'}
    </div>
  {/if}
{else}
  <form class="form-horizontal" role="form" action="{$path}/MyResearch/Edit" method="post">
    {if $recordId}<input type="hidden" name="id" value="{$recordId}" />{/if}
    {if $listFilter}<input type="hidden" name="list_id" value="{$listFilter}" />{/if}
  
    <div class="alert-container">
      {if $errorMsg}
      <div class="alert alert-danger alert-dismissable">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        {if $errorMsg}{$errorMsg|translate}{/if}
      </div>
      {/if}
    </div>
  
    {foreach from=$savedData item="current"}
    <input type="hidden" name="lists[]" value="{$current.listId}"/>
    <fieldset>
      <legend>{translate text='List'}: {$current.listTitle|escape}</legend>
      <div class="form-group">
        <label class="col-sm-3 control-label" for="edit_tags{$current.listId}">{translate text='Tags'}</label>
        <div class="col-sm-9">
          <input type="text" class="form-control" name="tags{$current.listId}" id="edit_tags{$current.listId}" value="{$current.tags|escape}" />
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-3 control-label" for="edit_notes{$current.listId}">{translate text='Notes'}</label>
        <div class="col-sm-9">
          <textarea class="form-control" id="edit_notes{$current.listId}" name="notes{$current.listId}" rows="3">{$current.notes|escape}</textarea>
        </div>
      </div>
    </fieldset>
    {/foreach}
    
    <div class="form-group">
      <div class="col-sm-offset-3 col-sm-9">
        <input type="submit" class="btn btn-default" data-dismiss="modal" name="cancel" value="{translate text='Cancel'}" />
        <input type="submit" class="btn btn-primary" name="submit" value="{translate text='Save'}" />
      </div>
    </div>
  </form>
{/if}

<form data-json="{$path}/AJAX/JSON?method=saveRecord&amp;id={$id}" class="form-horizontal" role="form" action="{$path}/Record/{$id}/Save" method="post">  
  <div class="alert-container">
    {if $error}
    <div class="alert alert-danger alert-dismissable">
      <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
      {if $error}{$error|translate}{/if}
    </div>
    {/if}

    {if !empty($containingLists)}
    <div class="alert alert-warning alert-dismissable">
      <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
      {translate text='This item is already part of the following list/lists'}:
      <ul>
      {foreach from=$containingLists item="list"}
        <li>{$list.title|escape}</li>
      {/foreach}
      </ul>
    </div>
    {/if}
  </div>
  
  {* Only display the list drop-down if the user has lists that do not contain this item 
    Otherwise, display a text box so the user can create a new list *}
  {if (!empty($nonContainingLists)) }
  <div class="form-group">
    <label class="col-sm-3 control-label" for="save_list">{translate text='Choose a List'}</label>
    <div class="col-sm-9">
      <select id="save_list" name="list" class="form-control">
        {foreach from=$nonContainingLists item="list" name=listloop}
          <option value="{$list.id}"{if $list.id==$lastListUsed || (empty($lastListUsed) && $smarty.foreach.listloop.index==0)} selected="selected"{/if}>{$list.title|escape}</option>
        {/foreach}
      </select>
      <span class="help-block">{translate text='Choose an existing list or enter a new list name below'}</span>
    </div>
  </div>
  {/if}
  <div class="form-group">
    <label class="col-sm-3 control-label" for="add_list">{translate text='New List Name'}</label>
    <div class="col-sm-9">
      <input type="text" class="form-control" id="add_list" name="listname" value="{if empty($nonContainingLists)}Untitled List{/if}"/>
      <span class="help-block">{translate text='A new list will be created with this name'}</span>
    </div>
  </div>
  <div class="form-group">
    <label class="col-sm-3 control-label" for="add_mytags">{translate text='Add Tags'}</label>
    <div class="col-sm-9">
      <input type="text" class="form-control" name="mytags" id="add_mytags" />
      <span class="help-block">{translate text='Use double quotes for multi-word tags. Example: &quot;My Tag&quot;'}</span>
    </div>
  </div>
  <div class="form-group">
    <label class="col-sm-3 control-label" for="add_notes">{translate text='Add a Note'}</label>
    <div class="col-sm-9">
      <textarea class="form-control" id="add_notes" name="notes" rows="3"></textarea>
    </div>
  </div>
  <div class="form-group">
    <div class="col-sm-offset-3 col-sm-9">
      <input type="submit" class="btn btn-default" data-dismiss="modal" name="cancel" value="{translate text='Cancel'}" />
      <input type="submit" class="btn btn-primary" name="submit" value="{translate text='Save'}" />
    </div>
  </div>
</form>


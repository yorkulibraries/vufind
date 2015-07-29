{if $user->cat_username}  
  <form data-json="{$path}/AJAX/JSON?method=placeRequest" class="form-horizontal" role="form" action="{$path}/Record/{$id}/{$action}" method="post">
    <input type="hidden" name="id" value="{$id}" />
    <input type="hidden" name="requestType" value="{$action}" />
    
    <div class="alert-container">
      {* This will always be an error as successes get redirected to MyResearch/Holds.tpl *}
      {if $results.status}
        <div class="alert alert-danger">{translate text=$results.status}</div>
      {/if}
      {if $results.sysMessage}
        <div class="alert alert-danger">{translate text=$results.sysMessage}</div>
      {/if}

      {* errors *}
      {if !empty($errors)}
        {foreach from=$errors item=error}
          <div class="alert alert-danger">{translate text=$error}</div>
        {/foreach}
      {/if}

      {* form validation errors *}
      {if !empty($validationErrors)}
        {foreach from=$validationErrors item=error key=field}
          <div class="alert alert-danger">{translate text=$error}</div>
        {/foreach}
      {/if}
    </div>
    
    {if !$requestNotAllowed}  
      {include file="Record/hold-items-table.tpl"}
      
      {if in_array($action, array('ICB', 'InProcess', 'Storage'))}
      <div class="form-group">
        <label class="col-sm-3 control-label" for="comment">{translate text='Comments'}</label>
        <div class="col-sm-9">
          <textarea class="form-control" id="comment" name="comment" rows="3"></textarea>
        </div>
      </div>
      {/if}
      
      <div class="form-group">
        <label class="col-sm-3 control-label" for="requiredBy">{translate text='hold_required_by'}</label>
        <div class="col-sm-9">
          <div class="input-group">
            <span class="input-group-addon">
                <span class="fa fa-calendar"></span>
            </span>
            <input data-date-language="{$userLang}" data-date-disable-touch-keyboard="true" data-provide="datepicker" placeholder="mm/dd/yyyy" data-date-format="mm/dd/yyyy" data-date-start-date="+1d" data-date-end-date="+100d" data-date-autoclose="true" class="form-control" type="text" id="requiredBy" name="requiredBy" value="{if $requiredBy}{$requiredBy|escape}{/if}" />
          </div>
        </div>
      </div>

      <div class="form-group" id="pickupLocationsContainer">
        {include file="Record/pickupLocations.tpl"}
      </div>
    {/if}
    <div class="form-group">
      <div class="col-sm-offset-3 col-sm-9">
        <input type="submit" class="btn btn-default" data-dismiss="modal" name="cancel" value="{translate text='Cancel'}" />
        <input type="submit" class="btn btn-primary" name="submit" value="{translate text='request_submit_text'}" />
      </div>
    </div>
  </form>
{else}
  {include file="MyResearch/catalog-login.tpl"}
{/if}

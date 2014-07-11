{if $user->cat_username}
  <h1>{translate text='Your Holds'}</h1>
    
  {if $holdResults.success}
      <div class="alert alert-success alert-dismissable">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        {translate text=$holdResults.status}
      </div>
  {/if}
    
  {if $cancelResults.count > 0}
      <div class="alert alert-success alert-dismissable">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        {$cancelResults.count|escape} {translate text="hold_cancel_success_items"}
      </div>
  {/if}
    
  {if $errorMsg}
      <div class="alert alert-danger alert-dismissable">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        {translate text=$errorMsg}
      </div>
  {/if}

  {if is_array($recordList)}
    <form role="form" action="{$path}/MyResearch/Holds" method="post">
      {if $cancelable}
      <div class="btn-group cancel-selected-holds">
        <input class="btn btn-danger btn-sm cancel-hold-button" type="submit" name="cancelSelected" value="{translate text='hold_cancel_selected'}" />
      </div>
      <div class="btn-group cancel-all-holds">
        <input class="btn btn-danger btn-sm cancel-hold-button" type="submit" name="cancelAll" value="{translate text='hold_cancel_all'}" />
      </div>
      {/if}
      {foreach from=$recordList item=resource}
        {if $resource.ils_details.cancel_details && !$resource.ils_details.available}
          <input type="hidden" name="cancelAllIDS[]" value="{$resource.ils_details.cancel_details|escape}" />
        {/if} 
        <div class="panel panel-{if $resource.ils_details.available}success{else}default{/if}">
          <div class="panel-heading">
            <h3 class="panel-title">
              <a title="{$resource.title|escape}" href="{$url}/Record/{$resource.id}">
      	      {$resource.title|escape}
      	      </a>
      	    </h3>
          </div>
          <div class="panel-body">
            <div class="row">
              {if $coversOnLeft}
              <div class="col-sm-2 col-md-2 col-lg-2 bookcover hidden-xs">
            	  <a title="{$resource.title|escape}" href="{$url}/Record/{$resource.id}">
              	  <img class="img-responsive" src="{$path}/bookcover.php?isn={$resource.isbn|@formatISBN}&amp;id={$resource.id}&amp;size=small" alt="{translate text='Cover Image'}"/>
              	</a>
              </div>
              {/if}
              <div class="col-lg-10">
                <dl class="dl-horizontal">
                  <dt>{translate text='Created'}:</dt>
                  <dd>{$resource.ils_details.create|escape}</dd>

                  <dt>{translate text='Expires'}:</dt>
                  <dd>{$resource.ils_details.expire|escape}</dd>

                  <dt>{translate text='Available'}:</dt>
                  <dd>
                    {if $resource.ils_details.available}
                      <span class="available">{translate text='hold_available'}</span>
                      {if $resource.ils_details.date_available_expires}
                        {$resource.ils_details.date_available_expires}
                      {/if}
                    {else}
                      {translate text='Not Available'}
                    {/if}
                  </dd>

                  <dt>{translate text='Note'}:</dt>
                  <dd>{$resource.ils_details.comment|replace:'VuFind - Pickup:':'Pick up @'|escape}</dd>
                </dl>
                {if !$resource.ils_details.available}
                <div class="select-item-container">
                  <div class="btn btn-default btn-xs">
                    <input type="checkbox" name="cancelSelectedIDS[]" value="{$resource.ils_details.cancel_details}" id="checkbox_{$resource.id|regex_replace:'/[^a-z0-9]/':''|escape}" />
                    <label for="checkbox_{$resource.id|regex_replace:'/[^a-z0-9]/':''|escape}">{translate text='Select This Item to Cancel Hold'}</label>
                  </div>
                </div>
                {/if}
              </div>
              {if !$coversOnLeft}
              <div class="col-sm-2 col-md-2 col-lg-2 bookcover hidden-xs">
            	  <a title="{$resource.title|escape}" href="{$url}/Record/{$resource.id}">
              	  <img class="img-responsive" src="{$path}/bookcover.php?isn={$resource.isbn|@formatISBN}&amp;id={$resource.id}&amp;size=small" alt="{translate text='Cover Image'}"/>
              	</a>
              </div>
              {/if}
            </div>
          </div>
        </div>
      {/foreach}
      {if $cancelable}
      <div class="btn-group cancel-selected-holds">
        <input class="btn btn-danger btn-sm cancel-hold-button" type="submit" name="cancelSelected" value="{translate text='hold_cancel_selected'}" />
      </div>
      <div class="btn-group cancel-all-holds">
        <input class="btn btn-danger btn-sm cancel-hold-button" type="submit" name="cancelAll" value="{translate text='hold_cancel_all'}" />
      </div>
      {/if}
    </form>
    
  {else}
      {translate text='You do not have any holds or recalls placed'}.
  {/if}
{else}
  {include file="MyResearch/catalog-login.tpl"}
{/if}

{if !$modal}
<div class="center-block normal-login-form">
{/if}
  <form {if $modal}data-json="{$path}/AJAX/JSON?method=altLogin"{/if} role="form" class="login-form" method="post" action="{$path}/MyResearch/{if $loginAction}{$loginAction}{else}Home{/if}">
    {if !$modal}
      <h1>{translate text='Library User Authentication'}</h1>
    {/if}
    <div class="alert-container">
    {if $message || $error}
      <div class="alert alert-danger alert-dismissable">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        {if $message}<div>{$message|translate}</div>{/if}
        {if $error}<div>{$error|translate}</div>{/if}
      </div>
    {/if}
    </div>

    <div class="form-group">
      <label class="sr-only" for="loginUsername">{translate text='Username'}</label>
      <input autofocus="autofocus" class="form-control" type="text" name="username" id="loginUsername" placeholder="{translate text='Username or Barcode'}" />
    </div>
    <div class="form-group">
      <label class="sr-only" for="loginPassword">{translate text='Password'}</label>
      <input class="form-control" type="password" name="password" id="loginPassword" placeholder="{translate text='Password'}" />
    </div>
    <div class="form-group">
      <input type="submit" class="btn btn-primary" name="submit" value="{translate text='Login'}" />
    </div>
    {if $followup}<input type="hidden" name="followup" value="{$followup}"/>{/if}
    {if $followupModule}<input type="hidden" name="followupModule" value="{$followupModule}"/>{/if}
    {if $followupAction}<input type="hidden" name="followupAction" value="{$followupAction}"/>{/if}
    {if $recordId}<input type="hidden" name="recordId" value="{$recordId|escape}"/>{/if}
    {if $extraParams}
      {foreach from=$extraParams item=item}
        <input type="hidden" name="extraParams[]" value="{$item.name|escape}|{$item.value|escape}" />
      {/foreach}
    {/if}
    {if $followupURL}<input type="hidden" name="followupURL" value="{$followupURL|escape}"/>{/if}
    {if $followupQueryString}<input type="hidden" name="followupQueryString" value="{$followupQueryString|escape}"/>{/if}
  </form>

{if !$modal}
</div>
{/if}

<p class="login-question"><a href="http://www.library.yorku.ca/web/ask-services/borrow-renew-return/your-library-card/">{translate text='New user'}?</a></p>
<p class="login-question"><a href="{$path}/Help/Home?topic=login">{translate text='Locked account'}?</a></p>
<p class="login-question"><a href="{$path}/Help/Home?topic=login">{translate text='Forgot your password'}?</a></p>

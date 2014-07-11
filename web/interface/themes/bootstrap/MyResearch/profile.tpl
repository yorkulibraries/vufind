{if $user->cat_username}
  <h1>{translate text='Your Profile'}</h1>
  {if $userMsg}
    <div class="alert alert-success">{translate text=$userMsg}</div>
  {/if}
  
  <dl class="dl-horizontal">
    <dt>{translate text='First Name'}:</dt>
    <dd>{$profile.firstname|escape}</dd>
    <dt>{translate text='Last Name'}:</dt>
    <dd>{$profile.lastname|escape}</dd>
    {if $profile.email}
      <dt>{translate text='Email'}:</dt>
      <dd>{$profile.email|escape}</dd>
    {/if}
    <dt>{translate text='Address'}:</dt>
    <dd>{$profile.address1|escape}</dd>
    <dt>{translate text='Postal Code'}:</dt>
    <dd>{$profile.zip|escape}</dd>
    <dt>{translate text='Phone Number'}:</dt>
    <dd>{$profile.phone|escape}</dd>
    <dt>{translate text='Profile'}:</dt>
    <dd>{$profile.group|escape}</dd>
  </dl>
{else}
    {include file="MyResearch/catalog-login.tpl"}
{/if}

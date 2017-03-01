<div class="container">
  {if $message}
    <div class="alert alert-danger">{translate text=$message|escape}</div>
  {else}
    <h1>Vividata</h1>
    {foreach from=$links item=linkLabel key=targetURL}
      <p><a href="{$targetURL|escape}">Click to continue to <strong>{$linkLabel|escape}</strong></a>.</p>
    {/foreach}
  {/if}
</div>

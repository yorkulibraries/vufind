<div class="container">
  {if $message}
    <div class="alert alert-danger">{translate text=$message|escape}</div>
    {if $displayTermsOfUse}
      {include file="MyResearch/$tou.$userLang.tpl"}
    {/if}
  {else}
    {include file="MyResearch/$tou.$userLang.tpl"}
    <form role="form" method="post" action="{$url}/MyResearch/{$action}{if $queryString}?{$queryString}{/if}">
      <div class="form-group">
        <input autofocus class="btn btn-primary" type="submit" name="agree" value="{translate text='I agree to abide by acceptable use'|escape}" />
      </div>
    </form>
  {/if}
</div>
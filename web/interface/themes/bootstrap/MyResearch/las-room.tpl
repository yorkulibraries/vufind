<h1>{$pageTitle|translate|escape}</h1>

{if $message}
  <div class="alert alert-danger">{translate text=$message|escape}</div>
{else}
  <p class="text-success">
    {translate text='The current access code is'}: <strong>{$code}</strong>
  </p>
{/if}
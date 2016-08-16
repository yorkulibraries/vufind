<h1>{translate text='Error'}</h1>
<p class="text-danger">{$message|translate|escape}</p>

{if !empty($paymentStatus.status)}
  <p>{translate text='The credit card transaction status is:'} {$paymentStatus.status|escape}</p>
{/if}
{if !empty($paymentStatus.message)}
  <p>{translate text='The credit card transaction message is:'} {$paymentStatus.message|escape}</p>
{/if}

<div class="btn-group">
  <a class="btn btn-primary btn-sm" href="{$path}/MyResearch/Fines" role="button">{translate text='Go back to view my fines'}</a>
</div>

<h1>Preqin Access</h1>

<div class="alert alert-warning" role="alert">
<p>Use of this product is restricted to members of the York University community.</p>

<p>It is the responsibility of each user to ensure that he or she uses this product for individual,
non-commercial educational or research purposes only.</p>
</div>

<p>Choose one of the logins provided below and then</p>
<div class="btn-group">
<a class="btn btn-sm btn-default" href="{$finalUrl|escape}">click here to sign on</a>
</div>



<h2>Login with:</h2>
{foreach from=$passwords item=item}
<p>Username: {$item.0|escape}</p>
<p>Password: {$item.1|escape}</p>
<br/>
{/foreach}

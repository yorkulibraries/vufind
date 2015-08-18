<h1>TaxFind Access</h1>

<div class="alert alert-warning" role="alert">
<p>Use of this product is restricted to members of the University of York community.</p>

<p>It is the responsibility of each user to ensure that he or she uses this product for individual,
non-commercial educational or research purposes only.</p>
</div>

<p>To gain access to TaxFind at York choose one of the logins provided below and then</p>
<div class="btn-group">
<a class="btn btn-sm btn-default" href="{$finalUrl|escape}">click here to sign on</a>
</div>



<h2>Login with:</h2>
{foreach from=$passwords item=password key=username}
<p>Username: {$username|escape}</p>
<p>Password: {$password|escape}</p>
<br/>
{/foreach}

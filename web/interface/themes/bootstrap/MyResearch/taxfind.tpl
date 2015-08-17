<h1>TaxFind Access</h1>

<p><strong>Use of this product is restricted to members of the University of York community.</strong></p>

<p><strong>It is the responsibility of each user to ensure that he or she uses this product for individual,
non-commercial educational or research purposes only.</strong></p>

<p><strong>To gain access to TaxFind at York choose one of the logins provided below and then
click <a href="{$finalUrl|escape}">here</a> to sign on.</strong></p>

<h2>Login with:</h2>
{foreach from=$passwords item=password key=username}
<p>Username: {$username|escape}</p>
<p>Password: {$password|escape}</p>
<br/>
{/foreach}

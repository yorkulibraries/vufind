<p class="text-success">
  {translate text='You have successfully authenticated as'} <strong>{$user->firstname|lower|regex_replace:'/\([a-z]+\.\)/':''|ucwords|escape} {$user->lastname|lower|ucwords|escape}.</strong>
</p>

{include file="MyResearch/fines-summary.tpl"}

<a class="btn btn-primary btn-sm" href="{$path}/MyResearch/CheckedOut" role="button">{translate text='Go to Your Account'}</a>

<p class="text-success">
  {assign var=userFirstName value=$user->firstname|lower|ucwords}
  {assign var=userLastName value=$user->lastname|lower|ucwords}
  {assign var=userFullName value=$userFirstName|cat:' '|cat:$userLastName}
  {assign var=welcomeMessage value='you_have_successfully_authenticated_as_xyz'|translate}
  {$welcomeMessage|replace:'###USERNAME###':$userFullName}
</p>

{include file="MyResearch/fines-summary.tpl"}

<a class="btn btn-primary btn-sm" href="{$path}/MyResearch/CheckedOut" role="button">{translate text='Go to Your Account'}</a>

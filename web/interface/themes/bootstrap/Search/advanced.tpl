<h1>{translate text='Advanced Search'}</h1>
<div class="change-search-link">
  <span class="fa fa-search"></span> <a href="{$path}/Search/Home?mylang={$userLang}">{translate text='Basic Search'}</a>
</div>

{if $editErr}
  {assign var=error value="advSearchError_$editErr"}
  <div class="alert alert-danger alert-dismissable">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    {translate text=$error}
  </div>
{/if}

{include file="Search/advanced-search-form.tpl"}


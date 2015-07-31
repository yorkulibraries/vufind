{if $recordDataSource=='SIRSI'}
<div class="btn-group request-buttons-container">
  <a target="_blank" href="http://theta.library.yorku.ca/uhtbin/cgisirsi/x/0/0/5?searchdata1=a{$id}{literal}{CKEY}{/literal}">{translate text='View this record in the classic catalogue'} <span class="fa fa-external-link"></span></a>
</div>
{/if}
{include file=$staffDetails}
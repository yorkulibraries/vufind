{if $isLAWStorageRequest}
<div class="alert alert-warning alert-dismissable">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
  {translate text='law_storage_request_instructions'}
</div>
{/if}

{if $isLAWSpecialCollectionRequest}
<div class="alert alert-warning alert-dismissable">
  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
  {translate text='law_special_collections_request_instructions'}
</div>
{/if}
<div class="media-left">
  {assign var=coverId value=null}
  {if $summId}{assign var=coverId value=$summId}{/if}
  {if $listId}{assign var=coverId value=$listId}{/if}
  {if $id}{assign var=coverId value=$id}{/if}
  
  {assign var=coverImage value=null}
  {if $summThumb}
    {assign var=coverImage value=$summThumb}
  {elseif $listThumb}
    {assign var=coverImage value=$listThumb}
  {/if}
  <div class="media-object">
    {if $coreThumbMedium}
      <img class="bookcover" src="{$coreThumbMedium|escape}" alt="{translate text='Cover Image'}"/>
    {else}
      {if $coverImage && $coverId}
      <a href="{$path}/Record/{$coverId|escape}">
        <img class="bookcover" src="{$coverImage|escape}" alt="{translate text='Cover Image'}"/>
      </a>
      {/if}
    {/if}
  </div>

  {if $coverId}
  <form class="coverupload" action="{$path}/AJAX/UploadCover" method="post" enctype="multipart/form-data">
    <div class="btn btn-default btn-sm fileinput-button {if !$user || !$user->can_upload_covers}hidden{/if} upload-cover-button">
      <span class="fa fa-upload"></span>
      <span>{translate text='Upload'}</span>
      <label class="sr-only" for="coverupload_{$coverId}_files">{translate text='Select image file'}</label>
      <input id="coverupload_{$coverId}_files" type="file" name="files[]" />
      <input type="hidden" name="id" value="{$coverId}" />
      <input type="submit" class="sr-only" name="submit" value="{translate text='Upload'}"/>
    </div>
  </form>
  {/if}
  
  {assign var=previewISBN value=null}
  {if $summISBN}{assign var=previewISBN value=$summISBN}{/if}
  {if $listISBN}{assign var=previewISBN value=$listISBN}{/if}
  {if $isbn}{assign var=previewISBN value=$isbn}{/if}
  {if $previewISBN}
  <div class="google-books-preview hidden" data-isbn="{$previewISBN}">
    <a href="#" title="{translate text='Preview from'} Google Books" target="_blank">
      <img class="img-responsive" src="//www.google.com/intl/en/googlebooks/images/gbs_preview_button1.png" alt="{translate text='Preview'}"/>
    </a>
  </div>
  {/if}
</div>

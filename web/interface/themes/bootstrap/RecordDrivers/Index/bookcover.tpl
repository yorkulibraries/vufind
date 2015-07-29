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
  <div aria-hidden="true" class="media-object">
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

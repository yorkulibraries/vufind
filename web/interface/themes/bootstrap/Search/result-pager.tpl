{if $pageLinks.all}
<div class="pagination-centered print-hidden hidden-xs">
  <ul class="pagination">
    {if $pageLinks.back}
      {$pageLinks.back}
    {/if}
    {$pageLinks.pages}
    {if $pageLinks.next}
      {$pageLinks.next}
    {/if}
  </ul>
</div>
<ul class="pager visible-xs">
  {if $pageLinks.back}
    <li class="previous"><a href="{$pageLinks.linkTagsRaw.prev.url}">&larr; {translate text='Prev'}</a></li>
  {/if}
  {if $pageLinks.next}
    <li class="next"><a href="{$pageLinks.linkTagsRaw.next.url}">{translate text='Next'} &rarr;</a></li>
  {/if}
</ul>
{/if}

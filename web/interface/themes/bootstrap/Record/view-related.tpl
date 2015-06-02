{if $browseShelf}
  <h4>{translate text="On the Shelf"}</h4>
  {$browseShelf}
{/if}

{if !empty($similarItems)}
  <h4>{translate text="Similar Items"}</h4>
  <ul class="carousel similar-items">
  {foreach from=$similarItems item=item}
    {$item}
  {/foreach}
  </ul>
{/if}

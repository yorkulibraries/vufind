{if $browseShelf}
<div class="section">
  <h4>{translate text="On the Shelf"}</h4>
  {$browseShelf}
</div>
{/if}

{if !empty($similarItems)}
<div class="section">
  <h4>{translate text="Similar Items"}</h4>
  <div class="carousel-container">
    <div class="carousel">
    {foreach from=$similarItems item=item}
      {$item}
    {/foreach}
    </div>
  </div>
</div>
{/if}

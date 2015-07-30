{if !empty($recordsToTheLeft) || !empty($recordsToTheRight)}
<div class="carousel-container">
  <div class="carousel browse-shelf" data-start-index="{$startIndex}" data-autoplay="false" data-infinite="false">
    {foreach from=$recordsToTheLeft item=item}
      {$item}
    {/foreach}
    {foreach from=$recordsToTheRight item=item}
      {$item}
    {/foreach}
  </div>
</div>
{/if}
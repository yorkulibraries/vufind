<div class="carousel-container">
  <div class="carousel browse-shelf" data-start-index="{$startIndex}">
    {foreach from=$recordsToTheLeft item=item}
      {$item}
    {/foreach}
    {$thisRecord}
    {foreach from=$recordsToTheRight item=item}
      {$item}
    {/foreach}
  </div>
</div>
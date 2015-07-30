<div class="carousel-container" data-carousel-id="{$id}">
  <div id="{$id}" class="carousel" data-start-index="0" data-infinite="true" data-autoplay="true">
  {foreach from=$carouselItems item=item}
    {$item}
  {/foreach}
  </div>
</div>

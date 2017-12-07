<div class="carousel-container" data-carousel-id="{$id|escape}">
  <div id="{$id|escape}" class="carousel" data-start-index="0" data-infinite="true" data-autoplay="{$autoplay|escape}">
  {foreach from=$carouselItems item=item}
    {$item}
  {/foreach}
  </div>
</div>

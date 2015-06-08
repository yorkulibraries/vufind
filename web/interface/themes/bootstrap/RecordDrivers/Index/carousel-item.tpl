{if $summId && $summThumb}
<div class="carousel-item">
  <div class="slide-inner">
    <a href="{$path}/Record/{$summId}">
      <img alt="{translate text='Cover Image'}" src="{$summThumb}" title="{$summTitle|escape}" />
      <div class="small title">
        {$summTitle|trim:' /:'|escape}
      </div>
    </a>
  </div>
</div>
{/if}
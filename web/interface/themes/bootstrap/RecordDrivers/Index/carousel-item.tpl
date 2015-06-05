{if $summId && $summThumb}
<div class="carousel-item">
  <div class="slide-inner">
    <a href="{$path}/Record/{$summId}">
      <img alt="{translate text='Cover Image'}" src="{$summThumb}" title="{$summTitle|escape}" />
      <div class="small">
        {$summTitle|truncate:30|trim:' /:'|escape}
      </div>
    </a>
  </div>
</div>
{/if}
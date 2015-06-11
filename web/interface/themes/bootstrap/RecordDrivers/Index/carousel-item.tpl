{if $summId && $summThumb}
<div class="carousel-item">
  <div class="slide-inner">
    <a href="{$url}/Record/{$summId}">
      <img alt="{translate text='Cover Image'}" src="{$summThumb}" title="{if $yorkTitleWithoutMedium}{$yorkTitleWithoutMedium|trim:' /:'|escape}{else}{$summTitle|trim:' /:'|escape}{/if}" />
      <div class="small title">
        {if $yorkTitleWithoutMedium}{$yorkTitleWithoutMedium|trim:' /:'|escape}{else}{$summTitle|trim:' /:'|escape}{/if}
      </div>
    </a>
  </div>
</div>
{/if}
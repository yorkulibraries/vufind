{if $yorkMobileEBookGuide}
<div class="panel panel-default">
  <div class="panel-heading">
    <h4 class="panel-title">{translate text="Mobile eBook"}</h4>
  </div>
    <div class="list-group">
      <a target="_blank" href="{$yorkMobileEBookGuide.url|escape}" class="list-group-item">
        <h5 class="list-group-item-heading">{$yorkMobileEBookGuide.collection|escape}</h5>
      </a>
    </div>
</div>
{/if}

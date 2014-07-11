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

{if is_array($similarRecords)}
<div class="panel panel-default similar-records">
  <div class="panel-heading">
    <h4 class="panel-title">{translate text="Similar Items"}</h4>
  </div>
  {foreach from=$similarRecords item=similar}
    <div class="list-group">
      <a href="{$path}/Record/{$similar.id|escape}" class="list-group-item">
        <h5 class="list-group-item-heading">{$similar.title|escape}</h5>
        {if $similar.author}
        <p class="list-group-item-text">{translate text='By'}: {$similar.author|escape}</p>
        {/if}
        {if $similar.publishDate}
        <p class="list-group-item-text">{translate text='Published'}: {$similar.publishDate.0|escape}</p>
        {/if}
      </a>
    </div>
  {/foreach}
</div>
{/if}
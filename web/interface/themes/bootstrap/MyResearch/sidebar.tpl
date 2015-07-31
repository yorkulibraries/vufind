{if $list && $tagList}
<div class="tags" role="navigation">
  <h4><span class="fa fa-tag"></span> {translate text='Tags'}</h4>
  <ul class="nav nav-pills">      
  {foreach from=$tagList item=tag}
    <li {if in_array($tag->tag, $tags)} class="active"{/if} role="presentation">
      <a href="{$path}/MyResearch/MyList/{$list->id}{if !in_array($tag->tag, $tags)}?tag[]={$tag->tag|escape:'url'}{/if}">
        {if in_array($tag->tag, $tags)}<span class="fa fa-close"></span>{/if} {$tag->tag|escape} <span class="badge">{$tag->cnt}</span></a> 
    </li>
  {/foreach}
  </ul>
</div>
{/if}

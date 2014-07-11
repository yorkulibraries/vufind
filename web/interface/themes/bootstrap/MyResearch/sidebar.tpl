{if false && $pageTemplate == 'login.tpl'}
  <div class="panel panel-default">
    <div class="panel-heading">
      <h4 class="panel-title">{translate text='Login FAQs'}</h4>
    </div>
    <div class="panel-body">
      TODO:
    </div>
  </div>
{/if}
{if $list && $tagList}
  <div class="panel panel-default">
    <div class="panel-heading">
      <h4 class="panel-title">{translate text='Tags'}</h4>
    </div>
    {if false}
    <div class="panel-body">
    </div>
    {/if}
    <ul class="list-group">      
    {foreach from=$tagList item=tag}
      <li class="list-group-item">
        <span class="fa fa-tag"></span>
        <a href="{$path}/MyResearch/MyList/{$list->id}?tag[]={$tag->tag|escape:'url'}{foreach from=$tags item=mytag}&amp;tag[]={$mytag|escape:'url'}{/foreach}">{$tag->tag|escape}</a> 
        <span class="badge bg-success pull-right">{$tag->cnt}</span>
      </li>
    {/foreach}
    </ul>
  </div>
{/if}

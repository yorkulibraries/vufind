<h1>{translate text='Search History'}</h1>

{if !$noHistory}
  {if $saved}
    <h2>{translate text="history_saved_searches"}</h2>
    <table class="table table-condensed table-hover table-responsive">
    <caption class="sr-only">{translate text='Table listing of your saved searches'}</caption>
    <thead>
      <tr>
        <th>{translate text="history_time"}</th>
        <th>{translate text="history_search"}</th>
        <th>{translate text="history_limits"}</th>
        <th>{translate text="history_results"}</th>
        <th>{translate text="history_delete"}</th>
      </tr>
    </thead>
    <tbody>
      {foreach item=info from=$saved name=historyLoop}
      <tr>
        <td>{$info.time}</td>
        <td><a href="{$info.url|escape}">{if empty($info.description)}{translate text="history_empty_search"}{else}{$info.description|escape}{/if}</a></td>
        <td>{foreach from=$info.filters item=filters key=field}{foreach from=$filters item=filter}
          <div>{translate text=$field|escape}: <strong>{$filter.display|escape}</strong></div>
        {/foreach}{/foreach}</td>
        <td>{$info.hits}</td>
        <td><a class="btn btn-danger btn-xs" href="{$path}/MyResearch/SaveSearch?delete={$info.searchId|escape:"url"}&amp;mode=history">{translate text="history_delete_link"}</a></td>
      </tr>
      {/foreach}
    </tbody>
    </table>
  {/if}

  {if $links}
    <h2>{translate text="history_recent_searches"}</h2>
    <table class="table table-condensed table-hover table-responsive">
    <caption class="sr-only">{translate text='Table listing of your recent unsaved searches'}</caption>
    <thead>
      <tr>
        <th class="hidden-xs">{translate text="history_time"}</th>
        <th>{translate text="history_search"}</th>
        <th>{translate text="history_limits"}</th>
        <th class="hidden-xs">{translate text="history_results"}</th>
        <th>{translate text="history_save"}</th>
      </tr>
    </thead>
    <tbody>
      {foreach item=info from=$links name=historyLoop}
      <tr>
        <td class="hidden-xs">{$info.time}</td>
        <td><a href="{$info.url|escape}">{if empty($info.description)}{translate text="history_empty_search"}{else}{$info.description|escape}{/if}</a></td>
        <td>{foreach from=$info.filters item=filters key=field}{foreach from=$filters item=filter}
          <div>{translate text=$field|escape}: <strong>{$filter.display|escape}</strong></div>
        {/foreach}{/foreach}</td>
        <td class="hidden-xs">{$info.hits}</td>
        <td><a class="btn btn-primary btn-xs" href="{$path}/MyResearch/SaveSearch?save={$info.searchId|escape:"url"}&amp;mode=history">{translate text="history_save_link"}</a></td>
      </tr>
      {/foreach}
    </tbody>
    </table>

    <div class="btn-group">
      <a class="btn btn-danger" href="{$path}/Search/History?purge=true" class="delete"><span class="icon-trash"></span> {translate text="history_purge"}</a>
    </div>
  {/if}

{else}
  <h2>{translate text="history_recent_searches"}</h2>
  {translate text="history_no_searches"}
{/if}

<ul>
  {foreach from=$items item=item key=index}
    <li>
      <a title="{$item.title_full|trim:'/.- '|escape}" href="{$url}/Record/{$item.id}">
      <img alt="{$item.title_full|trim:'/.- '|escape}" src="{$url}/bookcover.php?isn={if !empty($item.isbn)}{$item.isbn|@implode:','|escape}{else}{$item.issn|@implode:','|escape}{/if}&id={$item.id}&size=large" />
      </a>
    </li>
  {/foreach}
</ul>

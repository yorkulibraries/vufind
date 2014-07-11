<html lang="{$userLang}">
<![CDATA[
<style type="text/css">
{literal}
  .yul-portlet>ul {
    margin-top: 0;
    margin-bottom: 0;
    padding: 0;
  }
{/literal}
</style>

<div class="yul-portlet">
  {foreach from=$contents key=groupName item=items}
    {if $groupName=='My Librarian' && !empty($items)}
      <ul>
        {foreach from=$items item=item}
          <li>
            <a href="{$item->link|escape}">{$item->title|escape}</a>
          </li>
        {/foreach}
      </ul>
    {/if}
  {/foreach}
</div>
]]>
</html>

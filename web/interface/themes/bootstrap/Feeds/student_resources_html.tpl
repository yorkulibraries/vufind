<html lang="{$userLang}">
<![CDATA[
<style type="text/css">
{literal}
  .yul-portlet>h2 {
    font-size: 1.2em;
    margin: 10px 0;
  }
  .yul-portlet>ul {
    margin-top: 0;
    margin-bottom: 0;
    padding: 0;
  }
  .yul-portlet>ul.yul-links {
    list-style-type: none;
    margin: 0;
    padding: 0;
  }
{/literal}
</style>

<div class="yul-portlet">
  <p>{translate text='We suggest these research guides to help you with your current courses'}:</p>
  {foreach from=$contents key=groupName item=items}
    {if !empty($items)}
      <h2>{$groupName|translate|escape}</h2>
      <ul>
        {foreach from=$items item=item}
          <li>
            <a href="{$item->link|escape}">{$item->title|escape}</a>
          </li>
        {/foreach}
      </ul>
    {/if}
  {/foreach}
  
  <hr />

  <ul class="yul-links">
    <li>
      {translate text='See'} <a target="_blank" href="http://researchguides.library.yorku.ca/content.php?pid=220564">{translate text='the list of all subject guides'}</a> {translate text='for more'}.
    </li>
    <li>
      {translate text='See'} <a target="_blank" href="http://researchguides.library.yorku.ca/courses">{translate text='the list of all course guides'}</a> {translate text='for more'}.
    </li>
  </ul>
</div>
]]>
</html>

<html lang="{$userLang}">
<![CDATA[
<style type="text/css">
{literal}
  .sr-only {
    border: 0 none;
    clip: rect(0px, 0px, 0px, 0px);
    height: 1px;
    margin: -1px;
    overflow: hidden;
    padding: 0;
    position: absolute;
    width: 1px;
  }
  .yul-form-group {
    display: inline-block;
    margin-bottom: 10px;
    margin-right: 5px;
  }
  .yul-form-group input,.yul-form-group select {
    display: inline-block;
  }
  .yul-form-group input.yul-search-terms {
    min-width: 300px;
    padding-bottom: 2px;
    padding-top: 2px;
  }
  .yul-form-group .yul-search-button {
    vertical-align: middle;
  }
{/literal}
</style>
<script type="text/javascript">
{literal}
  jQuery(document).ready(function() {
    jQuery('#yulSearchType').change(function(e) {
      var searchType = jQuery(this).find(':selected').val();
      var form = jQuery(this).closest('form');
      var lookfor = form.find('input[name="lookfor"]');
      var placeholder = lookfor.attr('data-placeholder-' + searchType.toLowerCase());
      lookfor.attr('placeholder', placeholder);
    });
  });
{/literal}
</script>
<div class="yul-portlet">
  <form class="yul-basic-search" role="form" method="get" action="{$url}/Search/Results">
    <input type="hidden" name="mylang" value="{$userLang}" />
    <div class="yul-form-group">
      <label class="sr-only" for="yulSearchLookfor">{translate text='Your search terms'}</label>
      <input class="yul-search-terms"
        id="yulSearchLookfor" type="text" name="lookfor"
        placeholder="{translate text='basic_search_placeholder_allfields'|escape}"
        {foreach from=$basicSearchTypes item=searchDesc key=searchVal name=typeloop}
          data-placeholder-{$searchVal|lower}="{'basic_search_placeholder_'|cat:$searchVal|lower|translate|escape}"
        {/foreach}
      >
    </div>
    <div class="yul-form-group">
      <label class="sr-only" for="yulSearchType">{translate text='Search Type'}</label>
      <select name="type" id="yulSearchType">
        {foreach from=$basicSearchTypes item=searchDesc key=searchVal name=typeloop}
          <option value="{$searchVal}">{$searchDesc|translate|escape}</option>
        {/foreach}
      </select>

      <input class="yul-search-button" type="image" src="/StudentTheme-theme/images/common/search.png" title="{translate text='Find'}"/>
    </div>
  </form>
</div>
]]>
</html>

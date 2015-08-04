{if $isFond}
  {include file="RecordDrivers/Index/fond-field-list.tpl"}
{else}
  {include file="RecordDrivers/Index/core-field-list.tpl"}
{/if}
{if $coreCOinS}
  <span class="Z3988" title="{$coreCOinS|escape}"></span>
{/if}
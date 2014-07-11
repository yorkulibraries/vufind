<div class="media">
  {include file="RecordDrivers/Index/bookcover.tpl"}
  <div class="media-body">
    <h2 class="media-heading">
      {if $fullTitle}
        {$fullTitle|escape}
      {else}
        {$coreShortTitle|escape}
        {if $coreSubtitle}{$coreSubtitle|escape}{/if}
        {if $coreTitleSection}{$coreTitleSection|escape}{/if}
        {* {if $coreTitleStatement}{$coreTitleStatement|escape}{/if} *}
      {/if}
    </h2>
    {if $isFond}
	    {include file="RecordDrivers/Index/fond-field-list.tpl"}
	  {else}
	    {include file="RecordDrivers/Index/core-field-list.tpl"}
    {/if}
  </div>
</div>

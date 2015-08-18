<div class="pull-left number-and-checkbox print-hidden">
  <span class="result-number">{math equation="x + y" x=$recordStart y=$listItemIndex}.</span>
  {if !$hideCheckbox}
    {include file="RecordDrivers/Index/add-remove-bookbag.tpl"}
  {/if}
</div>
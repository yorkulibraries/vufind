<ul class="browse-shelf">
  {foreach from=$recordsToTheLeft item=item}
    {$item}
  {/foreach}
  {$thisRecord}
  {foreach from=$recordsToTheRight item=item}
    {$item}
  {/foreach}
</ul>

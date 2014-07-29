<h1>{$course|escape}</h1>
<p>{$instructor|escape}</p>
{if $subpage}
  {include file=$subpage}
{else}
  {$pageContent}
{/if}

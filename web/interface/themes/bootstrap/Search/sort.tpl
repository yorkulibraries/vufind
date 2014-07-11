<button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown">
  {translate text='Sort'} <span class="fa fa-sort"></span>
</button>
<ul class="dropdown-menu" role="menu">
  {foreach from=$sortList item=sortData key=sortLabel}
    <li role="presentation" class="{if $sortData.selected}active{/if}">
      <a role="menuitem" tabindex="-1" href="{$sortData.sortUrl|escape}">
        {if $sortData.desc == 'sort_relevance'}
          <span class="fa fa-arrow-down"></span>
        {/if}
        {if $sortData.desc == 'sort_year'}
          <span class="fa fa-arrow-down"></span>
        {/if}
        {if $sortData.desc == 'sort_year asc'}
          <span class="fa fa-arrow-up"></span>
        {/if}
        {if $sortData.desc == 'sort_first_indexed'}
          <span class="fa fa-arrow-down"></span>
        {/if}
        {if $sortData.desc == 'sort_first_indexed asc'}
          <span class="fa fa-arrow-up"></span>
        {/if}
        {if $sortData.desc == 'sort_callnumber'}
          <span class="fa fa-sort-alpha-asc"></span>
        {/if}
        {if $sortData.desc == 'sort_author'}
          <span class="fa fa-sort-alpha-asc"></span>
        {/if}
        {if $sortData.desc == 'sort_title'}
          <span class="fa fa-sort-alpha-asc"></span>
        {/if}
        {translate text=$sortData.desc}
      </a>
    </li>
  {/foreach}
</ul>

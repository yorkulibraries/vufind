{if !empty($electronic)}
<ul>
  {foreach from=$electronic item=link}
    <li class="more-less">
      <span class="label bg-success online-label">{translate text='Online'}</span>
      <a title="{translate text='Click to access this resource'}" target="_blank" class="online-access" data-proxy="{$link.proxy}" data-service-id="{$link.service_id}" data-target-name="{$link.target_name}" href="{$link.href}">{$link.title|trim|escape}
        {if $link.coverage}- <span class="coverage">{$link.coverage|replace:'Available':''|escape}</span>{/if}
      </a>
      {if $link.usage_rights}
        {assign var=oculUsageRights value=$link}
        {include file="OUR/usage-rights.tpl"}
      {/if}
    </li>
  {/foreach}
</ul>
<button data-toggle="more-less" class="btn btn-default btn-xs hidden" data-threshold="3" data-target=".openurl-container" data-target-name="ajax openurls"><span class="fa fa-plus"></span> <span class="more-less-label" data-alt="{translate text='Less'}">{translate text="More"}</span></button>
{/if}
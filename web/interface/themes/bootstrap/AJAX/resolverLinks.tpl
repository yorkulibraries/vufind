{if !empty($electronic)}
<ul>
  {foreach from=$electronic item=link}
    <li class="more-less">
      <span class="label bg-success online-label">{translate text='Online'}</span>
      <a title="{translate text='Click to access this resource'}" target="_blank" class="online-access" data-proxy="{$link.proxy}" data-service-id="{$link.service_id}" data-target-name="{$link.target_name}" href="{$link.href}">{$link.title|trim|escape}
        {if $link.coverage}- <span class="coverage">{$link.coverage|replace:'Available':''|escape}</span>{/if}
        {if $link.notes}<span class="notes">({$link.notes|escape})</span>{/if}
      </a>
      
      {if $link.usage_rights}
      <a data-license-name="{$link.license_name|escape}" class="license-information" target="_blank" title="{translate text='View license information'}" href="https://york.scholarsportal.info/licenses/?lang={$userLang}&tag={$link.license_name}">
        <div class="row">
          <div class="col-sm-6 col-lg-4 use-case">
            <span class="label bg-{if $link.usage_rights->e_reserves->status}{$link.usage_rights->e_reserves->status}{else}danger{/if}">{if $link.usage_rights->e_reserves->usage}{$link.usage_rights->e_reserves->usage|trim|translate|escape}{else}{translate text='No'}{/if}</span>
            <span class="question">{translate text='usage_right_e_reserves'|escape}</span>
          </div>
          <div class="col-sm-6 col-lg-4 use-case">  
            <span class="label bg-{if $link.usage_rights->cms->status}{$link.usage_rights->cms->status}{else}danger{/if}">{if $link.usage_rights->cms->usage}{$link.usage_rights->cms->usage|trim|translate|escape}{else}{translate text='No'}{/if}</span>
            <span class="question">{translate text='usage_right_cms'|escape}</span>
          </div>
          <div class="col-sm-6 col-lg-4 use-case">
            <span class="label bg-{if $link.usage_rights->course_pack->status}{$link.usage_rights->course_pack->status|strtolower}{else}danger{/if}">{if $link.usage_rights->course_pack->usage}{$link.usage_rights->course_pack->usage|trim|translate|escape}{else}{translate text='No'}{/if}</span>
            <span class="question">{translate text='usage_right_course_pack'|escape}</span>
          </div>
          <div class="col-sm-6 col-lg-4 use-case">
            <span class="label bg-{if $link.usage_rights->durable_url->status}{$link.usage_rights->durable_url->status|strtolower}{else}danger{/if}">{if $link.usage_rights->durable_url->usage}{$link.usage_rights->durable_url->usage|trim|translate|escape}{else}{translate text='No'}{/if}</span>
            <span class="question">{translate text='usage_right_durable_url'|escape}</span>
          </div>
          <div class="col-sm-6 col-lg-4 use-case">
            <span class="label bg-{if $link.usage_rights->ill_print->status}{$link.usage_rights->ill_print->status|strtolower}{else}danger{/if}">{if $link.usage_rights->ill_print->usage}{$link.usage_rights->ill_print->usage|trim|translate|escape}{else}{translate text='No'}{/if}</span>
            <span class="question">{translate text='usage_right_ill'|escape}</span>
          </div>
          <div class="col-sm-6 col-lg-4 use-case">  
            <span class="label bg-{if $link.usage_rights->ill_print->status}{$link.usage_rights->ill_print->status|strtolower}{else}danger{/if}">{if $link.usage_rights->ill_print->usage}{$link.usage_rights->ill_print->usage|trim|translate|escape}{else}{translate text='No'}{/if}</span>
            <span class="question">{translate text='usage_right_print'|escape}</span>
          </div>
        </div>
      </a>
      {/if}
    </li>
  {/foreach}
</ul>
{/if}

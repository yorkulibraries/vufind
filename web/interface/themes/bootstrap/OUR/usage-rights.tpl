{if $oculUsageRights}
<a data-license-name="{$oculUsageRights.license_name|escape}" class="license-information" target="_blank" title="{translate text='View license information'}" href="https://york.scholarsportal.info/licenses/?lang={$userLang}&tag={$oculUsageRights.license_name}">
  <div class="row">
    <div class="col-sm-6 col-lg-4 use-case">
      <span class="label bg-{if $oculUsageRights.usage_rights->e_reserves->status}{$oculUsageRights.usage_rights->e_reserves->status}{else}danger{/if}">{if $oculUsageRights.usage_rights->e_reserves->usage}{$oculUsageRights.usage_rights->e_reserves->usage|trim|translate|escape}{else}{translate text='No'}{/if}</span>
      <span class="question">{translate text='usage_right_e_reserves'|escape}</span>
    </div>
    <div class="col-sm-6 col-lg-4 use-case">  
      <span class="label bg-{if $oculUsageRights.usage_rights->cms->status}{$oculUsageRights.usage_rights->cms->status}{else}danger{/if}">{if $oculUsageRights.usage_rights->cms->usage}{$oculUsageRights.usage_rights->cms->usage|trim|translate|escape}{else}{translate text='No'}{/if}</span>
      <span class="question">{translate text='usage_right_cms'|escape}</span>
    </div>
    <div class="col-sm-6 col-lg-4 use-case">
      <span class="label bg-{if $oculUsageRights.usage_rights->course_pack->status}{$oculUsageRights.usage_rights->course_pack->status|strtolower}{else}danger{/if}">{if $oculUsageRights.usage_rights->course_pack->usage}{$oculUsageRights.usage_rights->course_pack->usage|trim|translate|escape}{else}{translate text='No'}{/if}</span>
      <span class="question">{translate text='usage_right_course_pack'|escape}</span>
    </div>
    <div class="col-sm-6 col-lg-4 use-case">
      <span class="label bg-{if $oculUsageRights.usage_rights->durable_url->status}{$oculUsageRights.usage_rights->durable_url->status|strtolower}{else}danger{/if}">{if $oculUsageRights.usage_rights->durable_url->usage}{$oculUsageRights.usage_rights->durable_url->usage|trim|translate|escape}{else}{translate text='No'}{/if}</span>
      <span class="question">{translate text='usage_right_durable_url'|escape}</span>
    </div>
    <div class="col-sm-6 col-lg-4 use-case">
      <span class="label bg-{if $oculUsageRights.usage_rights->ill_print->status}{$oculUsageRights.usage_rights->ill_print->status|strtolower}{else}danger{/if}">{if $oculUsageRights.usage_rights->ill_print->usage}{$oculUsageRights.usage_rights->ill_print->usage|trim|translate|escape}{else}{translate text='No'}{/if}</span>
      <span class="question">{translate text='usage_right_ill'|escape}</span>
    </div>
    <div class="col-sm-6 col-lg-4 use-case">  
      <span class="label bg-{if $oculUsageRights.usage_rights->ill_print->status}{$oculUsageRights.usage_rights->ill_print->status|strtolower}{else}danger{/if}">{if $oculUsageRights.usage_rights->ill_print->usage}{$oculUsageRights.usage_rights->ill_print->usage|trim|translate|escape}{else}{translate text='No'}{/if}</span>
      <span class="question">{translate text='usage_right_print'|escape}</span>
    </div>
  </div>
</a>
{/if}
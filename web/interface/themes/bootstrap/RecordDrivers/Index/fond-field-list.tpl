<dl class="dl-horizontal">
  {if !empty($yorkOutsideDates)}
    <dt>{translate text='Outside Dates'}:</dt>
    <dd>
      {foreach from=$yorkOutsideDates item=field name=loop}
      <div>
        {$field|escape}
      </div>
      {/foreach}
    </dd>
  {/if}
  
  {if !empty($extendedPhysical)}
  {assign var=extendedContentDisplayed value=1}
    <dt>{translate text='Physical Description'}:</dt>
    <dd>
      {foreach from=$extendedPhysical item=field name=loop}
      <div>
        {$field|escape}
      </div>
      {/foreach}
    </dd>
  {/if}

  {if !empty($yorkBiographicalSketch)}
    <dt>{translate text='Biographical Sketch'}:</dt>
    <dd>
      {foreach from=$yorkBiographicalSketch item=field name=loop}
      <div>
        {$field|escape}
      </div>
      {/foreach}
    </dd>
  {/if}
  
  {if !empty($extendedSummary)}
  {assign var=extendedContentDisplayed value=1}
    <dt>{translate text='Scope and Content'}:</dt>
    <dd>
      {foreach from=$extendedSummary item=field name=loop}
      <div>
        {$field|escape}
      </div>
      {/foreach}
    </dd>
  {/if}

  {if !empty($yorkOwnershipHistoryNote) }
    <dt>{translate text='Custodial History'}:</dt>
    <dd>
      {foreach from=$yorkOwnershipHistoryNote item=field key=key name=loop}
      <div>
        {$field|escape}
      </div>
      {/foreach}
    </dd>
  {/if}

  {if !empty($extendedNotes)}
  {assign var=extendedContentDisplayed value=1}    
      {foreach from=$extendedNotes item=field name=loop}
      <dt>{translate text='General Note'}:</dt>
      <dd>
        {* escape description only if it does not contain HTML tags *}
        {if $field==strip_tags($field)}
          {$field|escape}
        {else}
          {$field}
        {/if}
      </dd>
      {/foreach}
  {/if}
  
  {if !empty($extendedAccess)}
  {assign var=extendedContentDisplayed value=1}
    <dt>{translate text='Access Restriction'}:</dt>
    <dd>
      {foreach from=$extendedAccess item=field name=loop}
      <div>
        {$field|escape}
      </div>
      {/foreach}
    </dd>
  {/if}
  
  {if !empty($yorkReproductionNote) }
    <dt>{translate text='Reproduction'}:</dt>
    <dd>
      {foreach from=$yorkReproductionNote item=field key=key name=loop}
      <div>
        {$field|escape}
      </div>
      {/foreach}
    </dd>
  {/if}

  {if !empty($yorkTermsGoverningUseReproductionNote) }
    <dt>{translate text='Terms Governing Use and Reproduction Note'}:</dt>
    <dd>
      {foreach from=$yorkTermsGoverningUseReproductionNote item=field key=key name=loop}
      <div>
        {$field|escape}
      </div>
      {/foreach}
    </dd>
  {/if}
  
  {if !empty($yorkAssociatedMaterials)}
    <dt>{translate text='Associated Materials'}:</dt>
    <dd>
      {foreach from=$yorkAssociatedMaterials item=field name=loop}
      <div>
        {$field|escape}
      </div>
      {/foreach}
    </dd>
  {/if}

  {if !empty($extendedFindingAids)}
  {assign var=extendedContentDisplayed value=1}
    <dt>{translate text='Finding Aid'}:</dt>
    <dd>
      {foreach from=$extendedFindingAids item=field name=loop}
      <div>
        {$field|escape}
      </div>
      {/foreach}
    </dd>
  {/if}

  {if !empty($yorkAccumulation)}
    <dt>{translate text='Accumulation/use'}:</dt>
    <dd>
      {foreach from=$yorkAccumulation item=field name=loop}
      <div>
        {$field|escape}
      </div>
      {/foreach}
    </dd>
  {/if}
  
  {if !empty($coreContributors)}
    <dt>{translate text='Provenance'}:</dt>
    <dd>
      {foreach from=$coreContributors item=displayValue key=searchValue name=loop}
        <a href="{$url}/Author/Home?author={$searchValue|escape:'url'}">{$displayValue|escape}</a>{if !$smarty.foreach.loop.last} | {/if}
      {/foreach}
    </dd>
  {/if}

  {if !empty($coreCorporateAuthor)}  
    <dt>{translate text='Corporate Author'}:</dt>
    <dd>
      {if is_array($coreCorporateAuthor)}
        {foreach from=$coreCorporateAuthor item=displayValue key=searchValue name=loop}
          <a href="{$url}/Author/Home?author={$searchValue|escape:'url'}">{$displayValue|escape}</a>
        {/foreach}
      {else}
        <a href="{$url}/Author/Home?author={$coreCorporateAuthor|escape:'url'}">{$coreCorporateAuthor|escape}</a>
      {/if}
    </dd>
  {/if}
    
  {if !empty($yorkHoldingInstitution)}
    <dt>{translate text='Holding Institution'}:</dt>
    <dd>
      {foreach from=$yorkHoldingInstitution item=field name=loop}
      <div>
        {$field|escape}
      </div>
      {/foreach}
    </dd>
  {/if}
  
  {if !empty($coreSubjects)}
  
    <dt>{translate text='Subjects'}:</dt>
    <dd>
      {foreach from=$coreSubjects item=field name=loop}
      <div class="subject-line">
        {assign var=subject value=""}
        {foreach from=$field item=subfield name=subloop}
          {if !$smarty.foreach.subloop.first} &raquo; {/if}
          {assign var=subject value="$subject $subfield"}
          <a title="{$subject|escape}" href="{$url}/Search/Results?lookfor=%22{$subject|escape:'url'}%22&amp;type=Subject">{$subfield|escape}</a>
        {/foreach}
      </div>
      {/foreach}
    </dd>
  
  {/if}
</dl>
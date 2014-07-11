<dl class="dl-horizontal">
  {if !empty($coreNextTitles)}
  
    <dt>{translate text='New Title'}:</dt>
    <dd>
      {foreach from=$coreNextTitles item=field name=loop}
      <div>
        <a href="{$url}/Search/Results?lookfor=%22{$field|escape:'url'}%22&amp;type=Title">{$field|escape}</a>
      </div>
      {/foreach}
    </dd>
  
  {/if}

  {if !empty($corePrevTitles)}
  
    <dt>{translate text='Previous Title'}:</dt>
    <dd>
      {foreach from=$corePrevTitles item=field name=loop}
      <div>
        <a href="{$url}/Search/Results?lookfor=%22{$field|escape:'url'}%22&amp;type=Title">{$field|escape}</a>
      </div>
      {/foreach}
    </dd>
  
  {/if}

  {if !empty($coreMainAuthor)}
  
    <dt>{translate text='Main Author'}:</dt>
    <dd><a href="{$url}/Author/Home?author={$coreMainAuthor|escape:'url'}">{$coreMainAuthor|escape}{if !empty($coreMainAuthorFuller)} {$coreMainAuthorFuller|escape}{/if}</a></dd>
  
  {/if}

  {* YORK: we changed the way Corporate Authors and Other Authors are handled *}
  {if !empty($coreCorporateAuthor)}
  
    <dt>{translate text='Corporate Author'}:</dt>
    <dd>
      {if is_array($coreCorporateAuthor)}
        {foreach from=$coreCorporateAuthor item=displayValue key=searchValue name=loop}
        <div>
          <a href="{$url}/Author/Home?author={$searchValue|escape:'url'}">{$displayValue|escape}</a>
        </div>
        {/foreach}
      {else}
        <a href="{$url}/Author/Home?author={$coreCorporateAuthor|escape:'url'}">{$coreCorporateAuthor|escape}</a>
      {/if}
    </dd>
  
  {/if}

  {if !empty($coreContributors)}
  
    <dt>{translate text='Other Authors'}:</dt>
    <dd>
      {foreach from=$coreContributors item=displayValue key=searchValue name=loop}
      <div>
        <a href="{$url}/Author/Home?author={$searchValue|escape:'url'}">{$displayValue|escape}</a>
      </div>
      {/foreach}
    </dd>
  
  {/if}
  {* End Corporate/Other Authors *}
  
  {if !empty($recordFormat)}
  
    <dt>{translate text='Format'}:</dt>
    <dd>
    {if is_array($recordFormat)}
      {foreach from=$recordFormat item=displayFormat name=loop}
      <div>
        <span class="format">{translate text=$displayFormat}</span>
      </div>
      {/foreach}
    {else}
      <span class="format">{translate text=$recordFormat}</span>
    {/if}
    </dd>
  
  {/if}
  
  {if !empty($recordLanguage)}
  
    <dt>{translate text='Language'}:</dt>
    <dd>
      {foreach from=$recordLanguage item=lang name=loop}
      <div>
        {$lang|escape}
      </div>
      {/foreach}
    </dd>
  
  {/if}

  {if !empty($corePublications)}
  
    <dt>{translate text='Published'}:</dt>
    <dd>
      {foreach from=$corePublications item=field name=loop}
      <div>
        {$field|escape}
      </div>
      {/foreach}
      {if !empty($yorkManufacturedDetails)}
        {foreach from=$yorkManufacturedDetails item=field name=loop}
        <div>
  	      {$field|escape}
  	    </div>
      	{/foreach}
  	    {/if}
    </dd>
  
  {/if}

  {if !empty($coreEdition)}
  
    <dt>{translate text='Edition'}:</dt>
    <dd>
      {$coreEdition|escape}
    </dd>
  
  {/if}

  {* Display series section if at least one series exists. *}
  {if !empty($coreSeries)}
  
    <dt>{translate text='Series'}:</dt>
    <dd>
      {foreach from=$coreSeries item=field name=loop}
      <div>
        {* Depending on the record driver, $field may either be an array with
           "name" and "number" keys or a flat string containing only the series
           name.  We should account for both cases to maximize compatibility. *}
        {if is_array($field)}
          {if !empty($field.name)}
            <a href="{$url}/Search/Results?lookfor=%22{$field.name|escape:'url'}%22&amp;type=Series">{$field.name|escape}</a>
            {if !empty($field.number)}
              {$field.number|escape}
            {/if}
          {/if}
        {else}
          <a href="{$url}/Search/Results?lookfor=%22{$field|escape:'url'}%22&amp;type=Series">{$field|escape}</a>
        {/if}
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

  {if !empty($coreRecordLinks)}
  {foreach from=$coreRecordLinks item=coreRecordLink}
  
    <dt>{translate text=$coreRecordLink.title}:</dt>
    <dd><a href="{$coreRecordLink.link|escape}">{$coreRecordLink.value|escape}</a></dd>
  
  {/foreach}
  {/if}

  {* YORK: extra "core" metadata *}
  {foreach from=$yorkVaryingFormsOfTitle item=field name=loop}

    <dt>{translate text=$field.form}:</dt>
    <dd><a href="{$url}/Search/Results?lookfor=%22{$field.title|escape:'url'}%22&amp;type=Title">{$field.title|escape}</a></dd>

  {/foreach}

  {if !empty($yorkUniformTitles)}

    <dt>{translate text='Uniform Title'}:</dt>
    <dd>
      {foreach from=$yorkUniformTitles item=field name=loop}
      <div>
        <a href="{$url}/Search/Results?lookfor=%22{$field|escape:'url'}%22&amp;type=AllFields">{$field|escape}</a>
      </div>
      {/foreach}
    </dd>

  {/if}

  {if !empty($yorkMeetingNames)}

    <dt>{translate text='Meeting Name'}:</dt>
    <dd>
      {foreach from=$yorkMeetingNames item=field name=loop}
      <div>
        {$field|escape}
      </div>
      {/foreach}
    </dd>

  {/if}  

  {if !empty($yorkAddedTitles)}

    <dt>{translate text='Added Title'}:</dt>
    <dd>
      {foreach from=$yorkAddedTitles item=field name=loop}
      <div>
        <a href="{$url}/Search/Results?lookfor=%22{$field|escape:'url'}%22&amp;type=Title">{$field|escape}</a>
      </div>
      {/foreach}
    </dd>

  {/if}

  {if !empty($yorkLanguageNotes)}

    <dt>{translate text='Language Notes'}:</dt>
    <dd>
      {foreach from=$yorkLanguageNotes item=field name=loop}
      <div>
        {$field|escape}
      </div>
      {/foreach}
    </dd>

  {/if}

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

  {if !empty($yorkPublisherNumbers)}

    <dt>
      {translate text='Publisher Number'}: 
   </dt>
    <dd>
      {foreach from=$yorkPublisherNumbers item=field name=loop}
      <div>
        {$field|escape}
      </div>
      {/foreach}
    </dd>

  {/if}

  {if !empty($yorkRelatedURLs)}

    <dt>
      {translate text='Related Resource'}: 
   </dt>
    <dd>  
      {foreach from=$yorkRelatedURLs item=display key=href name=loop}
      <div>
      {if $display|stristr:"bookplate"}
        {assign var="hasBookplate" value=1}
      {else}
        <a target="_blank" href="{$href|escape}">{if !empty($display)}{$display|escape}{else}{$href|escape}{/if}</a>
      {/if}
      </div>
      {/foreach}
    </dd>

  {/if}

  {if $hasBookplate}

    <dt>
      {translate text='Digital Bookplate'}: 
   </dt>
    <dd>  
      {foreach from=$yorkRelatedURLs item=display key=href name=loop}
      <div>
      {if $display|stristr:"bookplate"}
        <a target="_blank" href="{$href|escape}">{if !empty($display)}{$display|escape}{else}{$href|escape}{/if}</a>
      {/if}
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

  {if !empty($yorkDatePlaceEventNote)}

    <dt>{translate text='Date/Place Captured'}:</dt>
    <dd>
      {foreach from=$yorkDatePlaceEventNote item=field name=loop}
      <div>
        {$field|escape}
      </div>
      {/foreach}
    </dd>

  {/if}

  {if !empty($yorkAccumulation)}

    <dt>{translate text='Accumulation'}:</dt>
    <dd>
      {foreach from=$yorkAccumulation item=field name=loop}
      <div>
        {$field|escape}
      </div>
      {/foreach}
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

  {if !empty($yorkPerformerNote) }

    <dt>{translate text='Performer'}:</dt>
    <dd>
      {foreach from=$yorkPerformerNote item=field key=key name=loop}
      <div>
        {$field|escape}
      </div>
      {/foreach}
    </dd>

  {/if}

  {if !empty($yorkWithNote) }

    <dt>{translate text='With'}:</dt>
    <dd>
      {foreach from=$yorkWithNote item=field key=key name=loop}
      <div>
        {$field|escape}
      </div>
      {/foreach}
    </dd>

  {/if}
  {if !empty($yorkDissertationNote) }

    <dt>{translate text='Dissertation'}:</dt>
    <dd>
      {foreach from=$yorkDissertationNote item=field key=key name=loop}
      <div>
        {$field|escape}
      </div>
      {/foreach}
    </dd>

  {/if}

  {if !empty($yorkNumberingNote) }

    <dt>{translate text='Numbering'}:</dt>
    <dd>
      {foreach from=$yorkNumberingNote item=field key=key name=loop}
      <div>
        {$field|escape}
      </div>
      {/foreach}
    </dd>

  {/if}
  {if !empty($yorkFileNote) }

    <dt>{translate text='File/Data'}:</dt>
    <dd>
      {foreach from=$yorkFileNote item=field key=key name=loop}
      <div>
        {$field|escape}
      </div>
      {/foreach}
    </dd>

  {/if}

  {if !empty($yorkSupplementNote) }

    <dt>{translate text='Supplement'}:</dt>
    <dd>
      {foreach from=$yorkSupplementNote item=field key=key name=loop}
      <div>
        {$field|escape}
      </div>
      {/foreach}
    </dd>

  {/if}

  {if !empty($yorkAlternativeNote) }

    <dt>{translate text='Alternative'}:</dt>
    <dd>
      {foreach from=$yorkAlternativeNote item=field key=key name=loop}
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

  {if !empty($yorkOriginalVersionNote) }

    <dt>{translate text='Original Version'}:</dt>
    <dd>
      {foreach from=$yorkOriginalVersionNote item=field key=key name=loop}
      <div>
        {$field|escape}
      </div>
      {/foreach}
    </dd>

  {/if}

  {if !empty($yorkFormerTitleNote) }

    <dt>{translate text='Former Title'}:</dt>
    <dd>
      {foreach from=$yorkFormerTitleNote item=field key=key name=loop}
      <div>
        {$field|escape}
      </div>
      {/foreach}
    </dd>

  {/if}

  {if !empty($yorkIssuingBodyNote) }

    <dt>{translate text='Issuing Body'}:</dt>
    <dd>
      {foreach from=$yorkIssuingBodyNote item=field key=key name=loop}
      <div>
        {$field|escape}
      </div>
      {/foreach}
    </dd>

  {/if}

  {if !empty($yorkOwnershipHistoryNote) }

    <dt>{translate text='Ownership History'}:</dt>
    <dd>
      {foreach from=$yorkOwnershipHistoryNote item=field key=key name=loop}
      <div>
        {$field|escape}
      </div>
      {/foreach}
    </dd>

  {/if}
  {if !empty($yorkLocal590Note) }

    <dt>{translate text='Local Note'}:</dt>
    <dd>
      {foreach from=$yorkLocal590Note item=field key=key name=loop}
      <div>
        {$field|escape}
      </div>
      {/foreach}
    </dd>

  {/if}

  {if !empty($yorkLocal593Note) }

    <dt>{translate text='Local Note'}:</dt>
    <dd>
      {foreach from=$yorkLocal593Note item=field key=key name=loop}
      <div>
        {$field|escape}
      </div>
      {/foreach}
    </dd>

  {/if}

  {if !empty($yorkLocal599Note) }

    <dt>{translate text='Latest Issues'}:</dt>
    <dd>
      {foreach from=$yorkLocal599Note item=field key=key name=loop}
      <div>
        {$field|escape}
      </div>
      {/foreach}
    </dd>

  {/if}

  {if !empty($yorkSourceDescription) }

    <dt>{translate text='Source of Description'}:</dt>
    <dd>
      {foreach from=$yorkSourceDescription item=field key=key name=loop}
      <div>
        {$field|escape}
      </div>
      {/foreach}
    </dd>

  {/if}  
  {* End York extra "core" metadata *}

  {* YORK: Include original "extended" metadata (copied from blueprint's Index/extended.tpl) *}
  {if !empty($extendedDescription)}
  {assign var=extendedContentDisplayed value=1}

    <dt>{translate text='Description'}:</dt>
    <dd>
      {* escape description only if it does not contain HTML tags *}
      {if $extendedDescription==strip_tags($extendedDescription)}
        {$extendedDescription|escape}
      {else}
        {$extendedDescription}
      {/if}
    </dd>

  {/if}

  {if !empty($extendedSummary)}
  {assign var=extendedContentDisplayed value=1}

    <dt>{translate text='Summary'}:</dt>
    <dd>
      {foreach from=$extendedSummary item=field name=loop}
      <div>
        {$field|escape}
      </div>
      {/foreach}
    </dd>

  {/if}

  {if !empty($extendedDateSpan)}
  {assign var=extendedContentDisplayed value=1}

    <dt>{translate text='Published'}:</dt>
    <dd>
      {foreach from=$extendedDateSpan item=field name=loop}
      <div>
        {$field|escape}
      </div>
      {/foreach}
    </dd>

  {/if}

  {if !empty($extendedNotes)}
  {assign var=extendedContentDisplayed value=1}

    <dt>{translate text='Item Description'}:</dt>
    <dd>
      {foreach from=$extendedNotes item=field name=loop}
      <div>
        {* escape description only if it does not contain HTML tags *}
        {if $field==strip_tags($field)}
          {$field|escape}
        {else}
          {$field}
        {/if}
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

  {if !empty($extendedFrequency)}
  {assign var=extendedContentDisplayed value=1}

    <dt>{translate text='Publication Frequency'}:</dt>
    <dd>
      {foreach from=$extendedFrequency item=field name=loop}
      <div>
        {$field|escape}
      </div>
      {/foreach}
    </dd>

  {/if}

  {if !empty($extendedPlayTime)}
  {assign var=extendedContentDisplayed value=1}

    <dt>{translate text='Playing Time'}:</dt>
    <dd>
      {foreach from=$extendedPlayTime item=field name=loop}
      <div>
        {$field|escape}
      </div>
      {/foreach}
    </dd>

  {/if}

  {if !empty($extendedSystem)}
  {assign var=extendedContentDisplayed value=1}

    <dt>{translate text='Technical Details'}:</dt>
    <dd>
      {foreach from=$extendedSystem item=field name=loop}
      <div>
        {$field|escape}
      <div>
      {/foreach}
    </dd>

  {/if}

  {if !empty($extendedAudience)}
  {assign var=extendedContentDisplayed value=1}

    <dt>{translate text='Audience'}:</dt>
    <dd>
      {foreach from=$extendedAudience item=field name=loop}
      <div>
        {$field|escape}
      </div>
      {/foreach}
    </dd>

  {/if}

  {if !empty($extendedAwards)}
  {assign var=extendedContentDisplayed value=1}

    <dt>{translate text='Awards'}:</dt>
    <dd>
      {foreach from=$extendedAwards item=field name=loop}
      <div>
        {$field|escape}
      </div>
      {/foreach}
    </dd>

  {/if}

  {if !empty($extendedCredits)}
  {assign var=extendedContentDisplayed value=1}

    <dt>{translate text='Production Credits'}:</dt>
    <dd>
      {foreach from=$extendedCredits item=field name=loop}
      <div>
        {$field|escape}
      </div>
      {/foreach}
    </dd>

  {/if}

  {if !empty($extendedBibliography)}
  {assign var=extendedContentDisplayed value=1}

    <dt>{translate text='Bibliography'}:</dt>
    <dd>
      {foreach from=$extendedBibliography item=field name=loop}
      <div>
        {$field|escape}
      </div>
      {/foreach}
    </dd>

  {/if}

  {if !empty($extendedISBNs)}
  {assign var=extendedContentDisplayed value=1}

    <dt>{translate text='ISBN'}:</dt>
    <dd>
      {foreach from=$extendedISBNs item=field name=loop}
      <div>
        {$field|escape}
      </div>
      {/foreach}
    </dd>

  {/if}

  {if !empty($extendedISSNs)}
  {assign var=extendedContentDisplayed value=1}

    <dt>{translate text='ISSN'}:</dt>
    <dd>
      {foreach from=$extendedISSNs item=field name=loop}
      <div>
        {$field|escape}
      </div>
      {/foreach}
    </dd>

  {/if}

  {if !empty($extendedRelated)}
  {assign var=extendedContentDisplayed value=1}

    <dt>{translate text='Related Items'}:</dt>
    <dd>
      {foreach from=$extendedRelated item=field name=loop}
      <div>
        {$field|escape}
      </div>
      {/foreach}
    </dd>

  {/if}

  {if !empty($extendedAccess)}
  {assign var=extendedContentDisplayed value=1}

    <dt>{translate text='Access'}:</dt>
    <dd>
      {foreach from=$extendedAccess item=field name=loop}
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

  {if !empty($extendedAuthorNotes)}
  {assign var=extendedContentDisplayed value=1}

    <dt>{translate text='Author Notes'}:</dt>
    <dd>
      {foreach from=$extendedAuthorNotes item=providerList key=provider}
        {foreach from=$providerList item=field name=loop}
        <div>
          {$field.Content}
        </div>
        {/foreach}
      {/foreach}
    </dd>

  {/if}

  {if !empty($extendedVideoClips)}
  {assign var=extendedContentDisplayed value=1}

    <dt>{translate text='Video Clips'}:</dt>
    <dd>
      {foreach from=$extendedVideoClips item=providerList key=provider}
        {foreach from=$providerList item=field name=loop}
          <div>{$field.Content}</div>
          <div>{$field.Copyright}</div>
        {/foreach}
      {/foreach}
    </dd>

  {/if}
  {* End original "extended" metadata inclusion *}
  
  <!-- YorkU 2012 Addition -->

  {if !empty($yorkCountryOfProducingEntity) }
  {assign var=yorkContentDisplayed value=1}

    <dt>{translate text='Country Of Producing Entity'}:</dt>
    <dd>
      {foreach from=$yorkCountryOfProducingEntity item=field key=key name=loop}
      <div>
        {$field|escape}
      </div>
      {/foreach}
    </dd>

  {/if}

  {if !empty($yorkProdPubDistManufactureCopyrightNotice) }
  {assign var=yorkContentDisplayed value=1}
   {foreach from=$yorkProdPubDistManufactureCopyrightNotice item=field name=loop}
  
      <dt>{translate text=$field.form}:</dt>
      <dd>
    	  {$field.value|escape}
      </dd>
  
   {/foreach}
  {/if}

  {if !empty($yorkContentType) }
  {assign var=yorkContentDisplayed value=1}

    <dt>{translate text='Content Type'}:</dt>
    <dd>
      {foreach from=$yorkContentType item=field key=key name=loop}
      <div>
        {$field|escape}
      </div>
      {/foreach}
    </dd>

  {/if}

  {if !empty($yorkMediaType) }
  {assign var=yorkContentDisplayed value=1}

    <dt>{translate text='Media Type'}:</dt>
    <dd>
      {foreach from=$yorkMediaType item=field key=key name=loop}
      <div>
        {$field|escape}
      </div>
      {/foreach}
    </dd>

  {/if}

  {if !empty($yorkCarrierType) }
  {assign var=yorkContentDisplayed value=1}

    <dt>{translate text='Carrier Type'}:</dt>
    <dd>
      {foreach from=$yorkCarrierType item=field key=key name=loop}
      <div>
        {$field|escape}
      </div>
      {/foreach}
    </dd>

  {/if}

  {if !empty($yorkPhysicalMedium) }
  {assign var=yorkContentDisplayed value=1}

    <dt>{translate text='Physical Medium'}:</dt>
    <dd>
      {foreach from=$yorkPhysicalMedium item=field key=key name=loop}
      <div>
        {$field|escape}
      </div>
      {/foreach}
    </dd>

  {/if}

  {if !empty($yorkSoundCharacteristics) }
  {assign var=yorkContentDisplayed value=1}

    <dt>{translate text='Sound Characteristics'}:</dt>
    <dd>
      {foreach from=$yorkSoundCharacteristics item=field key=key name=loop}
      <div>
        {$field|escape}
      </div>
      {/foreach}
    </dd>

  {/if}

  {if !empty($yorkProjectionCharacteristicsOfMovingImage) }
  {assign var=yorkContentDisplayed value=1}

    <dt>{translate text='Projection Characteristics Of Moving Image'}:</dt>
    <dd>
      {foreach from=$yorkProjectionCharacteristicsOfMovingImage item=field key=key name=loop}
      <div>
        {$field|escape}
      </div>
      {/foreach}
    </dd>

  {/if}

  {if !empty($yorkVideoCharacteristics) }
  {assign var=yorkContentDisplayed value=1}

    <dt>{translate text='Video Characteristics'}:</dt>
    <dd>
      {foreach from=$yorkVideoCharacteristics item=field key=key name=loop}
      <div>
        {$field|escape}
      </div>
      {/foreach}
    </dd>

  {/if}

  {if !empty($yorkDigitalFileCharacteristics) }
  {assign var=yorkContentDisplayed value=1}

    <dt>{translate text='Digital File Characteristics'}:</dt>
    <dd>
      {foreach from=$yorkDigitalFileCharacteristics item=field key=key name=loop}
      <div>
        {$field|escape}
      </div>
      {/foreach}
    </dd>

  {/if}

  {if !empty($yorkAssociatedLanguage) }
  {assign var=yorkContentDisplayed value=1}

    <dt>{translate text='Associated Language'}:</dt>
    <dd>
      {foreach from=$yorkAssociatedLanguage item=field key=key name=loop}
      <div>
        {$field|escape}
      </div>
      {/foreach}
    </dd>

  {/if}

  {if !empty($yorkFormOfWork) }
  {assign var=yorkContentDisplayed value=1}

    <dt>{translate text='Form Of Work'}:</dt>
    <dd>
      {foreach from=$yorkFormOfWork item=field key=key name=loop}
      <div>
        {$field|escape}
      </div>
      {/foreach}
    </dd>

  {/if}

  {if !empty($yorkOtherDistinguishingCharacteristics) }
  {assign var=yorkContentDisplayed value=1}

    <dt>{translate text='Other Distinguishing Characteristics'}:</dt>
    <dd>
      {foreach from=$yorkOtherDistinguishingCharacteristics item=field key=key name=loop}
      <div>
        {$field|escape}
      </div>
      {/foreach}
    </dd>

  {/if}

  {if !empty($yorkMediumOfPerformance) }
  {assign var=yorkContentDisplayed value=1}

    <dt>{translate text='Medium Of Performance'}:</dt>
    <dd>
      {foreach from=$yorkMediumOfPerformance item=field key=key name=loop}
      <div>
        {$field|escape}
      </div>
      {/foreach}
    </dd>

  {/if}

  {if !empty($yorkNumbericalDesignationOfMusicalWork) }
  {assign var=yorkContentDisplayed value=1}

    <dt>{translate text='Numberical Designation Of Musical Work'}:</dt>
    <dd>
      {foreach from=$yorkNumbericalDesignationOfMusicalWork item=field key=key name=loop}
      <div>
        {$field|escape}
      </div>
      {/foreach}
    </dd>

  {/if}

  {if !empty($yorkKey384) }
  {assign var=yorkContentDisplayed value=1}

    <dt>{translate text='Key'}:</dt>
    <dd>
      {foreach from=$yorkKey384 item=field key=key name=loop}
      <div>
        {$field|escape}
      </div>
      {/foreach}
    </dd>

  {/if}

  {if !empty($yorkAudienceNote) }
  {assign var=yorkContentDisplayed value=1}

    <dt>{translate text='Audience Note'}:</dt>
    <dd>
      {foreach from=$yorkAudienceNote item=field key=key name=loop}
      <div>
        {$field|escape}
      </div>
      {/foreach}
    </dd>

  {/if}

  {if !empty($licenseURLs)}  

    <dt>{translate text='License'}:</dt>
    <dd>{translate text='See License Terms of Use'}:</dt>
	    {foreach from=$licenseURLs item=licenseURL key=name name=loop}
    	  <div>
    	    <a target="_blank" title="{translate text='See License Terms of Use'}" href="{$licenseURL}">{$name|escape}</a>
    	  </div>
      {/foreach}
    </dd>

  {/if}         

  {if !empty($otherTitles)}

    <dt>{translate text='Other Titles'}:
  	<dd>
  	  {foreach from=$otherTitles item=otherTitle name=loop}
  	  <div>
  	    {$otherTitle|escape} 
  	  </div>
      {/foreach}
    </dd>

  {/if}

  {if !empty($publicNotes)}

    <dt>{translate text='Note'}:
  	<dd>
  	  {foreach from=$publicNotes item=notes name=loop}
  	  <div>
  	    {$notes|escape} 
  	  </div>
      {/foreach}
    </dd>

  {/if}

  <!-- End of YorkU 2012 Addition -->
</dl>
{if is_array($referenceType)}
RT {$referenceType.0|trim}
{else}
RT {$referenceType|trim}
{/if}
{assign var=marcField value=$marc->getField('245')}
T1 {$marcField|getvalue:'a'}{if $marcField|getvalue:'b'} {$marcField|getvalue:'b'|replace:'/':''}{/if}
{* Uniform titles 130 240 730 *}
{assign var=marcField value=$marc->getFields('130')}
{if $marcField}
{foreach from=$marcField item=field name=loop}
T2 {$field|getvalue:'a'}
{/foreach}
{/if}
{assign var=marcField value=$marc->getFields('240')}
{if $marcField}
{foreach from=$marcField item=field name=loop}
T2 {$field|getvalue:'a'}
{/foreach}
{/if}
{assign var=marcField value=$marc->getFields('730')}
{if $marcField}
{foreach from=$marcField item=field name=loop}
T2 {$field|getvalue:'a'}
{/foreach}
{/if}
{* Load the three possible series fields -- 440 is deprecated but
   still exists in many catalogs. *}
{assign var=marcField440 value=$marc->getFields('440')}
{assign var=marcField490 value=$marc->getFields('490')}
{assign var=marcField830 value=$marc->getFields('830')}
{* Check for 490's with indicator 1 == 0; these should be displayed
   since they will have no corresponding 830 field.  Other 490s would
   most likely be redundant and can be ignored. *}
{assign var=visible490 value=0}
{if $marcField490}
{foreach from=$marcField490 item=field}
{if $field->getIndicator(1) == 0}
{assign var=visible490 value=1}
{/if}
{/foreach}
{/if}
{* Display subject section if at least one subject exists. *}
{if $marcField440 || $visible490 || $marcField830}
{if $marcField440}
{foreach from=$marcField440 item=field name=loop}
T2 {$field|getvalue:'a'}
{/foreach}
{/if}
{if $visible490}
{foreach from=$marcField490 item=field name=loop}
{if $field->getIndicator(1) == 0}
T2 {$field|getvalue:'a'}
{/if}
{/foreach}
{/if}
{if $marcField830}
{foreach from=$marcField830 item=field name=loop}
T2 {$field|getvalue:'a'}
{/foreach}
{/if}
{/if}
{assign var=marcField value=$marc->getField('100')}
{if $marcField}
A1 {$marcField|getvalue:'a'}
{/if}
{assign var=marcField value=$marc->getFields('110')}
{if $marcField}
{foreach from=$marcField item=field name=loop}
A2 {$marcField|getvalue:'a'} {$marcField|getvalue:'b'} {$marcField|getvalue:'4'}
{/foreach}
{/if}
{assign var=marcField value=$marc->getFields('710')}
{if $marcField}
{foreach from=$marcField item=field name=loop}
A2 {$field|getvalue:'a'} {$field|getvalue:'b'} {$field|getvalue:'c'} {$field|getvalue:'d'} {$field|getvalue:'4'}
{/foreach}
{/if}
{assign var=marcField value=$marc->getFields('700')}
{if $marcField}
{foreach from=$marcField item=field name=loop}
A2 {$field|getvalue:'a'}
{/foreach}
{/if}
{foreach from=$recordLanguage item=lang}
LA {$lang}
{/foreach}
{assign var=marcField value=$marc->getFields('260')}
{if $marcField}
{foreach from=$marcField item=field name=loop}
PP {$field|getvalue:'a'|replace:':':''} 
PB {$field|getvalue:'b'|replace:',':''} 
YR {$field|getvalue:'c'|replace:'.':''}
{/foreach}
{/if}
{assign var=marcField value=$marc->getFields('250')}
{if $marcField}
{foreach from=$marcField item=field name=loop}
ED {$field|getvalue:'a'}
{/foreach}
{/if}
UL {$url}/Record/{$id|escape:"url"}
{assign var=marcField value=$marc->getField('520')}
{if $marcField}
AB {$marcField|getvalue:'a'} {$marcField|getvalue:'b'}
{/if}
{assign var=marcField value=$marc->getField('300')}
{if $marcField}
OP {$marcField|getvalue:'a'}
{/if}
{assign var=marcField value=$marc->getField('500')}
{if $marcField}
NO {$marcField|getvalue:'a'}
{/if}
{assign var=marcField value=$marc->getField('099')}
{if $marcField}
CN {$marcField|getvalue:'a'}
{else}
{assign var=marcField value=$marc->getField('050')}
{if $marcField}
CN {foreach from=$marcField->getSubfields() item=subfield name=subloop}{$subfield->getData()}{/foreach}
{/if}
{/if}
{assign var=marcField value=$marc->getField('020')}
{if $marcField}
SN {$marcField|getvalue:'a'}
{/if}
{assign var=marcField value=$marc->getFields('650')}
{if $marcField}
{foreach from=$marcField item=field name=loop}
K1 {foreach from=$field->getSubfields() item=subfield name=subloop}{if !$smarty.foreach.subloop.first} : {/if}{assign var=subfield value=$subfield->getData()}{$subfield}{/foreach}
{/foreach}{/if}
{assign var=marcField value=$marc->getField('501')}
{if $marcField}
NO With: {$marcField|getvalue:'a'}
{/if}
{assign var=marcField value=$marc->getField('502')}
{if $marcField}
NO Dissertation: {$marcField|getvalue:'a'}
{/if}
{assign var=marcField value=$marc->getField('506')}
{if $marcField}
NO Access: {$marcField|getvalue:'a'}
{/if}
{assign var=marcField value=$marc->getField('508')}
{if $marcField}
NO Production Credits: {$marcField|getvalue:'a'}
{/if}
{assign var=marcField value=$marc->getField('511')}
{if $marcField}
NO Performer: {$marcField|getvalue:'a'}
{/if}
{assign var=marcField value=$marc->getField('515')}
{if $marcField}
NO Numbering: {$marcField|getvalue:'a'}
{/if}
{assign var=marcField value=$marc->getField('516')}
{if $marcField}
NO File: {$marcField|getvalue:'a'}
{/if}
{assign var=marcField value=$marc->getField('518')}
{if $marcField}
NO DatePlaceEvent: {$marcField|getvalue:'a'}
{/if}
{assign var=marcField value=$marc->getField('521')}
{if $marcField}
NO Audience: {$marcField|getvalue:'a'}
{/if}
{assign var=marcField value=$marc->getField('521')}
{if $marcField}
NO Supplement: {$marcField|getvalue:'a'}
{/if}
{assign var=marcField value=$marc->getField('530')}
{if $marcField}
NO Alternative: {$marcField|getvalue:'a'}
{/if}
{assign var=marcField value=$marc->getField('533')}
{if $marcField}
NO Reproduction: {$marcField|getvalue:'a'}
{/if}
{assign var=marcField value=$marc->getField('534')}
{if $marcField}
NO OriginalVersion: {$marcField|getvalue:'a'}
{/if}
{assign var=marcField value=$marc->getField('538')}
{if $marcField}
NO Technical: {$marcField|getvalue:'a'}
{/if}
{assign var=marcField value=$marc->getField('545')}
{if $marcField}
NO BiographicalSketch: {$marcField|getvalue:'a'}
{/if}
{assign var=marcField value=$marc->getField('546')}
{if $marcField}
NO LanguageNotes: {$marcField|getvalue:'a'}
{/if}
{assign var=marcField value=$marc->getField('547')}
{if $marcField}
NO FormerTitle: {$marcField|getvalue:'a'}
{/if}
{assign var=marcField value=$marc->getField('550')}
{if $marcField}
NO IssuingBody: {$marcField|getvalue:'a'}
{/if}
{assign var=marcField value=$marc->getField('561')}
{if $marcField}
NO OwnershipHistory: {$marcField|getvalue:'a'}
{/if}
{assign var=marcField value=$marc->getField('584')}
{if $marcField}
NO Accumulation: {$marcField|getvalue:'a'}
{/if}
{assign var=marcField value=$marc->getField('588')}
{if $marcField}
NO SourceDescription: {$marcField|getvalue:'a'}
{/if}
{assign var=marcField value=$marc->getField('590')}
{if $marcField}
NO Local Notes: {$marcField|getvalue:'a'}
{/if}
{assign var=marcField value=$marc->getField('593')}
{if $marcField}
NO Local Notes: {$marcField|getvalue:'a'}
{/if}
{assign var=marcField value=$marc->getField('599')}
{if $marcField}
NO Local Notes: {$marcField|getvalue:'bcdefgh'}
{/if}
{assign var=marcField value=$marc->getFields('999')}
{if $marcField}
{foreach from=$marcField item=field name=loop}
AV {$field|getvalue:'a'} {$field|getvalue:'l'} 
{/foreach}{/if}
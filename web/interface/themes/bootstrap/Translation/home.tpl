<h1>{translate text="Translations"}</h1>

{if !empty($index)}
<table class="table table-striped table-condensed table-bordered">
  <tr>
    <th>{translate text="Key"}</th>
    <th>{translate text="Translations"}</th>
  </tr>
  {foreach from=$index key=key item=translations}
  <tr>
    <td>{$key|escape}</td>
    <td>
      <div class="btn-group" role="group" aria-label="Translations">
        {foreach from=$enabledLanguageCodes item=languageCode}
          {assign var=translation value=$translations.$languageCode}
          {if $translation}
            <a class="btn {if $translation->verified}btn-success{else}btn-warning{/if} btn-sm" href="{$path}/Translation/Edit?id={$translation->id}" role="button">{$languageCode|escape}</a>
          {else}
            <a class="btn btn-danger btn-sm" href="{$path}/Translation/Add?lang={$languageCode|escape:'url'}&key={$key|escape:'url'}" role="button">{$languageCode|escape}</a>
          {/if}
        {/foreach}
      </div>
    </td>
  </tr>
  {/foreach}
</table>
{else}
  <a class="btn btn-primary" href="{$path}/Translation/Populate">{translate text="Populate"}</a>
{/if}

<h1>{translate text="Translations"}</h1>

<form class="print-hidden" role="form" method="get" action="{$path}/Translation/Home">
  <div class="input-group">
    <input type="text" name="q" value="{$q|escape}" class="form-control" placeholder="{'Search for Key or Value'|translate|escape}" />
    <div class="input-group-btn">
    <button class="btn btn-default" type="submit"><span class="fa fa-search"></span></button>
    </div>
  </div>
</form>
<br>

{if !empty($results)}
  {foreach from=$results key=key item=translations}
  <div class="panel panel-default">
    <div class="panel-heading">{$key|escape}</div>
    <table class="table">
      <thead>
        <tr>
          <th class="col-md-1">{'Language'|translate|escape}</th>
          <th>{'Value'|translate|escape}</th>
        </tr>
      </thead>
      <tbody>
      {foreach from=$enabledLanguageCodes item=languageCode}
      <tr>
        {assign var=translation value=$translations.$languageCode}
        {if $translation}
          <td>
            <a title="{'Edit this translation'|translate|escape}" class="btn {if $translation->verified}btn-success{else}btn-warning{/if} btn-sm" href="{$path}/Translation/Edit?id={$translation->id}" role="button">{$enabledLanguages.$languageCode|translate|escape}</a>
          </td>
          <td>
            {$translation->value|escape}
          </td>
        {else}
          <td>
            <a title="{'Edit this translation'|translate|escape}" class="btn btn-danger btn-sm" href="{$path}/Translation/Edit?lang={$languageCode|escape:'url'}&key={$key|escape:'url'}" role="button">{$enabledLanguages.$languageCode|translate|escape}</a>
          </td>
          <td></td>
        {/if}
      </tr>
      {/foreach}
      </tbody>
    </table>
  </div>
  {/foreach}
{/if}

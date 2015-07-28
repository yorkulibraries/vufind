<div class="record-location">
  <h1>
    {if $fullTitle}
      {$fullTitle|escape}
    {else}
      {$coreShortTitle|escape}
      {if $coreSubtitle}{$coreSubtitle|escape}{/if}
      {if $coreTitleSection}{$coreTitleSection|escape}{/if}
    {/if}
  </h1>
  
  <dl class="dl-horizontal">
    <dt>{translate text='Call Number'}:</dt>
    <dd>{$callnumber|escape}</dd>
    <dt>{translate text='Location'}:</dt>
    <dd>{$location|escape}</dd>
  </dl>
  
  {if $map}
  <div class="map">
    <img class="img-responsive" src="{$path}/images/maps/{$map}" alt="Map"/>
  </div>
  {else}
    <p>{translate text='No map available'}.</p>
  {/if}
</div>


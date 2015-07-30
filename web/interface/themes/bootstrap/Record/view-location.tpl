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
    <dd>
      {$area.building|escape}{if $area.areaName} - {$area.areaName|escape}{/if}
    </dd>
  </dl>
  
  {if $area.map || $area.googleMap}
  <div class="map">
    {if $area.googleMap}
      <div class="google-map-container">
        <iframe src="{$area.googleMap}" width="400" height="300" frameborder="0" style="border:0" allowfullscreen></iframe>
      </div>
    {else}
      <img class="img-responsive" src="{$path}/images/maps/{$area.map}" alt="Map"/>
    {/if}
  </div>
  {else}
    <p>{translate text='No map available'}.</p>
  {/if}
</div>


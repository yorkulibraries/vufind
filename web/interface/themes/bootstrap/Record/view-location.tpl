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
    {if stripos($map, 'https://www.google.com/maps/embed') == 0 }
      <iframe src="{$map}" width="400" height="300" frameborder="0" style="border:0" allowfullscreen></iframe>
    {else}
      <img class="img-responsive" src="{$path}/images/maps/{$map}" alt="Map"/>
    {/if}
  </div>
  {else}
    <p>{translate text='No map available'}.</p>
  {/if}
</div>


<h1>{translate text="Proxy Abusers"}</h1>

{if !empty($lockedOutList)}
<table class="table table-striped table-condensed table-bordered">
<tr>
  <th>{translate text="Barcode"}</th>
  <th>{translate text="Action"}</th>
</tr>
{foreach from=$lockedOutList item=lockedout}
  <tr>
    <td>{$lockedout->cat_username|escape:"html"}</td>
    <td><a class="btn btn-success btn-sm" href="{$path}/Admin/ProxyAbusers?unlock={$lockedout->cat_username}">{translate text="Unlock"}</a></td>
  </tr>
{/foreach}
</table>  
{else}
  <p>{translate text="There are currently no locked accounts."}</p>
{/if}
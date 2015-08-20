<h1>{translate text="Locked Accounts"}</h1>

{if !empty($lockedOutList)}
<table class="table table-striped table-condensed table-bordered">
<tr>
  <th>{translate text="Username"}</th>
  <th>{translate text="Last attempt"}</th>
  <th>{translate text="Last IP"}</th>
  <th>{translate text="# Attempts"}</th>
  <th>{translate text="Action"}</th>
</tr>
{foreach from=$lockedOutList item=lockedout}
  <tr>
    <td>{$lockedout->username|escape:"html"}</td>
    <td>{$lockedout->last_attempt|escape}</td>
    <td>{$lockedout->ip|escape:"html"}</td>
    <td>{$lockedout->attempts|escape:"html"}</td>
    <td><a class="btn btn-success btn-sm" href="{$path}/Admin/LockedAccounts?delete={$lockedout->id}">{translate text="Unlock"}</a></td>
  </tr>
{/foreach}
</table>  
{else}
  <p>{translate text="There are currently no locked accounts."}</p>
{/if}
<div class="admin-menu">
  <ul class="nav nav-pills nav-stacked">
    <li role="presentation" {if $action == "UserComments"} class="active"{/if}><a href="UserComments">{translate text="User Comments"}</a></li>
    <li role="presentation" {if $action == "MaterialRequests"} class="active"{/if}><a href="MaterialRequests">{translate text="Material Requests"}</a></li>
    <li role="presentation" {if $action == "LockedAccounts"} class="active"{/if}><a href="LockedAccounts">{translate text="Locked Accounts"}</a></li>
    <li role="presentation" {if $action == "OnlinePayments"} class="active"{/if}><a href="OnlinePayments">{translate text="Online Payments"}</a></li>
  </ul>
<div>
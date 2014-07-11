{if $user && $user->firstname}
<ul class="nav navbar-nav navbar-right">
  <li>
    <a href="#" class="dropdown-toggle" data-toggle="dropdown"><span class="fa fa-user"></span> {$user->firstname|lower|regex_replace:'/\([a-z]+\.\)/':''|ucwords|escape} {$user->lastname|lower|ucwords|escape} <b class="caret"></b></a>
    <ul class="dropdown-menu">
      <li>
        <a data-json="{$path}/AJAX/JSON?method=logout" href="{$path}/MyResearch/Logout">{translate text='Logout'}</a>
      </li>
      <li>
        <a href="{$path}/MyResearch/CheckedOut">{translate text='My Checkouts'}</a>
      </li>
      <li>
        <a href="{$path}/MyResearch/Holds">{translate text='My Holds'}</a>
      </li>
      <li>
        <a href="{$path}/MyResearch/Fines">{translate text='My Fines'}</a>
      </li>
      <li>
        <a href="{$path}/MyResearch/MyList">{translate text='My Lists'}</a>
      </li>
      <li>
        <a href="{$path}/Search/History?require_login=1">{translate text='My Search History'}</a>
      </li>
    </ul>
  </li>
</ul>
{/if}

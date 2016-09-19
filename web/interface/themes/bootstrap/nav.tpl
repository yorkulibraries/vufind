<nav id="mainNav" class="navbar navbar-default yul-navbar" role="navigation">
  <div class="container">
    <div class="navbar-header">
      <button class="navbar-toggle" type="button" data-toggle="collapse" data-target=".navbar-collapse">
        <span class="sr-only">{translate text='Navigation menu'}</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a title="{translate text='York University'}" href="http://www.yorku.ca/" class="navbar-brand"><img src="{$path}/interface/themes/bootstrap/images/yib11yorklogo.gif" alt="York University"/></a>
      <a title="{translate text='Library Catalogue'}" href="{$path}/" class="navbar-brand catalogue-home">{translate text='Library Catalogue'}</a>
    </div>

    <div class="collapse navbar-collapse">
      <div id="myAccountPanel">
        {if $user && $user->firstname}
        <ul class="nav navbar-nav navbar-right">
          <li>
            <a href="#" class="dropdown-toggle" data-toggle="dropdown"><span class="fa fa-user"></span> {$user->firstname|lower|regex_replace:'/\([a-z]+\.\)/':''|ucwords|escape} {$user->lastname|lower|ucwords|escape} <b class="caret"></b></a>
            <ul class="dropdown-menu">
              <li>
                <a data-json="{$path}/AJAX/JSON?method=logout" href="{$path}/MyResearch/Logout">{translate text='Logout'}</a>
              </li>
              <li>
                <a href="{$path}/MyResearch/CheckedOut">{translate text='Checkouts'}</a>
              </li>
              <li>
                <a href="{$path}/MyResearch/Holds">{translate text='Holds'}</a>
              </li>
              <li>
                <a href="{$path}/MyResearch/Fines">{translate text='Fines'}</a>
              </li>
              <li>
                <a href="{$path}/MyResearch/MyList">{translate text='Lists'}</a>
              </li>
              <li>
                <a href="{$path}/Search/History?require_login=1">{translate text='Search History'}</a>
              </li>
            </ul>
          </li>
        </ul>
        {else}
          {if $pageTemplate != 'login.tpl' && $pageTemplate != 'ezproxy.tpl'}
          <ul class="nav navbar-nav navbar-right">
            <li>
              <a data-toggle="modal" data-target="#modal" href="{$path}/MyResearch/Home"><span class="fa fa-sign-in"></span> {translate text='Login'}</a>
            </li>
          </ul>
          {/if}
        {/if}  
      </div>
      
      <ul class="nav navbar-nav">
        <li role="presentation">
          <a role="menuitem" tabindex="-1" href="//www.library.yorku.ca/">{translate text='Libraries Home'}</a>
        </li>
        {if 'fr'==$userLang}
          <li role="presentation">
            <a role="menuitem" tabindex="-1" data-mylang="en" href="#">English</a>
          </li>
        {else}
          <li role="presentation">
            <a role="menuitem" tabindex="-1" data-mylang="fr" href="#">Français</a>
          </li>
        {/if}
      </ul>
    </div>
  </div>
</nav>

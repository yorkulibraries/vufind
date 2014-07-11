{if $pageTemplate != 'login.tpl' && $pageTemplate != 'ezproxy.tpl'}
<ul class="nav navbar-nav navbar-right">
  <li>
    <a data-toggle="modal" data-target="#modal" href="{$path}/MyResearch/Home"><span class="fa fa-sign-in"></span> {translate text='Login'}</a>
  </li>
</ul>
{/if}

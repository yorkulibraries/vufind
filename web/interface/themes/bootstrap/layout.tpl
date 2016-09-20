<!DOCTYPE html>
<html lang="{$userLang}">
<head>
  <!-- our javascript -->
  <script type="text/javascript">
    var _global_path = '{$path}';
    var _global_url = '{$url}';
    {if $bookbagWarningMessage}
      var _bookbagWarningMessage = '{$bookbagWarningMessage|translate}';
    {/if}
  </script>
  <title>
  {if $error}
    {translate text="An error has occurred"}
  {elseif $module=='Search' && $pageTemplate=='home.tpl'}
    {translate text='Library Catalogue'}
  {elseif $module=='Search' && $action=='Results'}
    {translate text='Catalogue Search Results'}
  {elseif $breadcrumbText}
    {$pageTitle|escape} : {$breadcrumbText|truncate:64:"..."|escape}
  {else}
    {$pageTitle|escape}
  {/if}
  | {translate text='York University Libraries'}
  </title>
  <meta http-equiv="Content-Type" content="text/html;charset=utf-8"/>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
  {if $addHeader}{$addHeader}{/if}

  {if $module=='Record' && $hasRDF}
  <link rel="alternate" type="application/rdf+xml" title="RDF Representation" href="{$url}/Record/{$id|escape}/RDF"/>    
  {/if}
  <link rel="search" type="application/opensearchdescription+xml" title="Library Catalog Search" href="{$url}/Search/OpenSearch?method=describe" />

  <!-- favicon -->
  <link rel="shortcut icon" href="{$path}/interface/themes/bootstrap/images/favicon.ico" />
  
  <link rel="unapi-server" type="application/xml" title="unAPI" href="{$url}/unAPI/Server"/>
  	
  <!-- our stylesheets -->
  {minifycss media="all" files="font-awesome.min.css,bootstrap.min.css,bootstrap-theme.min.css,bootstrap-datepicker.min.css,pretify.css,jquery.fileupload.css,slick.css,slick-theme.css.less,carousel.css.less,styles.css.less"}
  {minifycss media="print" files="print.css.less"}
  
  <!-- combine javascript files -->
  <script type="text/javascript" src="{$path}/interface/themes/bootstrap/min/f=js/jquery.min.js,js/bootstrap.min.js,js/bootstrap-datepicker.min.js,js/bootstrap-datepicker.fr.min.js,js/jquery.ui.widget.js,js/jquery.fileupload.js,js/jquery.cookie.js,js/slick.js,js/carousel.js,js/ZeroClipboard.min.js,js/spin.min.js,js/vufind.js"></script>
</head>
<body>
  <a class="sr-only" href="#content">{translate text='Skip to main content'}</a>
  
  {include file="nav.tpl"}
  
  <div class="container">
    <!-- $module = {$module}  -->
    <!-- $action = {$action}  -->
    <!-- $pageTemplate = {$pageTemplate}  -->
    <!-- $subTemplate = {$subTemplate}  -->
    
    {if $error}
      <div id="content">
        <div class="alert alert-danger">
          <h1>{translate text="An error has occurred"}</h1>
          {if $isFatal}
            {translate text="fatal_error_staff_notified"}
          {else}
            {$error->getMessage()}
          {/if}
        </div>
      </div>
    {elseif $module=='Search' && $pageTemplate=='home.tpl'}
      <div id="content">
        {include file="$module/$pageTemplate"}
      </div>
    {elseif $module=='Help'}
      <div id="content">
        {include file="$pageTemplate"}
      </div>
    {else}
      <div class="row row-offcanvas row-offcanvas-right">
        <div class="col-xs-12 col-md-8 col-lg-9 left-column print-full">
          <div id="content">
            {if $module=='Search' && $pageTemplate=='list.tpl'}
              <a class="sr-only" href="#searchResults">{translate text='Skip to search results'}</a>
            {/if}
            {if !($module == 'Search' && $pageTemplate == 'home.tpl') && !($pageTemplate == 'ezproxy.tpl')}
              {if $pageTemplate != 'advanced.tpl' && $pageTemplate != 'login.tpl' && $module != 'Record'}
                    {include file="Search/searchbox.tpl"}
              {/if}
            {/if}
            {include file="$module/$pageTemplate"}
          </div>
        </div>

        <div class="col-xs-11 col-sm-5 col-md-4 col-lg-3 sidebar-offcanvas sidebar print-hidden" id="sidebar" role="navigation">
          <button type="button" class="btn btn-danger btn-xs visible-xs visible-sm hide-sidebar" data-toggle="offcanvas"><span class="fa fa-times"></span> {translate text='Close'}</button>
          {assign var=sidebar value="`$smarty_template_dir`/`$module`/sidebar.tpl"}
          {if file_exists($sidebar)}
            {include file=$sidebar}
          {/if}
        </div>
      </div>
    {/if}
    <ul class="nav pull-right feedback-button">
      <li><a data-toggle="modal" data-target="#modal" href="{$path}/Feedback/Submit"><span class="fa fa-comment"></span> {translate text='Feedback'}</a></li>
    </ul>
    {* placeholder for all modal dialogs *}
    <div data-backdrop="false" class="modal fade ajax-modal" id="modal" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
        </div>
      </div>
    </div>
    <div id="spinModal" class="modal fade" tabindex="-1" role="dialog" data-keyboard="false" data-backdrop="false" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h4 class="modal-title">{translate text='Please wait'}...</h4>
          </div>
          <div class="modal-body" >
            <p>{translate text='long_wait_message'}</p>
            <div class="spinner">
              <span id="spinModalSpinner"></span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div> <!--/.container -->
  
  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
  <script src="//oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
  <script src="//oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
  <![endif]-->
  
  {if $gaId}  
  <script type="text/javascript">
      {literal}
      (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
       (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
       m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
       })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
      {/literal}
      ga('create', '{$gaId}', 'yorku.ca');
      ga('send', 'pageview');
  </script>
  {/if}
</body>
</html>

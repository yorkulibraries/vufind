<h1>{translate text='nohit_heading'}</h1>

<div class="no-hit-container">  
  <div class="alert alert-warning" role="alert">
    <p>{translate text='nohit_prefix'} - <strong>{$lookfor|escape:"html"}</strong> - {translate text='nohit_suffix'}</p>
  </div>

  {if $parseError}
  <div class="alert alert-danger" role="alert">
    <p>{translate text='nohit_parse_error'}</p>
  </div>
  {/if}

  {if $spellingSuggestions}
    <h2>{translate text='nohit_spelling'}:</h2>
  
    {foreach from=$spellingSuggestions item=details key=term name=termLoop}
    <p>
      <strong>{$term|escape}</strong> &raquo; 
      {foreach from=$details.suggestions item=data key=word name=suggestLoop}
        <a href="{$data.replace_url|escape}">{$word|escape}</a>{if !$smarty.foreach.suggestLoop.last}, {/if}
      {/foreach}
    </p>
    {/foreach}
  {/if}

  <p>You may want to try to revise your search phrase by removing some words for a more general search.</p>

  <h2>Looking for an article?</h2>
  
  <p>For more information on finding articles please consult the tutorial: <a href="http://researchguides.library.yorku.ca/journalarticles" target="_blank">Finding Journal Articles at York Libraries</a>.</p>
  
  <p>You may also find it helpful to start your research from a <a href="http://researchguides.library.yorku.ca/content.php?pid=220564" target="_blank">Subject Research Guides</a>.</p>
      
  <h2>If you need librarian assistance with your search:</h2>
  
  <ul>
    <li>In-person help is available at research or reference desks during <a href="http://www.library.yorku.ca/cms/library-hours/" target="_blank">reference hours</a>. </li>
    <li> <a href="http://www.library.yorku.ca/cms/askalibrarian2/" target="_blank">Live chat and IM help</a> is available.</li>
    <li><a href="http://www.library.yorku.ca/cms/help-with-research/askalibrarian/" target="_blank">E-mail brief research questions to us</a> and expect a response by 5 pm the next weekday.</li>
    <li>Phone us at a <a href="http://www.library.yorku.ca/cms/help-with-research/in-person-help/" target="_blank" >Library reference desk</a>.</li>
  </ul>

  <h2>RACER - Interlibrary Loan</h2>
  
  <p><a href="http://www.library.yorku.ca/cms/resourcesharing/services-for-york-faculty-and-students/" target="_blank">Make a request through interlibrary loan</a> (if York does not own the material you require, and it is not a textbook).  Please allow sufficient time for the material to arrive.  For more information please see the Interlibrary Loan Policy and Procedures.</p>

  <h2>Try Other Catalogues</h2>

  <div class="panel panel-default">
    <div class="panel-heading">
      <h3 class="panel-title">University of Toronto</h3>
    </div>
    <div class="panel-body">
      <form target="_blank" role="form" method="get" action="http://search.library.utoronto.ca/search">
        <input type="hidden" name="N" value="0"/>
        <input type="hidden" name="Ntx" value="mode matchallpartial"/>
        <input type="hidden" name="Nu" value="p_work_normalized"/>
        <input type="hidden" name="" value=""/>
        <label class="sr-only" for="search_toronto_lookfor">{translate text='Search Terms'}</label>
        <div class="input-group">
          <input type="text" name="Ntt" id="search_toronto" value="{$lookfor}" class="form-control"/>
          <div class="input-group-btn">
            <button type="submit" class="btn btn-default" tabindex="-1"><span class="fa fa-search"></span></button>
          </div>
        </div>
      </form>
    </div>
  </div>
  
  <div class="panel panel-default">
    <div class="panel-heading">
      <h3 class="panel-title">Ryerson University</h3>
    </div>
    <div class="panel-body">
      <form target="_blank" role="form" method="get" action="http://catalogue.library.ryerson.ca/search~S0/X">
        <label class="sr-only" for="search_ryerson_lookfor">{translate text='Search Terms'}</label>
        <div class="input-group">
          <input type="text" name="SEARCH" id="search_ryerson" value="{$lookfor}" class="form-control"/>
          <div class="input-group-btn">
            <button type="submit" class="btn btn-default" tabindex="-1"><span class="fa fa-search"></span></button>
          </div>
        </div>
      </form>
    </div>
  </div>
  
  <div class="panel panel-default">
    <div class="panel-heading">
      <h3 class="panel-title">WorldCat</h3>
    </div>
    <div class="panel-body">
      <form target="_blank" role="form" method="get" action="//www.worldcat.org/search">
        <label class="sr-only" for="search_worldcat_lookfor">{translate text='Search Terms'}</label>
        <div class="input-group">
          <input type="text" name="q" id="search_worldcat" value="{$lookfor}" class="form-control"/>
          <div class="input-group-btn">
            <button type="submit" class="btn btn-default" tabindex="-1"><span class="fa fa-search"></span></button>
          </div>
        </div>
      </form>
    </div>
  </div> 

  <h2>Try Google</h2>
  
  <div class="panel panel-default">
    <div class="panel-heading">
      <h3 class="panel-title">Google Scholar</h3>
    </div>
    <div class="panel-body">
      <form target="_blank" role="form" method="get" action="//scholar.google.ca/scholar">
        <label class="sr-only" for="google_scholar_lookfor">{translate text='Search Terms'}</label>
        <div class="input-group">
          <input type="text" name="q" id="google_scholar" value="{$lookfor}" class="form-control"/>
          <div class="input-group-btn">
            <button type="submit" class="btn btn-default" tabindex="-1"><span class="fa fa-search"></span></button>
          </div>
        </div>
      </form>
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-heading">
      <h3 class="panel-title">Google Books</h3>
    </div>
    <div class="panel-body">
      <form target="_blank" role="form" method="get" action="//books.google.ca/books">
        <label class="sr-only" for="google_books_lookfor">{translate text='Search Terms'}</label>
        <div class="input-group">
          <input type="text" name="q" id="google_books" value="{$lookfor}" class="form-control"/>
          <div class="input-group-btn">
            <button type="submit" class="btn btn-default" tabindex="-1"><span class="fa fa-search"></span></button>
          </div>
        </div>
      </form> 
    </div>
  </div>
</div>

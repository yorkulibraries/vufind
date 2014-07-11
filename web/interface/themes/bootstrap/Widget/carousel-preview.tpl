
<script type="text/javascript" src="{$url|replace:'https:':''|replace:'http:':''}/Widget/Carousel?id=t{$time}&amp;count=5&amp;{$searchUrlParams|escape}"></script>
<div title="{$carouselTitle|escape}" id="t{$time}"></div>

<div class="carousel-container">
<h2>Add the following HTML code into your webpage.</h2>
<pre  class="prettyprint lang-html" style="padding: 10px;">
&lt;script type=&quot;text/javascript&quot; src=&quot;{$url|replace:'https:':''|replace:'http:':''}/Widget/Carousel?id=t{$time}&amp;count=5&amp;{$searchUrlParams|escape}&quot;&gt;&lt;/script&gt;
&lt;div title=&quot;{$carouselTitle|escape}&quot; id=&quot;t{$time}&quot;&gt;&lt;/div&gt;
</pre>
<h2>If the above code does not work, you may need to add jQuery first.</h2>
{assign var=time value=$smarty.now}
<pre  class="prettyprint lang-html" style="padding: 10px;">
&lt;script src="//code.jquery.com/jquery-1.9.1.min.js"&gt;&lt;/script&gt;
</pre>
</div>


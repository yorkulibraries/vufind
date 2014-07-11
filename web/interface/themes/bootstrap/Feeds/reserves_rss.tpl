<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0">
  <channel>
    <title>{translate text='Course Reserves'}</title>
    <description></description>
    <link>{$url}/Search/Reserves</link>
  {if $resultTotal > 0}
    <item>
      <title>{$searchQuery|escape}</title>
      <description></description>
      <pubDate>Thu, 14 Jun 2012 15:11:55 +0000</pubDate>
      <link>{$searchUrl|escape}</link>
      <guid>{$searchUrl|escape}</guid>
    </item>
  {/if}
  </channel>
</rss>

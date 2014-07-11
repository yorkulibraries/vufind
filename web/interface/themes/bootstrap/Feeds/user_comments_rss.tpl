<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0">
  <channel>
    <title>{translate text='User Comments'}</title>
    <description></description>
    <link>{$url}/Feeds/UserComments</link>
  {foreach from=$commentList item=comment}
    <item>
      <title>{$comment->title|escape}</title>
      <description>{$comment->comment|escape}</description>
      <pubDate>{$comment->created|date_format:'%a, %d %b %Y %H:%M:%S %z'|escape}</pubDate>
      <link>{$url}/Record/{$comment->record_id|escape}#UserComments</link>
      <guid>{$url}/Record/{$comment->record_id|escape}#UserComments{$comment->id|escape}</guid>
    </item>
  {/foreach}
  </channel>
</rss>

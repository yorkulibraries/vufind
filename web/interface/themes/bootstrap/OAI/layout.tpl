<?xml version="1.0" encoding="UTF-8" ?>
<OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/" 
         xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/
         http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd">
  <responseDate>{$responseDate|escape}</responseDate>
  <request{if $requestParams}{foreach from=$requestParams key='name' item='value'} {$name|escape}="{$value|escape}"{/foreach}{/if}>{$requestURL|escape}</request>
  {include file="OAI/$pageTemplate"}
</OAI-PMH>
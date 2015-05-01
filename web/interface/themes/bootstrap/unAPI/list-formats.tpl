<?xml version="1.0" encoding="UTF-8" ?>
<formats {if $id}id="{$id}"{/if}>
  {foreach from=$formats key=formatName item=formatType}
  <format name="{$formatName}" type="{$formatType}"/>
  {/foreach}
</formats>
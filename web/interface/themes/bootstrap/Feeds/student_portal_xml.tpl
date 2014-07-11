<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE response [
  <!ELEMENT response  (html)*>
  <!ELEMENT html  ( #PCDATA )>
  <!ATTLIST html lang CDATA #REQUIRED>
]>
<response>
  {if !empty($html)}
    {foreach from=$html key=lang item=html}
      {$html}
    {/foreach}
  {/if}
</response>
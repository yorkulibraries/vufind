{assign var=date_format value='%a %b %d %H:%M:%S %Z %Y'}
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE response [
  <!ELEMENT response  ( status | timestamp | user_info | loans | holds | fines | html)*>
  <!ELEMENT status  ( #PCDATA )>
  <!ELEMENT timestamp  ( #PCDATA )>
  <!ELEMENT user_info  ( id | email | name | user_type | date_privilege_expires )*>
  <!ELEMENT email  ( #PCDATA )>
  <!ELEMENT name  ( #PCDATA )>
  <!ELEMENT user_type  ( #PCDATA )>
  <!ELEMENT date_privilege_expires  ( #PCDATA )>
  <!ELEMENT loans  ( item )*>
  <!ELEMENT item  ( id | title | dates | is_overdue | item_available | amount_billed | bill_reason )*>
  <!ELEMENT id  ( #PCDATA )>
  <!ELEMENT title  ( #PCDATA )>
  <!ELEMENT dates  ( loan_date | due_date | date_placed | date_expires | date_billed )*>
  <!ELEMENT loan_date  ( #PCDATA )>
  <!ELEMENT due_date  ( #PCDATA )>
  <!ELEMENT is_overdue  ( #PCDATA )>
  <!ELEMENT holds  ( item )>
  <!ELEMENT date_placed  ( #PCDATA )>
  <!ELEMENT date_expires  ( #PCDATA )>
  <!ELEMENT item_available  ( #PCDATA )>
  <!ELEMENT fines  ( item )*>
  <!ELEMENT date_billed  ( #PCDATA )>
  <!ELEMENT amount_billed  ( #PCDATA )>
  <!ELEMENT bill_reason  ( #PCDATA )>
  <!ELEMENT html  ( #PCDATA )>
  <!ATTLIST html lang CDATA #REQUIRED>
]>
<response>
  <status>{$status}</status>
  <timestamp>{$smarty.now|date_format:$date_format}</timestamp>
  
  {if !empty($html)}
    {foreach from=$html key=lang item=html}
      {$html}
    {/foreach}
  {/if}
  
  {* the following are here for backward compatibility *}
  <user_info>
    <email>{$patron.email|escape}</email>
    <id>{$patron.alt_id|escape}</id>
    <name>{$patron.name|escape}</name>
    <user_type>{$patron.profile|escape}</user_type>
    <date_privilege_expires></date_privilege_expires>
  </user_info>
  {if !empty($loans)}
  <loans>
    {foreach from=$loans item=item}
    <item>
      <id></id>
      <title><![CDATA[{$item.title} ]]></title>
      <dates>
        <loan_date>{$item.date_charged}</loan_date>
        <due_date>{if !empty($item.recall_duedate)}{$item.recall_duedate}{else}{$item.duedate}{/if}</due_date>
      </dates>
      <is_overdue>{if $item.overdue=='Y'}true{else}false{/if}</is_overdue>
    </item>
    {/foreach}
  </loans>
  {/if}
  {if !empty($holds)}
  <holds>
    {foreach from=$holds item=item}
    <item>
      <title><![CDATA[{$item.title} ]]></title>
      <dates>
        <date_placed>{$item.ils_details.create}</date_placed>
        <date_expires>{if $item.ils_details.date_available_expires}{$item.ils_details.date_available_expires}{/if}</date_expires>
      </dates>
      <item_available>{if $item.ils_details.available=='Y'}true{else}false{/if}</item_available>
    </item>
    {/foreach}
  </holds>
  {/if}
  {if !empty($fines)}
  <fines>
    {foreach from=$fines item=item}
    <item>
      <title><![CDATA[{if empty($item.title)}N/A{else}{$item.title}{/if} ]]></title>
      <dates>
        <date_billed>{$item.date_billed}</date_billed>
      </dates>
      <amount_billed>{$item.balance}</amount_billed>
      <bill_reason>{$item.fine}</bill_reason>
    </item>
    {/foreach}
  </fines>
  {/if}
</response>
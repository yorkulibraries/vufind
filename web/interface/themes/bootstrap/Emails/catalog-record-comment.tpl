{* This is a text-only email template; do not include HTML! *}
{translate text='Catalog Record Link'}:
{$url}/Record/{$recordID|escape:"url"}

{translate text='Patron Information'}:
==================================================================
{translate text='Name'}: {$patron.lastname}, {$patron.firstname} 
{translate text='ID'}: {$patron.barcode}
{translate text='Email'}: {$profile.email}

{translate text='Comments'}:
==================================================================
{$comment}

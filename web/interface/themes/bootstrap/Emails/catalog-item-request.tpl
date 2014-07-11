{translate text="$requestType Request"}

{translate text='Title'}: {$record.title_full}
{translate text='pick_up_location'}: {$pickUpLocation}
{translate text='Call Number'}: {$requestedItem.callnumber}
{translate text='Item ID'}: {$requestedItem.barcode}
{translate text="hold_required_by"}: {$requiredBy}
{translate text="email_link"}: {$url}/Record/{$requestedItem.id}

{translate text='Patron Information'}:
==================================================================
{translate text='Name'}: {$patron.lastname}, {$patron.firstname} 
{translate text='ID'}: {$patron.barcode}
{translate text='Email'}: {$profile.email}

{translate text='Comments'}:
==================================================================
{$comment}

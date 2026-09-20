@component('mail::message')
# Your place is confirmed

Thank you — your place at **{{ $title }}** is held. Your code is **{{ $code }}**.

Add it to your calendar:

@component('mail::button', ['url' => $googleUrl])
Add to Google Calendar
@endcomponent

Or download it for Apple Calendar, Outlook and the rest:
[{{ $icsUrl }}]({{ $icsUrl }})

See you in October,
Asociația Prin Banat
@endcomponent

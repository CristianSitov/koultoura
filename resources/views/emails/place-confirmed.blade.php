@component('mail::message')
# Your place is confirmed

Thank you — your place at **{{ $title }}** is held.

**{{ $when }}**

Your place code: **{{ $code }}**

@if($description)
## About the workshop

{!! $description !!}
@endif

Add it to your calendar:

@component('mail::button', ['url' => $googleUrl])
Add to Google Calendar
@endcomponent

Or download it for Apple Calendar, Outlook and the rest:
[{{ $icsUrl }}]({{ $icsUrl }})

See you soon,
Asociația Prin Banat
@endcomponent

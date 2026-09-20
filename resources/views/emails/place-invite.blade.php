@component('mail::message')
# You have a place at Why Culture Matters 2026

You are invited to **{{ $title }}**, an internal session at the symposium.

Your place code is **{{ $code }}**.

Please confirm so we know to keep it for you:

@component('mail::button', ['url' => $confirmUrl])
Confirm my place
@endcomponent

If the button does not work, paste this into your browser:
[{{ $confirmUrl }}]({{ $confirmUrl }})

If this was not meant for you, ignore this email and nothing further will happen.

See you in October,
Asociația Prin Banat
@endcomponent

@component('mail::message')
# {{ __('Almost there, :name', ['name' => $registration->name]) }}

{{ __('Thank you for registering for Why Culture Matters 2026, 7–10 October in Timișoara. One step is left: confirm this address so we know we can reach you.') }}

@component('mail::button', ['url' => $confirmUrl])
{{ __('Confirm my registration') }}
@endcomponent

{{ __('If the button does not work, paste this into your browser:') }}
[{{ $confirmUrl }}]({{ $confirmUrl }})

@if ($registration->workshop_interest)
{{ __('You said you would like a place in a Heritage School workshop. Places are limited and allocated by hand — we will write again about that separately.') }}
@endif

{{ __('If you did not register, ignore this email and nothing further will happen.') }}

{{ __('See you in October,') }}
{{ __('Asociația Prin Banat') }}
@endcomponent

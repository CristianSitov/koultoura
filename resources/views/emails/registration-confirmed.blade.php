@component('mail::message')
# {{ __('You are registered, :name', ['name' => $registration->name]) }}

{!! __('confirmed.email.intro') !!}

{!! __('confirmed.email.calendar') !!}

[{{ __('Add to Google Calendar') }}]({{ $googleCalendarUrl }})

{{ __('See you in October,') }}
{{ __('Asociația Prin Banat') }}
@endcomponent

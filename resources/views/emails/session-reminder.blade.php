@component('mail::message')
# {{ $title }}

{{ __('booking.email.hello', ['name' => $name]) }}

{!! __('booking.reminder.intro', ['title' => $title, 'date' => $date, 'time' => $time]) !!}

@if($brief)
{!! $brief !!}
@endif

@component('mail::button', ['url' => $url])
{{ __('See your booking') }}
@endcomponent

{{ __('booking.reminder.cannot', ['email' => $contact]) }}

@if($description)
## {{ $tour ? __('About the tour') : __('About the workshop') }}

{!! $description !!}
@endif

{{ __('See you soon,') }}
{{ __('Asociația Prin Banat') }}
@endcomponent

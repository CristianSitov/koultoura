@component('mail::message')
# {{ $title }}

{{ $name ? __('booking.email.hello', ['name' => $name]) : __('day.email.hello') }}

{!! __('booking.reminder.intro', ['title' => $title, 'date' => $date, 'time' => $time]) !!}

@if($brief)
{!! $brief !!}
@endif

@if($code)
{{ __('Your place code:') }} **{{ $code }}**

@endif
@if($url)
@component('mail::button', ['url' => $url])
{{ __('See your booking') }}
@endcomponent
@endif

{{ __('booking.reminder.cannot', ['email' => $contact]) }}

@if($description)
## {{ $tour ? __('About the tour') : __('About the workshop') }}

{!! $description !!}
@endif

{{ __('See you soon,') }}
{{ __('Asociația Prin Banat') }}
@endcomponent

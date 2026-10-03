@component('mail::message')
# {{ __('booking.email.hello', ['name' => $name]) }}

{!! __('booking.email.held', ['title' => $title, 'date' => $date, 'time' => $time]) !!}

@component('mail::button', ['url' => $url])
{{ __('See your booking') }}
@endcomponent

{{ __('Changed your mind? Write to us and we will free the place for someone else.') }}

{{ __('See you in October,') }}
{{ __('Asociația Prin Banat') }}
@endcomponent

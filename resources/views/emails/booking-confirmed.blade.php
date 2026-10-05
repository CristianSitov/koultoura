@component('mail::message')
# {{ __('booking.email.hello', ['name' => $name]) }}

{!! __('booking.email.held', ['title' => $title, 'date' => $date, 'time' => $time]) !!}

{{ __('Please confirm you are coming, or release the place so someone else can take it.') }}

@component('mail::button', ['url' => $url])
{{ __('Confirm or release my place') }}
@endcomponent

{{ __('See you soon,') }}
{{ __('Asociația Prin Banat') }}
@endcomponent

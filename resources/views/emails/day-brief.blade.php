@component('mail::message')
{!! $body !!}

@component('mail::button', ['url' => $url])
{{ __('See the day’s programme') }}
@endcomponent

{{ __('See you tomorrow,') }}
{{ __('Asociația Prin Banat') }}
@endcomponent

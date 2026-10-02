@component('mail::message')
# {{ $date }}@if($theme) — {{ $theme }}@endif


@if($name)
{{ __('day.email.hello_name', ['name' => $name]) }}
@else
{{ __('day.email.hello') }}
@endif

{{ __('day.email.intro', ['n' => $n]) }}

@if($brief)
{!! $brief !!}
@endif

@component('mail::button', ['url' => $url])
{{ __('See the day’s programme') }}
@endcomponent

{{ __('See you tomorrow,') }}
{{ __('Asociația Prin Banat') }}
@endcomponent

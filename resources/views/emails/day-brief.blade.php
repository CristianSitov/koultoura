@component('mail::message')
# {{ $date }}@if($theme) — {{ $theme }}@endif


@if($name)
{{ __('day.email.hello_name', ['name' => $name]) }}
@else
{{ __('day.email.hello') }}
@endif

{{-- Without the day's text, no "here is what to expect" pointing at nothing. --}}
@if($brief)
{{ __('day.email.intro', ['n' => $n]) }}

{!! $brief !!}
@else
{{ __('day.email.intro_plain', ['n' => $n]) }}
@endif

@component('mail::button', ['url' => $url])
{{ __('See the day’s programme') }}
@endcomponent

{{ __('See you tomorrow,') }}
{{ __('Asociația Prin Banat') }}
@endcomponent

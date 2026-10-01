@component('mail::message')
# {{ __('Hello :name,', ['name' => $name]) }}

{{ __($update ? 'agenda.email.update' : 'agenda.email.intro') }}

@component('mail::button', ['url' => $url])
{{ __('Open my programme') }}
@endcomponent

{{ __('agenda.email.private') }}

{{ __('See you in October,') }}
{{ __('Asociația Prin Banat') }}
@endcomponent

@component('mail::message')
<h2>{!! __('hello', ['name' => $user['name']]) !!}</h2>
<p>{!! __('thank you title') !!}</p>
<p>{!! __('thank you email subscription') !!}</p>

@foreach ($profile['event_details']->days as $day)
@component('mail::panel')
<div>
<h3>{{ __('Day :day', ['day' => $day]) }} &mdash; 6 oct &mdash; {{ __('schedule short '.$day.' title') }}</h3>
<div>
<span>{{ __('schedule short '.$day.' hours') }}</span>
<span>{{ __('schedule short '.$day.' location') }}</span>
</div>
</div>
@endcomponent
@endforeach

<p>{!! __('thank you closing') !!}</p>
<p>{!! __('thank you footer', ['home' => route('home')]) !!}</p>
<p>{{ __('Regards') }}<br />{{ __('association') }}</p>

@endcomponent

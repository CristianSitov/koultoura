@component('mail::message')
<h2>{!! __('hello', ['name' => $user['name']]) !!}</h2>
<p>{!! __('thank you title') !!}</p>
<p>{!! __('thank you email subscription') !!}</p>

@if(in_array('1', $profile['event_details']->days))
@component('mail::panel')
<div>
    <h3>{{ __('Day :day', ['day' => 1]) }} &mdash; 6 oct &mdash; {{ __('schedule short one title') }}</h3>
    <div>
        <span>{{ __('schedule short one hours') }}</span>
        <span>{{ __('schedule short one location') }}</span>
    </div>
</div>
@endcomponent
@endif

@if(in_array('2', $profile['event_details']->days))
@component('mail::panel')
<div>
    <h3>{{ __('Day :day', ['day' => 2]) }} &mdash; 7 oct &mdash; {{ __('schedule short two title') }}</h3>
    <div>
        <span class="">{{ __('schedule short two hours') }}</span><br />
        <span class="">{{ __('schedule short two location') }}</span>
    </div>
</div>
@endif
@endcomponent

@if(in_array('3', $profile['event_details']->days))
@component('mail::panel')
<div>
    <h3>{{ __('Day :day', ['day' => 3]) }} &mdash; 8 oct &mdash; {{ __('schedule short three title') }}</h3>
    <div>
        <span>{{ __('schedule short three hours') }}</span><br />
        <span>{{ __('schedule short three location') }}</span>
    </div>
</div>
@endcomponent
@endif

<p>{!! __('thank you email subscription') !!}</p>
<p>{!! __('thank you closing') !!}</p>
<p>{!! __('thank you footer') !!}</p>
<p>{{ __('Regards') }}<br />{{ __('association') }}</p>

@endcomponent

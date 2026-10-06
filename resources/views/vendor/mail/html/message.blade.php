{{--
    The framework's email frame, with one change: the name at the top and in
    the footer is the event's name as it is written (config('wcm.name'),
    "Why Culture Matters"), not the site's internal APP_NAME — which also
    names the login cookie, so it is left as it is. The sender's name
    (MAIL_FROM_NAME) is set apart, with its "- No Reply".
--}}
<x-mail::layout>
{{-- Header --}}
<x-slot:header>
<x-mail::header :url="config('app.url')">
{{ config('wcm.name') }}
</x-mail::header>
</x-slot:header>

{{-- Body --}}
{{ $slot }}

{{-- Subcopy --}}
@isset($subcopy)
<x-slot:subcopy>
<x-mail::subcopy>
{{ $subcopy }}
</x-mail::subcopy>
</x-slot:subcopy>
@endisset

{{-- Footer --}}
<x-slot:footer>
<x-mail::footer>
© {{ date('Y') }} {{ config('wcm.name') }}. {{ __('All rights reserved.') }}
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>

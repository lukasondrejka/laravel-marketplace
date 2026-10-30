<x-mail::message>
# {{ __('Someone is interested in your listing') }}

**{{ $safeName }}** ({{ $safeEmail }}) {{ __('wrote about') }} **{{ $safeTitle }}**:

<x-mail::panel>
{{ $safeBody }}
</x-mail::panel>

{{ __('Reply to this email to answer directly.') }}

<x-mail::button :url="route('listings.show', $listing)">
{{ __('View listing') }}
</x-mail::button>
</x-mail::message>

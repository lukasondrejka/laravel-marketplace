<x-mail::message>
# {{ __('Someone is interested in your listing') }}

**{{ $senderName }}** ({{ $senderEmail }}) {{ __('wrote about') }} **{{ $listing->title }}**:

<x-mail::panel>
{{ $body }}
</x-mail::panel>

{{ __('Reply to this email to answer directly.') }}

<x-mail::button :url="route('listings.show', $listing)">
{{ __('View listing') }}
</x-mail::button>
</x-mail::message>

@php
    $providers = collect(\App\Http\Controllers\Auth\SocialiteController::PROVIDERS)->filter(fn ($p) => config("services.$p.client_id"));
@endphp
@if ($providers->isNotEmpty())
    <div class="divider text-xs text-base-content/50">{{ __('or') }}</div>
    <div class="grid gap-2">
        @foreach ($providers as $provider)
            <a href="{{ route('social.redirect', $provider) }}" class="btn w-full">{{ __('Continue with :provider', ['provider' => ucfirst($provider)]) }}</a>
        @endforeach
    </div>
@endif

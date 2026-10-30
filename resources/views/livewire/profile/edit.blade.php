<div class="max-w-3xl mx-auto px-4 py-8 space-y-6">
    <x-page-header :title="__('Profile')" :subtitle="__('Manage your account information.')" />

    @unless (auth()->user()->hasVerifiedEmail())
        <x-alert type="warning">
            {{ __('Your email address is not verified. You must verify it before posting listings.') }}
            <form method="POST" action="{{ route('verification.send') }}" class="inline">
                @csrf
                <button class="link font-medium">{{ __('Resend verification email') }}</button>
            </form>
        </x-alert>
    @endunless

    <livewire:profile.update-information />
    <livewire:profile.update-password />
    <livewire:profile.delete-account />
</div>

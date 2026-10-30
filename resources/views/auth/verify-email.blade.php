<x-layouts.app :title="__('Verify your email')">
    <x-auth-card :title="__('Verify your email')" :subtitle="__('Before posting listings, confirm your address by clicking the link we just emailed you.')">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-button type="submit" variant="primary" class="w-full">{{ __('Resend verification email') }}</x-button>
        </form>
        <form method="POST" action="{{ route('logout') }}" class="text-center">
            @csrf
            <button type="submit" class="link text-sm">{{ __('Log out') }}</button>
        </form>
    </x-auth-card>
</x-layouts.app>

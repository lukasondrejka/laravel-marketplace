<x-layouts.app :title="__('Confirm password')">
    <x-auth-card :title="__('Confirm your password')" :subtitle="__('This is a secure area. Please confirm your password to continue.')">
        <form method="POST" action="{{ route('password.confirm') }}" class="space-y-3">
            @csrf
            <x-input name="password" type="password" :label="__('Password')" required autofocus autocomplete="current-password" />
            <x-button type="submit" variant="primary" class="w-full">{{ __('Confirm') }}</x-button>
        </form>
    </x-auth-card>
</x-layouts.app>

<x-layouts.app :title="__('Reset password')">
    <x-auth-card :title="__('Choose a new password')">
        <form method="POST" action="{{ route('password.update') }}" class="space-y-3">
            @csrf
            <input type="hidden" name="token" value="{{ $request->route('token') }}">
            <x-input name="email" type="email" :label="__('Email')" :value="old('email', $request->email)" required />
            <x-input name="password" type="password" :label="__('New password')" required autocomplete="new-password" />
            <x-input name="password_confirmation" type="password" :label="__('Confirm password')" required autocomplete="new-password" />
            <x-button type="submit" variant="primary" class="w-full">{{ __('Reset password') }}</x-button>
        </form>
    </x-auth-card>
</x-layouts.app>

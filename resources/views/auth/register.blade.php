<x-layouts.app :title="__('Sign up')">
    <x-auth-card :title="__('Create your account')" :subtitle="__('It takes less than a minute.')">
        <form method="POST" action="{{ route('register') }}" class="space-y-3">
            @csrf
            <x-input name="name" :label="__('Name')" :value="old('name')" required autofocus autocomplete="name" />
            <x-input name="email" type="email" :label="__('Email')" :value="old('email')" required autocomplete="username" />
            <x-input name="password" type="password" :label="__('Password')" required autocomplete="new-password" />
            <x-input name="password_confirmation" type="password" :label="__('Confirm password')" required autocomplete="new-password" />
            <x-button type="submit" variant="primary" class="w-full">{{ __('Sign up') }}</x-button>
        </form>
        <x-social-login />
        <p class="text-sm text-center mt-2">{{ __('Already have an account?') }} <a href="{{ route('login') }}" class="link link-primary">{{ __('Log in') }}</a></p>
    </x-auth-card>
</x-layouts.app>

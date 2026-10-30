<x-layouts.app :title="__('Forgot password')">
    <x-auth-card :title="__('Forgot your password?')" :subtitle="__('Enter your email and we will send you a reset link.')">
        <form method="POST" action="{{ route('password.email') }}" class="space-y-3">
            @csrf
            <x-input name="email" type="email" :label="__('Email')" :value="old('email')" required autofocus />
            <x-button type="submit" variant="primary" class="w-full">{{ __('Email reset link') }}</x-button>
        </form>
        <p class="text-sm text-center mt-2"><a href="{{ route('login') }}" class="link">{{ __('Back to log in') }}</a></p>
    </x-auth-card>
</x-layouts.app>

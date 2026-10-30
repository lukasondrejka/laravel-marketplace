<x-layouts.app :title="__('Log in')">
    <x-auth-card :title="__('Welcome back')" :subtitle="__('Log in to manage your listings.')">
        <form method="POST" action="{{ route('login') }}" class="space-y-3">
            @csrf
            <x-input name="email" type="email" :label="__('Email')" :value="old('email')" required autofocus autocomplete="username" />
            <x-input name="password" type="password" :label="__('Password')" required autocomplete="current-password" />
            <label class="label cursor-pointer justify-start gap-2">
                <input type="checkbox" name="remember" class="checkbox checkbox-sm">
                <span class="label-text">{{ __('Remember me') }}</span>
            </label>
            <x-button type="submit" variant="primary" class="w-full">{{ __('Log in') }}</x-button>
        </form>
        <x-social-login />
        <div class="text-sm text-center space-y-1 mt-2">
            <p><a href="{{ route('password.request') }}" class="link">{{ __('Forgot your password?') }}</a></p>
            <p>{{ __("Don't have an account?") }} <a href="{{ route('register') }}" class="link link-primary">{{ __('Sign up') }}</a></p>
        </div>
    </x-auth-card>
</x-layouts.app>

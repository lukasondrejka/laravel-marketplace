<x-layouts.app :title="__('Page expired')">
    <div class="max-w-xl mx-auto px-4 py-20 text-center">
        <p class="text-7xl font-extrabold text-primary">419</p>
        <h1 class="text-2xl font-bold mt-4">{{ __('Page expired') }}</h1>
        <p class="text-base-content/70 mt-2">{{ __('Your session expired. Please go back, refresh and try again.') }}</p>
        <div class="mt-6">
            <x-button :href="route('home')" variant="primary">{{ __('Back to home') }}</x-button>
        </div>
    </div>
</x-layouts.app>

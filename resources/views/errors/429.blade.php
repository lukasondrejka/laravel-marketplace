<x-layouts.app :title="__('Too many requests')">
    <div class="max-w-xl mx-auto px-4 py-20 text-center">
        <p class="text-7xl font-extrabold text-primary">429</p>
        <h1 class="text-2xl font-bold mt-4">{{ __('Too many requests') }}</h1>
        <p class="text-base-content/70 mt-2">{{ __('Slow down a little and try again in a moment.') }}</p>
        <div class="mt-6">
            <x-button :href="route('home')" variant="primary">{{ __('Back to home') }}</x-button>
        </div>
    </div>
</x-layouts.app>

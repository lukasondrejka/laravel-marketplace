<x-layouts.app :title="__('Down for maintenance')">
    <div class="max-w-xl mx-auto px-4 py-20 text-center">
        <p class="text-7xl font-extrabold text-primary">503</p>
        <h1 class="text-2xl font-bold mt-4">{{ __('Down for maintenance') }}</h1>
        <p class="text-base-content/70 mt-2">{{ __('We will be back shortly.') }}</p>
        <div class="mt-6">
            <x-button :href="route('home')" variant="primary">{{ __('Back to home') }}</x-button>
        </div>
    </div>
</x-layouts.app>

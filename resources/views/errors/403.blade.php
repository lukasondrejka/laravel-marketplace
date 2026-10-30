<x-layouts.app :title="__('Not allowed')">
    <div class="max-w-xl mx-auto px-4 py-20 text-center">
        <p class="text-7xl font-extrabold text-primary">403</p>
        <h1 class="text-2xl font-bold mt-4">{{ __('Not allowed') }}</h1>
        <p class="text-base-content/70 mt-2">{{ __('You do not have permission to view this page.') }}</p>
        <div class="mt-6">
            <x-button :href="route('home')" variant="primary">{{ __('Back to home') }}</x-button>
        </div>
    </div>
</x-layouts.app>

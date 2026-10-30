<x-layouts.app :title="__('Page not found')">
    <div class="max-w-xl mx-auto px-4 py-20 text-center">
        <p class="text-7xl font-extrabold text-primary">404</p>
        <h1 class="text-2xl font-bold mt-4">{{ __('Page not found') }}</h1>
        <p class="text-base-content/70 mt-2">{{ __('The page you are looking for does not exist or was removed.') }}</p>
        <div class="mt-6">
            <x-button :href="route('home')" variant="primary">{{ __('Back to home') }}</x-button>
        </div>
    </div>
</x-layouts.app>

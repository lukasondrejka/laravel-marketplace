<footer class="bg-base-100 border-t border-base-300 mt-16">
    <div class="max-w-6xl mx-auto px-4 py-6 flex flex-wrap items-center justify-between gap-3 text-sm text-base-content/60">
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}</p>
        <nav class="flex gap-4" aria-label="{{ __('Footer') }}">
            <a href="{{ route('listings.index') }}" wire:navigate class="hover:text-base-content">{{ __('Browse listings') }}</a>
            <a href="{{ route('listings.create') }}" wire:navigate class="hover:text-base-content">{{ __('Post a listing') }}</a>
        </nav>
    </div>
</footer>

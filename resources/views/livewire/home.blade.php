<div>
    <section class="bg-base-100 border-b border-base-300">
        <div class="max-w-6xl mx-auto px-4 py-12 sm:py-16">
            <h1 class="text-3xl sm:text-4xl font-semibold tracking-tight">{{ __('Buy and sell locally') }}</h1>
            <p class="mt-2 text-base-content/60 max-w-xl">{{ __('Find second-hand goods near you or post your own listing for free.') }}</p>
            <form action="{{ route('listings.index') }}" method="GET" role="search" class="mt-6 flex flex-col sm:flex-row gap-2 max-w-2xl">
                <input type="search" name="q" placeholder="{{ __('What are you looking for?') }}" aria-label="{{ __('Search listings') }}" class="input input-lg w-full">
                <button type="submit" class="btn btn-primary btn-lg">{{ __('Search') }}</button>
            </form>
        </div>
    </section>

    <div class="max-w-6xl mx-auto px-4 py-10 space-y-12">
        <section aria-labelledby="categories-heading">
            <h2 id="categories-heading" class="text-lg font-semibold mb-3">{{ __('Categories') }}</h2>
            <ul class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-x-6 gap-y-1 bg-base-100 border border-base-300 rounded-box p-3">
                @foreach ($this->categories as $category)
                    <li>
                        <a href="{{ route('listings.index', ['category' => $category->slug]) }}" wire:navigate class="flex items-baseline justify-between gap-2 rounded px-2 py-1.5 text-sm hover:bg-base-200">
                            <span>{{ $category->name }}</span>
                            <span class="text-xs text-base-content/50">{{ $category->listings_count }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </section>

        <section aria-labelledby="latest-heading">
            <div class="flex items-baseline justify-between mb-3">
                <h2 id="latest-heading" class="text-lg font-semibold">{{ __('Latest listings') }}</h2>
                <a href="{{ route('listings.index') }}" wire:navigate class="text-sm link link-primary no-underline hover:underline">{{ __('View all') }}</a>
            </div>
            @if ($this->latest->isEmpty())
                <x-alert type="info">{{ __('No listings yet. Be the first to post one!') }}</x-alert>
            @else
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    @foreach ($this->latest as $listing)
                        <x-listing-card :listing="$listing" wire:key="latest-{{ $listing->id }}" />
                    @endforeach
                </div>
            @endif
        </section>
    </div>
</div>

<div class="max-w-6xl mx-auto px-4 py-8 space-y-6">
    <x-card>
        <div class="flex items-center gap-4">
            <x-avatar :user="$seller" class="size-16 text-lg" />
            <div class="min-w-0">
                <h1 class="text-xl font-semibold">{{ $seller->name }}</h1>
                <p class="text-sm text-base-content/60">
                    @if ($seller->city){{ $seller->city }} &middot; @endif
                    {{ __('Member since :date', ['date' => $seller->created_at->translatedFormat('M Y')]) }}
                </p>
            </div>
        </div>
        @if ($seller->bio)
            <p class="text-base-content/80 whitespace-pre-line">{{ $seller->bio }}</p>
        @endif
    </x-card>

    <section aria-labelledby="seller-listings">
        <h2 id="seller-listings" class="text-lg font-semibold mb-3">{{ __('Listings by :name', ['name' => $seller->name]) }} <span class="text-base-content/50 font-normal">({{ $this->listings->total() }})</span></h2>
        @if ($this->listings->isEmpty())
            <x-alert type="info">{{ __('This seller has no active listings.') }}</x-alert>
        @else
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach ($this->listings as $listing)
                    <x-listing-card :listing="$listing" wire:key="s-{{ $listing->id }}" />
                @endforeach
            </div>
            {{ $this->listings->links() }}
        @endif
    </section>
</div>

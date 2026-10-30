@php
    $images = $listing->getMedia(\App\Models\Listing::IMAGES)->map(fn ($m) => ['large' => $m->getUrl('large'), 'thumb' => $m->getUrl('thumb'), 'alt' => $listing->title])->values();
    $seller = $listing->user;
    $phone = $listing->contactPhone();
@endphp
@push('head')
    <script type="application/ld+json">{!! json_encode($this->structuredData(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
@endpush
<div class="max-w-6xl mx-auto px-4 py-6">
    <nav class="text-sm text-base-content/60 mb-4 flex flex-wrap gap-x-2" aria-label="{{ __('Breadcrumb') }}">
        <a href="{{ route('home') }}" wire:navigate class="hover:text-base-content">{{ __('Home') }}</a><span aria-hidden="true">/</span>
        <a href="{{ route('listings.index', ['category' => $listing->category->slug]) }}" wire:navigate class="hover:text-base-content">{{ $listing->category->name }}</a><span aria-hidden="true">/</span>
        <span class="truncate max-w-64 text-base-content">{{ $listing->title }}</span>
    </nav>

    @if ($listing->status === \App\Enums\ListingStatus::Draft)
        <x-alert type="warning" class="mb-4">{{ __('This listing is a draft and only visible to you.') }}</x-alert>
    @elseif ($listing->status === \App\Enums\ListingStatus::Sold)
        <x-alert type="info" class="mb-4">{{ __('This item has been sold.') }}</x-alert>
    @endif

    <div class="grid lg:grid-cols-[1fr_21rem] gap-6 items-start">
        <div class="space-y-6 min-w-0">
            {{-- Gallery with lightbox --}}
            <div x-data="{ images: @js($images), current: 0, zoom: false,
                    next() { this.current = (this.current + 1) % this.images.length },
                    prev() { this.current = (this.current - 1 + this.images.length) % this.images.length } }"
                 @keydown.right.window="(zoom || images.length > 1) && next()" @keydown.left.window="(zoom || images.length > 1) && prev()"
                 @keydown.escape.window="zoom = false"
                 class="bg-base-100 border border-base-300 rounded-box overflow-hidden">
                <template x-if="images.length">
                    <div>
                        <div class="relative aspect-4/3 bg-base-200">
                            <template x-for="(image, i) in images" :key="i">
                                <img data-testid="gallery-image" x-show="current === i" @click="zoom = true" :src="image.large" :alt="image.alt" class="absolute inset-0 size-full object-contain cursor-zoom-in" :loading="i === 0 ? 'eager' : 'lazy'">
                            </template>
                            <template x-if="images.length > 1">
                                <div>
                                    <button type="button" @click="prev()" class="btn btn-circle btn-sm absolute left-3 top-1/2 -translate-y-1/2" aria-label="{{ __('Previous image') }}"><x-icon name="chevron-left" class="size-4" /></button>
                                    <button type="button" @click="next()" class="btn btn-circle btn-sm absolute right-3 top-1/2 -translate-y-1/2" aria-label="{{ __('Next image') }}"><x-icon name="chevron-right" class="size-4" /></button>
                                    <span data-testid="gallery-counter" class="absolute bottom-3 right-3 rounded bg-black/60 px-2 py-0.5 text-xs text-white" x-text="(current + 1) + ' / ' + images.length"></span>
                                </div>
                            </template>
                        </div>
                        <div class="flex gap-2 p-3 overflow-x-auto border-t border-base-300" x-show="images.length > 1">
                            <template x-for="(image, i) in images" :key="'t' + i">
                                <button type="button" @click="current = i" class="shrink-0 rounded overflow-hidden border-2 transition-opacity" :class="current === i ? 'border-primary' : 'border-transparent opacity-60 hover:opacity-100'" :aria-label="'{{ __('Show image') }} ' + (i + 1)">
                                    <img :src="image.thumb" alt="" class="h-14 w-[4.5rem] object-cover">
                                </button>
                            </template>
                        </div>

                        {{-- Lightbox --}}
                        <div data-testid="lightbox" x-show="zoom" x-cloak x-transition.opacity class="fixed inset-0 z-50 bg-black/90 flex items-center justify-center p-4" @click.self="zoom = false" role="dialog" aria-modal="true">
                            <img :src="images[current].large" :alt="images[current].alt" class="max-h-full max-w-full object-contain">
                            <button type="button" data-testid="lightbox-close" @click="zoom = false" class="absolute top-4 right-4 btn btn-circle btn-ghost text-white" aria-label="{{ __('Close') }}"><x-icon name="x-mark" class="size-6" /></button>
                            <template x-if="images.length > 1">
                                <div>
                                    <button type="button" @click="prev()" class="absolute left-4 top-1/2 -translate-y-1/2 btn btn-circle btn-ghost text-white" aria-label="{{ __('Previous image') }}"><x-icon name="chevron-left" class="size-6" /></button>
                                    <button type="button" data-testid="lightbox-next" @click="next()" class="absolute right-4 top-1/2 -translate-y-1/2 btn btn-circle btn-ghost text-white" aria-label="{{ __('Next image') }}"><x-icon name="chevron-right" class="size-6" /></button>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
                <template x-if="!images.length">
                    <div class="aspect-4/3 flex flex-col items-center justify-center text-base-content/40 bg-base-200">
                        <x-icon name="photo" class="size-16" />
                        <span class="mt-2 text-sm">{{ __('No photos') }}</span>
                    </div>
                </template>
            </div>

            <x-card :title="__('Description')">
                <div class="leading-relaxed break-words">{{ $listing->rich_description }}</div>
                <p class="text-sm text-base-content/60 pt-2 border-t border-base-300">
                    {{ __('Posted :date', ['date' => ($listing->published_at ?? $listing->created_at)->translatedFormat('j M Y')]) }}
                    @if ($listing->updated_at->gt(($listing->published_at ?? $listing->created_at)->addMinute()))
                        &middot; {{ __('Updated :date', ['date' => $listing->updated_at->translatedFormat('j M Y')]) }}
                    @endif
                </p>
            </x-card>
        </div>

        <aside class="space-y-4 lg:sticky lg:top-20">
            <x-card>
                <div class="flex flex-wrap gap-1.5">
                    <x-badge variant="neutral">{{ $listing->category->name }}</x-badge>
                    <x-badge variant="neutral">{{ $listing->condition->label() }}</x-badge>
                    @if ($listing->type === \App\Enums\ListingType::Wanted)
                        <x-badge variant="info">{{ __('Wanted') }}</x-badge>
                    @endif
                </div>
                <h1 class="text-xl font-semibold leading-snug">{{ $listing->title }}</h1>
                <p class="text-3xl font-semibold">{{ $listing->formattedPrice() }}</p>
                @if ($listing->city)
                    <p class="text-sm text-base-content/60 flex items-center gap-1.5"><x-icon name="map-pin" class="size-4" />{{ $listing->city }}</p>
                @endif

                @can('update', $listing)
                    <div class="flex gap-2 pt-2 border-t border-base-300">
                        <x-button :href="route('listings.edit', $listing)" wire:navigate class="flex-1">{{ __('Edit') }}</x-button>
                        <x-button :href="route('dashboard')" wire:navigate variant="ghost">{{ __('My listings') }}</x-button>
                    </div>
                @endcan
            </x-card>

            <x-card :title="__('Seller')">
                <a href="{{ route('sellers.show', $seller) }}" wire:navigate class="flex items-center gap-3 group">
                    <x-avatar :user="$seller" class="size-11" />
                    <div class="min-w-0">
                        <p class="font-medium group-hover:text-primary truncate">{{ $seller->name }}</p>
                        <p class="text-sm text-base-content/60">{{ __('Member since :date', ['date' => $seller->created_at->translatedFormat('M Y')]) }}</p>
                    </div>
                </a>
                @if ($phone)
                    <a href="tel:{{ preg_replace('/[^0-9+]/', '', $phone) }}" class="btn w-full">{{ $phone }}</a>
                @endif
            </x-card>

            @if ($listing->status === \App\Enums\ListingStatus::Active && ! $listing->isOwnedBy(auth()->user()))
                <livewire:listings.contact-seller :listing="$listing" :key="'contact-'.$listing->id" />
            @endif
        </aside>
    </div>

    @if ($this->moreFromSeller->isNotEmpty())
        <section class="mt-12" aria-labelledby="seller-heading">
            <div class="flex items-baseline justify-between mb-3">
                <h2 id="seller-heading" class="text-lg font-semibold">{{ __('More from :name', ['name' => $seller->name]) }}</h2>
                <a href="{{ route('sellers.show', $seller) }}" wire:navigate class="text-sm link link-primary no-underline hover:underline">{{ __('View all') }}</a>
            </div>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach ($this->moreFromSeller as $other)
                    <x-listing-card :listing="$other" wire:key="seller-{{ $other->id }}" />
                @endforeach
            </div>
        </section>
    @endif

    @if ($this->related->isNotEmpty())
        <section class="mt-12" aria-labelledby="related-heading">
            <h2 id="related-heading" class="text-lg font-semibold mb-3">{{ __('Similar listings') }}</h2>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach ($this->related as $related)
                    <x-listing-card :listing="$related" wire:key="related-{{ $related->id }}" />
                @endforeach
            </div>
        </section>
    @endif
</div>

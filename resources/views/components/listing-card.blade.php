@props(['listing', 'showStatus' => false, 'layout' => 'grid'])
@php $cover = $listing->coverUrl('thumb'); @endphp
<article data-testid="listing-card" {{ $attributes->class([
    'group bg-base-100 border border-base-300 rounded-box overflow-hidden transition-colors hover:border-base-content/30',
    'flex' => $layout === 'list',
]) }}>
    <a href="{{ route('listings.show', $listing) }}" wire:navigate @class(['block', 'flex w-full' => $layout === 'list'])>
        <div @class(['bg-base-200 overflow-hidden', 'aspect-4/3' => $layout === 'grid', 'w-36 sm:w-56 shrink-0 aspect-4/3' => $layout === 'list'])>
            @if ($cover)
                <img src="{{ $cover }}" alt="{{ $listing->title }}" loading="lazy" class="size-full object-cover">
            @else
                <div class="size-full flex items-center justify-center text-base-content/25"><x-icon name="photo" class="size-10" /></div>
            @endif
        </div>
        <div class="p-3.5 min-w-0 flex-1">
            <p class="font-semibold">{{ $listing->formattedPrice() }}</p>
            <h3 class="mt-0.5 text-sm leading-snug line-clamp-2 group-hover:text-primary">{{ $listing->title }}</h3>
            @if ($layout === 'list')
                <p class="mt-1 text-sm text-base-content/60 line-clamp-2 hidden sm:block">{{ $listing->teaser }}</p>
            @endif
            <div class="mt-2 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-base-content/60">
                <span>{{ $listing->city ?: $listing->category->name }}</span>
                @if ($listing->published_at)
                    <span aria-hidden="true">&middot;</span>
                    <span>{{ $listing->published_at->diffForHumans(short: true) }}</span>
                @endif
                @if ($listing->type === \App\Enums\ListingType::Wanted)
                    <x-badge variant="info">{{ __('Wanted') }}</x-badge>
                @endif
                @if ($showStatus || $listing->status === \App\Enums\ListingStatus::Sold)
                    <x-badge :variant="$listing->status->badge()">{{ $listing->status->label() }}</x-badge>
                @endif
            </div>
        </div>
    </a>
</article>

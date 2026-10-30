<div class="max-w-5xl mx-auto px-4 py-8">
    <x-page-header :title="__('My listings')" :subtitle="__('Publish, hide, mark as sold or remove your ads.')">
        <x-slot:actions>
            <x-button :href="route('listings.create')" wire:navigate variant="primary">{{ __('Post a listing') }}</x-button>
        </x-slot:actions>
    </x-page-header>

    @unless (auth()->user()->hasVerifiedEmail())
        <x-alert type="warning" class="mb-4">{{ __('Verify your email address to post listings.') }}
            <form method="POST" action="{{ route('verification.send') }}" class="inline">@csrf<button class="link font-medium">{{ __('Resend verification email') }}</button></form>
        </x-alert>
    @endunless

    <div role="tablist" class="tabs tabs-border mb-4 overflow-x-auto">
        @foreach (['' => __('All'), 'active' => __('Active'), 'draft' => __('Drafts'), 'sold' => __('Sold')] as $value => $label)
            <button type="button" role="tab" wire:click="$set('status', '{{ $value }}')" @class(['tab', 'tab-active font-medium' => $status === (string) $value])>
                {{ $label }} <span class="ml-1.5 text-xs text-base-content/50">{{ $this->counts[$value] ?? 0 }}</span>
            </button>
        @endforeach
    </div>

    @if ($this->listings->isEmpty())
        <x-card class="text-center">
            <h2 class="font-semibold">{{ __('Nothing here yet') }}</h2>
            <p class="text-base-content/60">{{ __('Post your first listing and reach buyers in your area.') }}</p>
        </x-card>
    @else
        <ul class="bg-base-100 border border-base-300 rounded-box divide-y divide-base-300">
            @foreach ($this->listings as $listing)
                <li wire:key="row-{{ $listing->id }}" class="flex gap-4 p-4">
                    <a href="{{ route('listings.show', $listing) }}" wire:navigate class="w-24 sm:w-32 shrink-0 aspect-4/3 rounded bg-base-200 overflow-hidden flex items-center justify-center">
                        @if ($listing->coverUrl())
                            <img src="{{ $listing->coverUrl() }}" alt="" class="size-full object-cover">
                        @else
                            <x-icon name="photo" class="size-8 text-base-content/25" />
                        @endif
                    </a>
                    <div class="min-w-0 flex-1 space-y-2">
                        <div class="flex flex-wrap items-center gap-2">
                            <a href="{{ route('listings.show', $listing) }}" wire:navigate class="font-medium hover:text-primary truncate">{{ $listing->title }}</a>
                            <x-badge :variant="$listing->status->badge()">{{ $listing->status->label() }}</x-badge>
                        </div>
                        <p class="text-sm text-base-content/60">{{ $listing->formattedPrice() }} &middot; {{ $listing->category->name }} &middot; {{ __('Updated :date', ['date' => $listing->updated_at->diffForHumans()]) }}</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($listing->status->transitions() as $target)
                                <button type="button" class="btn btn-xs" wire:click="setStatus({{ $listing->id }}, '{{ $target->value }}')">
                                    {{ match ($target) {
                                        \App\Enums\ListingStatus::Active => $listing->status === \App\Enums\ListingStatus::Draft ? __('Publish') : __('Reactivate'),
                                        \App\Enums\ListingStatus::Draft => __('Unpublish'),
                                        \App\Enums\ListingStatus::Sold => __('Mark as sold'),
                                    } }}
                                </button>
                            @endforeach
                            <a href="{{ route('listings.edit', $listing) }}" wire:navigate class="btn btn-xs btn-ghost">{{ __('Edit') }}</a>
                            <button type="button" class="btn btn-xs btn-ghost text-error" x-data @click="$wire.confirmDelete({{ $listing->id }}); $dispatch('open-modal', 'delete-listing')">{{ __('Delete') }}</button>
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
        {{ $this->listings->links() }}
    @endif

    <x-modal name="delete-listing" :title="__('Delete this listing?')">
        <p class="text-base-content/70">{{ __('The listing will be removed from the site. This cannot be undone from the dashboard.') }}</p>
        <div class="flex justify-end gap-2">
            <x-button variant="ghost" x-data @click="$dispatch('close-modal', 'delete-listing')">{{ __('Cancel') }}</x-button>
            <x-button variant="danger" wire:click="delete" x-data @click="$dispatch('close-modal', 'delete-listing')">{{ __('Delete') }}</x-button>
        </div>
    </x-modal>
</div>

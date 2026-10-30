<div class="max-w-6xl mx-auto px-4 py-8">
    <x-page-header :title="__('Browse listings')" :subtitle="trans_choice(':count result|:count results', $this->listings->total())" />

    <div class="grid lg:grid-cols-[16rem_1fr] gap-6 items-start">
        <aside>
            <x-card :padding="false">
                <form wire:submit.prevent class="p-4 space-y-4" role="search">
                    <x-input wire:model.live.debounce.400ms="q" type="search" :label="__('Keywords')" />

                    <x-select wire:model.live="category" :label="__('Category')" :placeholder="__('All categories')">
                        @foreach ($this->categories as $category)
                            <option value="{{ $category->slug }}">{{ $category->name }}</option>
                        @endforeach
                    </x-select>

                    <x-select wire:model.live="type" :label="__('Type')" :placeholder="__('Offers and wanted')">
                        @foreach (\App\Enums\ListingType::cases() as $type)
                            <option value="{{ $type->value }}">{{ $type->label() }}</option>
                        @endforeach
                    </x-select>

                    <x-select wire:model.live="condition" :label="__('Condition')" :placeholder="__('Any condition')">
                        @foreach (\App\Enums\ListingCondition::cases() as $condition)
                            <option value="{{ $condition->value }}">{{ $condition->label() }}</option>
                        @endforeach
                    </x-select>

                    <div class="grid grid-cols-2 gap-2">
                        <x-input wire:model.live.debounce.500ms="min" type="number" min="0" step="1" inputmode="numeric" :label="__('Min price')" />
                        <x-input wire:model.live.debounce.500ms="max" type="number" min="0" step="1" inputmode="numeric" :label="__('Max price')" />
                    </div>

                    @if ($this->hasFilters)
                        <button type="button" wire:click="clearFilters" class="text-sm link link-hover text-base-content/70">{{ __('Clear filters') }}</button>
                    @endif
                </form>
            </x-card>
        </aside>

        <section aria-live="polite" wire:loading.class="opacity-60" class="transition-opacity min-w-0">
            <div class="flex items-center justify-between gap-3 mb-4">
                <div class="w-56">
                    <select wire:model.live="sort" aria-label="{{ __('Sort by') }}" class="select select-sm w-full">
                        <option value="newest">{{ __('Newest first') }}</option>
                        <option value="price_asc">{{ __('Price: low to high') }}</option>
                        <option value="price_desc">{{ __('Price: high to low') }}</option>
                    </select>
                </div>
                <div class="join" role="group" aria-label="{{ __('Layout') }}">
                    <button type="button" data-testid="view-grid" wire:click="$set('view', 'grid')" @class(['btn btn-sm join-item', 'btn-active' => $view === 'grid']) aria-label="{{ __('Grid view') }}" aria-pressed="{{ $view === 'grid' ? 'true' : 'false' }}"><x-icon name="squares" class="size-4" /></button>
                    <button type="button" data-testid="view-list" wire:click="$set('view', 'list')" @class(['btn btn-sm join-item', 'btn-active' => $view === 'list']) aria-label="{{ __('List view') }}" aria-pressed="{{ $view === 'list' ? 'true' : 'false' }}"><x-icon name="list" class="size-4" /></button>
                </div>
            </div>

            @if ($this->listings->isEmpty())
                <x-card class="text-center">
                    <h2 class="font-semibold">{{ __('No listings found') }}</h2>
                    <p class="text-base-content/60">{{ __('Try different keywords or remove some filters.') }}</p>
                </x-card>
            @else
                <div data-testid="listing-results" data-layout="{{ $view }}" @class(['grid gap-4', 'grid-cols-2 xl:grid-cols-3' => $view === 'grid', 'grid-cols-1' => $view === 'list'])>
                    @foreach ($this->listings as $listing)
                        <x-listing-card :listing="$listing" :layout="$view === 'list' ? 'list' : 'grid'" wire:key="listing-{{ $listing->id }}" />
                    @endforeach
                </div>
                {{ $this->listings->links() }}
            @endif
        </section>
    </div>
</div>

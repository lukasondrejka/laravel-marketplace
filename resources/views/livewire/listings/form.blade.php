@php
    $mediaById = $listing ? $listing->getMedia(\App\Models\Listing::IMAGES)->keyBy('id') : collect();
    $hasPhone = filled(auth()->user()->phone);
@endphp
<div class="max-w-3xl mx-auto px-4 py-8">
    <x-page-header :title="$listing ? __('Edit listing') : __('Post a listing')" :subtitle="__('Add clear photos and an honest description.')" />

    <form wire:submit.prevent class="space-y-6">
        <x-card :title="__('Details')">
            <x-field :label="__('I want to')" name="form.type">
                <div class="join" x-data="{ type: $wire.$entangle('form.type') }" role="group">
                    @foreach (\App\Enums\ListingType::cases() as $type)
                        <button type="button" class="btn join-item" :class="type === '{{ $type->value }}' && 'btn-primary'" :aria-pressed="type === '{{ $type->value }}'" @click="type = '{{ $type->value }}'">
                            {{ $type === \App\Enums\ListingType::Offer ? __('Sell something') : __('Find something') }}
                        </button>
                    @endforeach
                </div>
            </x-field>

            <x-input wire:model="form.title" :label="__('Title')" required maxlength="120" />

            <div class="grid gap-4 sm:grid-cols-2">
                <x-select wire:model="form.category_id" :label="__('Category')" :placeholder="__('Choose a category')" required>
                    @foreach ($this->categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </x-select>
                <x-select wire:model="form.condition" :label="__('Condition')" required>
                    @foreach (\App\Enums\ListingCondition::cases() as $condition)
                        <option value="{{ $condition->value }}">{{ $condition->label() }}</option>
                    @endforeach
                </x-select>
            </div>

            <x-textarea wire:model="form.description" :label="__('Description')" required rows="6" :hint="__('Links are made clickable automatically.')" />

            <div class="grid gap-4 sm:grid-cols-2">
                <x-input wire:model="form.price" type="number" step="0.01" min="0" inputmode="decimal" :label="__('Price (:currency)', ['currency' => config('marketplace.currency')])" required :hint="__('Use 0 for free items.')" />
                <x-input wire:model="form.city" :label="__('Location')" :hint="__('Prefilled from your profile.')" />
            </div>
        </x-card>

        <x-card :title="__('Contact')">
            <p class="text-sm text-base-content/70">{{ __('Buyers can message you from the listing page. Your email address is never shown.') }}</p>
            <label class="flex items-start gap-3 {{ $hasPhone ? 'cursor-pointer' : 'opacity-60' }}">
                <input type="checkbox" wire:model="form.show_phone" class="checkbox checkbox-sm mt-0.5" @disabled(! $hasPhone)>
                <span>
                    <span class="block text-sm font-medium">{{ __('Show my phone number') }}@if ($hasPhone) <span class="font-normal text-base-content/60">({{ auth()->user()->phone }})</span>@endif</span>
                    @unless ($hasPhone)
                        <span class="block text-sm text-base-content/60">{{ __('Add a phone number in your') }} <a href="{{ route('profile.edit') }}" wire:navigate class="link">{{ __('profile') }}</a> {{ __('to enable this.') }}</span>
                    @endunless
                </span>
            </label>
        </x-card>

        <x-card :title="__('Photos')">
            <div
                x-data="{ over: false, drag: null, uploading: false, progress: 0 }"
                x-on:livewire-upload-start="uploading = true; progress = 0"
                x-on:livewire-upload-finish="uploading = false"
                x-on:livewire-upload-error="uploading = false"
                x-on:livewire-upload-progress="progress = $event.detail.progress"
                class="space-y-4"
            >
                @if (count($imageOrder) < \App\Models\Listing::MAX_IMAGES)
                    <label
                        for="uploads"
                        @dragover.prevent="if (! drag) over = true"
                        @dragleave.prevent="over = false"
                        @drop.prevent="over = false; if (! drag && $event.dataTransfer.files.length) { $refs.input.files = $event.dataTransfer.files; $refs.input.dispatchEvent(new Event('change', { bubbles: true })) }"
                        :class="over ? 'border-primary bg-primary/5' : 'border-base-300 hover:border-base-content/40'"
                        class="flex flex-col items-center justify-center gap-1 rounded-box border-2 border-dashed px-4 py-8 text-center cursor-pointer transition-colors"
                    >
                        <x-icon name="arrow-up-tray" class="size-7 text-base-content/50" />
                        <span class="text-sm font-medium"><span class="text-primary">{{ __('Choose files') }}</span> {{ __('or drag and drop them here') }}</span>
                        <span class="text-xs text-base-content/60">{{ __('Up to :max images, :size MB each (JPG, PNG or WebP).', ['max' => \App\Models\Listing::MAX_IMAGES, 'size' => config('marketplace.images.max_size_kb') / 1024]) }}</span>
                        <input type="file" id="uploads" x-ref="input" wire:model="uploads" multiple accept="image/jpeg,image/png,image/webp" class="sr-only">
                    </label>
                    <progress x-show="uploading" x-cloak class="progress progress-primary w-full" :value="progress" max="100"></progress>
                    @error('uploads') <p class="text-sm text-error" role="alert">{{ $message }}</p> @enderror
                @endif

                @if (count($imageOrder))
                    <div>
                        <p class="text-xs text-base-content/60 mb-2">{{ __('Drag to reorder. The first image is the cover.') }}</p>
                        <ul class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            @foreach ($imageOrder as $i => $key)
                                @php
                                    $isNew = str_starts_with($key, 'n:');
                                    $src = $isNew
                                        ? (isset($newImages[$key]) && $newImages[$key]->isPreviewable() ? $newImages[$key]->temporaryUrl() : null)
                                        : ($mediaById[(int) substr($key, 2)] ?? null)?->getUrl('thumb');
                                @endphp
                                <li
                                    wire:key="img-{{ $key }}"
                                    data-testid="image-tile"
                                    draggable="true"
                                    @dragstart="drag = @js($key); $event.dataTransfer.effectAllowed = 'move'"
                                    @dragend="drag = null"
                                    @dragover.prevent
                                    @drop.prevent="if (drag && drag !== @js($key)) $wire.moveImageTo(drag, {{ $i }}); drag = null"
                                    :class="drag === @js($key) ? 'opacity-40' : ''"
                                    class="relative rounded-box overflow-hidden border border-base-300 bg-base-100 cursor-grab"
                                >
                                    <div class="aspect-4/3 bg-base-200">
                                        @if ($src)
                                            <img src="{{ $src }}" alt="" class="size-full object-cover pointer-events-none">
                                        @else
                                            <div class="size-full flex items-center justify-center text-base-content/30"><x-icon name="photo" class="size-8" /></div>
                                        @endif
                                    </div>
                                    @if ($i === 0)
                                        <span class="absolute top-1.5 left-1.5 rounded bg-primary px-1.5 py-0.5 text-xs font-medium text-primary-content">{{ __('Cover') }}</span>
                                    @endif
                                    <div class="flex items-center justify-between gap-1 p-1.5 border-t border-base-300">
                                        @if ($i !== 0)
                                            <button type="button" data-testid="make-cover" class="btn btn-ghost btn-xs" wire:click="makeCover('{{ $key }}')">{{ __('Make cover') }}</button>
                                        @else
                                            <span></span>
                                        @endif
                                        <button type="button" class="btn btn-ghost btn-xs btn-square text-error" wire:click="removeImage('{{ $key }}')" aria-label="{{ __('Remove image') }}"><x-icon name="trash" class="size-4" /></button>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </x-card>

        <div class="flex flex-wrap justify-end gap-2">
            @if ($listing)
                <x-button :href="route('listings.show', $listing)" wire:navigate variant="ghost">{{ __('Cancel') }}</x-button>
                <x-button variant="primary" wire:click="save" loading="save" wire:loading.attr="disabled">{{ __('Save changes') }}</x-button>
            @else
                <x-button data-testid="save-draft" wire:click="save('draft')" loading="save" wire:loading.attr="disabled">{{ __('Save as draft') }}</x-button>
                <x-button variant="primary" data-testid="publish" wire:click="save('active')" loading="save" wire:loading.attr="disabled">{{ __('Publish') }}</x-button>
            @endif
        </div>
    </form>
</div>

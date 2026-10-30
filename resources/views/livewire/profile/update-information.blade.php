<x-card :title="__('Profile information')">
    <form wire:submit="save" class="space-y-4">
        <div class="flex items-center gap-4">
            <div>
                <div class="size-16 rounded-full overflow-hidden bg-base-300 text-base-content/70 flex items-center justify-center">
                    @if ($form->avatar && $form->avatar->isPreviewable())
                        <img src="{{ $form->avatar->temporaryUrl() }}" alt="">
                    @elseif (auth()->user()->avatarUrl() && ! $form->removeAvatar)
                        <img src="{{ auth()->user()->avatarUrl() }}" alt="">
                    @else
                        <span class="text-xl">{{ auth()->user()->initials() }}</span>
                    @endif
                </div>
            </div>
            <div class="flex-1">
                <x-field name="form.avatar" :label="__('Avatar')" :hint="__('JPG, PNG or WebP, up to 2 MB.')">
                    <input type="file" wire:model="form.avatar" id="form.avatar" accept="image/jpeg,image/png,image/webp" class="file-input w-full">
                </x-field>
                @if (auth()->user()->avatarUrl())
                    <label class="label cursor-pointer justify-start gap-2 mt-1">
                        <input type="checkbox" wire:model="form.removeAvatar" class="checkbox checkbox-sm">
                        <span class="label-text">{{ __('Remove current avatar') }}</span>
                    </label>
                @endif
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <x-input wire:model="form.name" :label="__('Name')" required />
            <x-input wire:model="form.email" type="email" :label="__('Email')" required />
            <x-input wire:model="form.phone" :label="__('Phone')" :hint="__('Optional. Only shown on listings where you choose to show it.')" />
            <x-input wire:model="form.city" :label="__('City')" :hint="__('Used as the default location for new listings.')" />
        </div>

        <x-textarea wire:model="form.bio" :label="__('About you')" rows="3" :hint="__('Shown on your public seller page.')" />

        <div class="flex justify-end">
            <x-button type="submit" variant="primary" loading="save">{{ __('Save changes') }}</x-button>
        </div>
    </form>
</x-card>

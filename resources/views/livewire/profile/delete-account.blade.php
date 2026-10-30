<x-card :title="__('Delete account')" >
    <p class="text-base-content/70">{{ __('Once your account is deleted, all of its listings and images are permanently removed.') }}</p>
    <div class="flex justify-end">
        <x-button variant="danger" x-data @click="$dispatch('open-modal', 'delete-account')">{{ __('Delete account') }}</x-button>
    </div>

    <x-modal name="delete-account" :title="__('Are you sure?')">
        <form wire:submit="delete" class="space-y-4">
            <p>{{ __('Enter your password to confirm you want to permanently delete your account.') }}</p>
            <x-input wire:model="password" type="password" :label="__('Password')" autocomplete="current-password" />
            <div class="modal-action">
                <x-button variant="ghost" x-data @click="$dispatch('close-modal', 'delete-account')">{{ __('Cancel') }}</x-button>
                <x-button type="submit" variant="danger" loading="delete">{{ __('Delete account') }}</x-button>
            </div>
        </form>
    </x-modal>
</x-card>

<x-card :title="__('Change password')">
    <form wire:submit="save" class="space-y-4">
        <x-input wire:model="current_password" type="password" :label="__('Current password')" autocomplete="current-password" />
        <div class="grid gap-4 sm:grid-cols-2">
            <x-input wire:model="password" type="password" :label="__('New password')" autocomplete="new-password" />
            <x-input wire:model="password_confirmation" type="password" :label="__('Confirm new password')" autocomplete="new-password" />
        </div>
        <div class="flex justify-end">
            <x-button type="submit" variant="primary" loading="save">{{ __('Update password') }}</x-button>
        </div>
    </form>
</x-card>

<x-layouts.app>
    <div class="max-w-5xl mx-auto px-4 py-10 space-y-8">
        <x-page-header title="UI kit" subtitle="Base components"/>
        <x-card title="Form">
            <x-input label="Title" name="t" hint="Hint text" />
            <x-select label="Category" name="c" placeholder="Choose"><option>One</option></x-select>
            <x-textarea label="Description" name="d" />
            <div class="flex gap-2 flex-wrap items-center">
                <x-button>Primary</x-button><x-button variant="outline">Outline</x-button><x-button variant="danger">Danger</x-button>
                <x-badge variant="success">Active</x-badge><x-badge variant="warning">Draft</x-badge>
                <x-button variant="ghost" x-data @click="$dispatch('notify', {type: 'success', message: 'Hello toast'})">Toast</x-button>
                <x-button variant="ghost" x-data @click="$dispatch('open-modal', 'demo')">Modal</x-button>
            </div>
            <x-alert type="warning">Be careful.</x-alert>
        </x-card>
        <x-modal name="demo" title="Demo modal"><p>Content</p></x-modal>
    </div>
</x-layouts.app>

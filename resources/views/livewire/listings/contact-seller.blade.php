<x-card :title="__('Contact seller')">
    @if ($sent)
        <x-alert type="success" data-testid="contact-sent">{{ __('Your message was sent. The seller will reply to your email address.') }}</x-alert>
    @else
        <form wire:submit="send" class="space-y-3">
            @auth
                <p class="text-sm text-base-content/60">{{ __('Sending as :name (:email)', ['name' => $name, 'email' => $email]) }}</p>
            @else
                <x-input wire:model="name" :label="__('Your name')" required autocomplete="name" />
                <x-input wire:model="email" type="email" :label="__('Your email')" required autocomplete="email" />
            @endauth
            <x-textarea wire:model="message" :label="__('Message')" rows="4" required :hint="__('The seller\'s email address stays private; they reply to you directly.')" />
            <x-button type="submit" variant="primary" data-testid="contact-send" class="w-full" loading="send">{{ __('Send message') }}</x-button>
        </form>
    @endif
</x-card>

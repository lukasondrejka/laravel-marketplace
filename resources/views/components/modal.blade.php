@props(['name', 'title' => null])
{{-- Open with: $dispatch('open-modal', 'name'); close with: $dispatch('close-modal', 'name') --}}
<div
    x-data="{ open: false }"
    x-on:open-modal.window="if ($event.detail === '{{ $name }}') open = true"
    x-on:close-modal.window="if ($event.detail === '{{ $name }}') open = false"
    x-on:keydown.escape.window="open = false"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
    role="dialog"
    aria-modal="true"
    @if($title) aria-label="{{ $title }}" @endif
>
    <div class="fixed inset-0 bg-black/50" x-on:click="open = false" x-show="open" x-transition.opacity></div>
    <div class="relative w-full max-w-md bg-base-100 border border-base-300 rounded-box p-6 space-y-4" x-show="open" x-transition.opacity>
        @if ($title)
            <h3 class="text-lg font-semibold">{{ $title }}</h3>
        @endif
        {{ $slot }}
    </div>
</div>

@php
    $flash = collect(['success', 'error', 'warning', 'info'])
        ->filter(fn ($type) => session()->has($type))
        ->map(fn ($type) => ['type' => $type, 'message' => session($type)])
        ->values();
    if (session('status') && ! $flash->contains('type', 'success')) {
        $flash->push(['type' => 'success', 'message' => session('status')]);
    }
@endphp
<div
    x-data="{
        toasts: [],
        add(type, message) {
            const id = Date.now() + Math.random();
            this.toasts.push({ id, type, message });
            setTimeout(() => this.remove(id), 5000);
        },
        remove(id) { this.toasts = this.toasts.filter(t => t.id !== id); },
    }"
    x-init="@js($flash).forEach(t => add(t.type, t.message))"
    @notify.window="add($event.detail.type ?? 'success', $event.detail.message)"
    class="toast toast-end toast-bottom z-50 text-sm"
    role="status"
    aria-live="polite"
>
    <template x-for="toast in toasts" :key="toast.id">
        <div class="alert border border-base-300 bg-base-100 text-base-content" :class="{
            'border-l-4 border-l-success': toast.type === 'success',
            'border-l-4 border-l-error': toast.type === 'error',
            'border-l-4 border-l-warning': toast.type === 'warning',
            'border-l-4 border-l-info': toast.type === 'info',
        }">
            <span x-text="toast.message"></span>
            <button type="button" class="btn btn-ghost btn-xs btn-circle" @click="remove(toast.id)" aria-label="{{ __('Dismiss') }}">&times;</button>
        </div>
    </template>
</div>

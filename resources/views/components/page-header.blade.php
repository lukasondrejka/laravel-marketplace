@props(['title', 'subtitle' => null])
<div {{ $attributes->class('mb-6 flex flex-wrap items-end justify-between gap-4') }}>
    <div>
        <h1 class="text-2xl font-semibold tracking-tight">{{ $title }}</h1>
        @if ($subtitle)
            <p class="text-base-content/60 mt-1">{{ $subtitle }}</p>
        @endif
    </div>
    @isset($actions)
        <div class="flex gap-2">{{ $actions }}</div>
    @endisset
</div>

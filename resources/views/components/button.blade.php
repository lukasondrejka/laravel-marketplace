@props(['variant' => 'secondary', 'size' => null, 'href' => null, 'type' => 'button', 'loading' => null])
@php
    $classes = Arr::toCssClasses([
        'btn',
        'btn-primary' => $variant === 'primary',
        'btn-ghost' => $variant === 'ghost',
        'btn-error' => $variant === 'danger',
        'btn-sm' => $size === 'sm',
        'btn-xs' => $size === 'xs',
    ]);
@endphp
@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($loading)
            <span wire:loading wire:target="{{ $loading }}" class="loading loading-spinner loading-xs"></span>
        @endif
        {{ $slot }}
    </button>
@endif

@props(['variant' => 'neutral'])
<span {{ $attributes->class([
    'badge badge-sm font-medium',
    'badge-outline' => $variant === 'neutral',
    'badge-primary badge-soft' => $variant === 'primary',
    'badge-success badge-soft' => $variant === 'success',
    'badge-warning badge-soft' => $variant === 'warning',
    'badge-error badge-soft' => $variant === 'error',
    'badge-info badge-soft' => $variant === 'info',
    'badge-ghost' => $variant === 'ghost',
]) }}>{{ $slot }}</span>

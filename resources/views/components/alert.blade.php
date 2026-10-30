@props(['type' => 'info'])
<div role="alert" {{ $attributes->class([
    'alert alert-soft text-sm',
    'alert-success' => $type === 'success',
    'alert-error' => $type === 'error',
    'alert-warning' => $type === 'warning',
    'alert-info' => $type === 'info',
]) }}>
    <div>{{ $slot }}</div>
</div>

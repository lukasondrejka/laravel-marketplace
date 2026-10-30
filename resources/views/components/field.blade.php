@props(['label' => null, 'name' => null, 'hint' => null, 'required' => false])
@php
    $name ??= $attributes->whereStartsWith('wire:model')->first();
    $error = $name ? $errors->first($name) : null;
@endphp
<div {{ $attributes->only('class')->merge(['class' => 'w-full']) }}>
    @if ($label)
        <label class="block text-sm font-medium mb-1.5" @if($name) for="{{ $name }}" @endif>{{ $label }}@if($required)<span class="text-error"> *</span>@endif</label>
    @endif
    {{ $slot }}
    @if ($error)
        <p class="mt-1.5 text-sm text-error" role="alert">{{ $error }}</p>
    @elseif ($hint)
        <p class="mt-1.5 text-sm text-base-content/60">{{ $hint }}</p>
    @endif
</div>

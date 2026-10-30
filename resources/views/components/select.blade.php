@props(['label' => null, 'name' => null, 'hint' => null, 'placeholder' => null, 'required' => false])
@php
    $name ??= $attributes->whereStartsWith('wire:model')->first();
    $invalid = $name && $errors->has($name);
@endphp
<x-field :label="$label" :name="$name" :hint="$hint" :required="$required" :class="$attributes->get('class')">
    <select
        @if($name) id="{{ $name }}" name="{{ $name }}" @endif
        @required($required)
        {{ $attributes->except('class')->class(['select select-bordered w-full', 'select-error' => $invalid]) }}
    >
        @if ($placeholder !== null)
            <option value="">{{ $placeholder }}</option>
        @endif
        {{ $slot }}
    </select>
</x-field>

@props(['label' => null, 'name' => null, 'hint' => null, 'type' => 'text', 'required' => false])
@php
    $name ??= $attributes->whereStartsWith('wire:model')->first();
    $invalid = $name && $errors->has($name);
@endphp
<x-field :label="$label" :name="$name" :hint="$hint" :required="$required" :class="$attributes->get('class')">
    <input
        type="{{ $type }}"
        @if($name) id="{{ $name }}" name="{{ $name }}" @endif
        @required($required)
        {{ $attributes->except('class')->class(['input input-bordered w-full', 'input-error' => $invalid]) }}
    >
</x-field>

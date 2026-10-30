@props(['label' => null, 'name' => null, 'hint' => null, 'required' => false])
@php
    $name ??= $attributes->whereStartsWith('wire:model')->first();
    $invalid = $name && $errors->has($name);
@endphp
<x-field :label="$label" :name="$name" :hint="$hint" :required="$required" :class="$attributes->get('class')">
    <textarea
        @if($name) id="{{ $name }}" name="{{ $name }}" @endif
        @required($required)
        {{ $attributes->except('class')->class(['textarea textarea-bordered w-full min-h-32', 'textarea-error' => $invalid]) }}
    >{{ $slot }}</textarea>
</x-field>

@props(['title' => null, 'padding' => true])
<section {{ $attributes->merge(['class' => 'bg-base-100 border border-base-300 rounded-box']) }}>
    @if ($title)
        <h2 class="px-5 py-3 border-b border-base-300 font-semibold">{{ $title }}</h2>
    @endif
    <div @class(['p-5 space-y-4' => $padding])>
        {{ $slot }}
    </div>
</section>

@props(['title', 'subtitle' => null])
<div class="min-h-[70vh] flex items-center justify-center px-4 py-10">
    <div class="w-full max-w-md">
        <x-card>
            <div class="mb-2 text-center">
                <h1 class="text-2xl font-bold">{{ $title }}</h1>
                @if ($subtitle)
                    <p class="text-base-content/70 mt-1">{{ $subtitle }}</p>
                @endif
            </div>
            {{ $slot }}
        </x-card>
    </div>
</div>

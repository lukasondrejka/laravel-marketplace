@props([
    'title' => null,
    'description' => null,
    'canonical' => null,
    'image' => null,
    'type' => 'website',
])
@php
    $pageTitle = $title ? $title.' | '.config('app.name') : config('app.name').' - '.__('Buy and sell locally');
    $pageDescription = $description ?? __('Browse and post free classified ads: electronics, vehicles, furniture and more.');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDescription }}">
    <link rel="canonical" href="{{ $canonical ?? url()->current() }}">
    <meta property="og:site_name" content="{{ config('app.name') }}">
    <meta property="og:type" content="{{ $type }}">
    <meta property="og:title" content="{{ $title ?? config('app.name') }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:url" content="{{ $canonical ?? url()->current() }}">
    @if ($image)
        <meta property="og:image" content="{{ $image }}">
    @endif
    <meta name="twitter:card" content="{{ $image ? 'summary_large_image' : 'summary' }}">
    {{-- Apply the stored or system theme before first paint to avoid a flash. --}}
    <script>
        (function () {
            var theme = null;
            try { theme = localStorage.getItem('theme'); } catch (e) {}
            if (theme !== 'light' && theme !== 'dark') {
                theme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            }
            document.documentElement.dataset.theme = theme;
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('head')
</head>
<body class="min-h-screen flex flex-col bg-base-200 text-base-content antialiased">
    <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:z-50 btn btn-primary">{{ __('Skip to content') }}</a>

    <x-navbar />

    <main id="main" class="flex-1">
        {{ $slot }}
    </main>

    <x-footer />

    <x-toast-container />
    @livewireScripts
    @stack('scripts')
</body>
</html>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'EduPath') }}@isset($pageTitle) — {{ $pageTitle }}@endisset</title>
        @isset($metaDescription)<meta name="description" content="{{ $metaDescription }}">@endisset
        @isset($canonical)<link rel="canonical" href="{{ $canonical }}">@endisset

        <meta property="og:site_name" content="{{ config('app.name', 'EduPath') }}">
        @isset($pageTitle)<meta property="og:title" content="{{ $pageTitle }} — {{ config('app.name', 'EduPath') }}">@endisset
        @isset($metaDescription)<meta property="og:description" content="{{ $metaDescription }}">@endisset
        @isset($canonical)<meta property="og:url" content="{{ $canonical }}">@endisset
        <meta property="og:type" content="website">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased" style="background-color: #F8FAFC;">
        <a
            href="#main-content"
            class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-[60] focus:rounded-lg focus:bg-white focus:px-4 focus:py-2 focus:text-sm focus:font-medium focus:text-indigo-700 focus:shadow-sm"
        >
            Aller au contenu
        </a>

        <x-navbar />

        @isset($header)
            <header class="border-b border-slate-200 bg-white">
                <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endisset

        <main id="main-content">
            <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                {{ $slot }}
            </div>
        </main>

        <footer class="border-t border-slate-200 bg-white py-6">
            <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 px-4 text-sm text-slate-500 sm:flex-row sm:px-6 lg:px-8">
                <p class="font-semibold text-indigo-600">{{ config('app.name', 'EduPath') }}</p>
                <p>&copy; {{ date('Y') }} {{ config('app.name', 'EduPath') }} — Plateforme e-learning</p>
                <div class="flex gap-4">
                    @if (\Illuminate\Support\Facades\Route::has('catalog'))
                        <a href="{{ route('catalog') }}" class="hover:text-slate-900">Catalogue</a>
                    @endif
                    @if (\Illuminate\Support\Facades\Route::has('verify'))
                        <a href="{{ route('verify') }}" class="hover:text-slate-900">Vérifier un certificat</a>
                    @endif
                </div>
            </div>
        </footer>
    </body>
</html>
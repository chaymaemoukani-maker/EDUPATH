<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'EduPath') }}@isset($pageTitle) — {{ $pageTitle }}@endisset</title>
        @isset($metaDescription)<meta name="description" content="{{ $metaDescription }}">@endisset
        @isset($canonical)<link rel="canonical" href="{{ $canonical }}">@endisset

        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}?v=2">
        <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}?v=2">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}?v=2">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body x-data="{ sidebarOpen: false }" class="font-sans antialiased" style="background-color: #F8FAFC;">
        <a
            href="#main-content"
            class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-[60] focus:rounded-lg focus:bg-white focus:px-4 focus:py-2 focus:text-sm focus:font-medium focus:text-indigo-700 focus:shadow-sm"
        >
            Aller au contenu
        </a>

        {{-- Mobile top bar (hamburger opens the sidebar drawer) --}}
        <div class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-slate-200 bg-white px-4 md:hidden">
            <button
                type="button"
                @click="sidebarOpen = true"
                class="inline-flex items-center justify-center rounded-md p-2 text-slate-500 hover:bg-slate-100 focus:outline-none"
                aria-label="Ouvrir le menu"
            >
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>

            <a href="{{ url('/') }}" class="flex items-center gap-2">
                <x-application-logo class="h-8 w-8" />
                <span class="text-xl font-bold text-indigo-600">{{ config('app.name', 'EduPath') }}</span>
            </a>

            <div class="w-10"></div>
        </div>

        {{-- Desktop top navigation --}}
        <x-navbar class="hidden md:block" />

        {{-- Mobile sidebar drawer --}}
        <div x-show="sidebarOpen" style="display: none;" class="fixed inset-0 z-50 md:hidden">
            <div
                class="absolute inset-0 bg-black/50"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="sidebarOpen = false"
            ></div>

            <div
                class="absolute inset-y-0 left-0 w-72 max-w-[85vw] bg-white shadow-xl"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="-translate-x-full"
                x-transition:enter-end="translate-x-0"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="translate-x-0"
                x-transition:leave-end="-translate-x-full"
            >
                <div class="flex h-full flex-col overflow-y-auto">
                    <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                        <a href="{{ url('/') }}" class="flex items-center gap-2">
                            <x-application-logo class="h-8 w-8" />
                            <span class="text-xl font-bold text-indigo-600">{{ config('app.name', 'EduPath') }}</span>
                        </a>
                        <button
                            type="button"
                            @click="sidebarOpen = false"
                            class="inline-flex items-center justify-center rounded-md p-2 text-slate-500 hover:bg-slate-100"
                            aria-label="Fermer le menu"
                        >
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <x-sidebar :responsive="false" class="flex-1" />
                </div>
            </div>
        </div>

        {{-- Main area --}}
        <div class="mx-auto flex max-w-7xl">
            <x-sidebar class="sticky top-16 hidden h-[calc(100vh-4rem)] md:flex" />

            <main id="main-content" class="min-w-0 flex-1 px-4 py-8 sm:px-6 lg:px-8">
                @isset($header)
                    <header class="mb-8">
                        {{ $header }}
                    </header>
                @endisset

                {{ $slot }}
            </main>
        </div>
    </body>
</html>
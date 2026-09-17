@props(['title' => null, 'description' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}?v=2">
        <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}?v=2">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}?v=2">

        <title>{{ config('app.name', 'EduPath') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased" style="background-color: #F8FAFC;">
        <div class="flex min-h-screen flex-col">
            <header class="border-b border-slate-200 bg-white px-4 py-4">
                <a href="{{ url('/') }}" class="mx-auto flex max-w-6xl items-center gap-2 text-xl font-bold text-indigo-600">
                    <x-application-logo class="h-8 w-8" />
                    EduPath
                </a>
            </header>

            <main class="flex flex-1 items-center justify-center px-4 py-12">
                <div class="w-full max-w-md">
                    @if ($title)
                        <div class="mb-8 text-center">
                            <h1 class="mb-2 text-3xl font-bold text-slate-900">{{ $title }}</h1>
                            @if ($description)
                                <p class="text-sm text-slate-500">{{ $description }}</p>
                            @endif
                        </div>
                    @endif

                    <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
                        {{ $slot }}
                    </div>
                </div>
            </main>
        </div>
    </body>
</html>
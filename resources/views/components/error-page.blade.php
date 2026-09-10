@props([
    'code' => '500',
    'title' => 'Une erreur est survenue',
    'message' => 'Une erreur inattendue est survenue. Merci de réessayer.',
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $code }} — {{ config('app.name', 'EduPath') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css'])
    </head>
    <body class="font-sans text-gray-900 antialiased" style="background-color: #F8FAFC;">
        <div class="flex min-h-screen flex-col items-center justify-center px-6 py-16">
            <a href="{{ url('/') }}" class="mb-8 text-lg font-bold tracking-tight text-slate-900">
                {{ config('app.name', 'EduPath') }}
            </a>

            <div class="text-center">
                <p class="text-7xl font-bold text-indigo-600">{{ $code }}</p>
                <h1 class="mt-4 text-3xl font-bold tracking-tight text-slate-900">{{ $title }}</h1>
                <p class="mx-auto mt-3 max-w-md text-sm leading-relaxed text-slate-500">{{ $message }}</p>

                <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                    <a href="{{ url('/') }}"
                       class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-indigo-700">
                        Retour à l'accueil
                    </a>
                    <a href="javascript:history.back()"
                       class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50">
                        Page précédente
                    </a>
                </div>
            </div>
        </div>
    </body>
</html>
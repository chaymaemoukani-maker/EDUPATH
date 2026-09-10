@props(['disabled' => false, 'type' => 'text', 'error' => false])

@php
    $state = $error
        ? 'border-red-400 focus:border-red-400 focus:ring-red-400'
        : 'border-slate-300 focus:border-indigo-500 focus:ring-indigo-500';
@endphp

<input
    @disabled($disabled)
    type="{{ $type }}"
    {{ $attributes->merge(['class' => 'block w-full rounded-lg border bg-white px-3 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 '.$state]) }}
>
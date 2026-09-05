@props(['disabled' => false, 'type' => 'text'])

<input
    @disabled($disabled)
    type="{{ $type }}"
    {{ $attributes->merge(['class' => 'block w-full rounded-lg border-slate-300 px-3 py-2.5 text-sm text-slate-900 placeholder-slate-400 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 focus:ring-opacity-40']) }}
>
@props(['disabled' => false, 'placeholder' => null])

<select
    @disabled($disabled)
    {{ $attributes->merge(['class' => 'block w-full rounded-lg border-slate-300 px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 focus:ring-opacity-40']) }}
>
    @if ($placeholder)
        <option value="">{{ $placeholder }}</option>
    @endif
    {{ $slot }}
</select>
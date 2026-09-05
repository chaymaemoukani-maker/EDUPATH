@props(['type' => 'info', 'title' => null])

@php
    $styles = [
        'success' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
        'error' => 'bg-red-50 text-red-800 border-red-200',
        'warning' => 'bg-amber-50 text-amber-800 border-amber-200',
        'info' => 'bg-blue-50 text-blue-800 border-blue-200',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'rounded-lg border p-4 '.($styles[$type] ?? $styles['info'])]) }} role="alert">
    @if ($title)
        <h3 class="text-sm font-semibold">{{ $title }}</h3>
    @endif
    <div class="{{ $title ? 'mt-1' : '' }} text-sm">{{ $slot }}</div>
</div>
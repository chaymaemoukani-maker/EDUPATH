@props(['type' => 'info', 'title' => null])

@php
    $styles = [
        'success' => ['bg' => 'bg-emerald-50', 'border' => 'border-emerald-200', 'text' => 'text-emerald-800', 'icon' => '✓'],
        'error' => ['bg' => 'bg-red-50', 'border' => 'border-red-200', 'text' => 'text-red-800', 'icon' => '✕'],
        'warning' => ['bg' => 'bg-amber-50', 'border' => 'border-amber-200', 'text' => 'text-amber-800', 'icon' => '!'],
        'info' => ['bg' => 'bg-blue-50', 'border' => 'border-blue-200', 'text' => 'text-blue-800', 'icon' => 'i'],
    ];
    $style = $styles[$type] ?? $styles['info'];
@endphp

<div {{ $attributes->merge(['class' => 'flex gap-3 rounded-lg border p-4 '.$style['bg'].' '.$style['border']]) }} role="alert">
    <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-white text-xs font-bold {{ $style['text'] }}">{{ $style['icon'] }}</span>
    <div class="min-w-0">
        @if ($title)
            <h3 class="text-sm font-semibold {{ $style['text'] }}">{{ $title }}</h3>
        @endif
        <div class="{{ $title ? 'mt-1' : '' }} text-sm {{ $style['text'] }}">{{ $slot }}</div>
    </div>
</div>
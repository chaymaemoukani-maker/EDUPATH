@props([
    'percent' => 0,
    'showLabel' => true,
    'size' => 'md',
])

@php
    $clamped = min(100, max(0, (int) $percent));
    $height = $size === 'sm' ? 'h-1.5' : 'h-2.5';
    $color = $clamped >= 100 ? 'bg-emerald-600' : 'bg-indigo-600';
@endphp

<div {{ $attributes->merge(['class' => 'flex items-center gap-3']) }}>
    <div
        role="progressbar"
        aria-label="{{ $showLabel ? 'Progression' : 'Progression du cours' }}"
        aria-valuemin="0"
        aria-valuemax="100"
        aria-valuenow="{{ $clamped }}"
        class="h-full w-full flex-1 overflow-hidden rounded-full bg-slate-200 {{ $height }}"
    >
        <div class="{{ $color }} h-full rounded-full transition-all duration-500" style="width: {{ $clamped }}%"></div>
    </div>
    @if ($showLabel)
        <span class="w-8 shrink-0 text-right text-xs font-medium text-slate-500">{{ $clamped }}%</span>
    @endif
</div>
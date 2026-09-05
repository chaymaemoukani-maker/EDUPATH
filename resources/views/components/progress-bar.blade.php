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

<div {{ $attributes->merge(['class' => 'w-full']) }}>
    @if ($showLabel)
        <div class="mb-1.5 flex items-center justify-between">
            <span class="text-xs font-medium text-slate-500">Progression</span>
            <span class="text-xs font-semibold text-slate-700">{{ $clamped }}%</span>
        </div>
    @endif
    <div class="{{ $height }} w-full overflow-hidden rounded-full bg-slate-200">
        <div class="{{ $color }} h-full rounded-full transition-all duration-300" style="width: {{ $clamped }}%"></div>
    </div>
</div>
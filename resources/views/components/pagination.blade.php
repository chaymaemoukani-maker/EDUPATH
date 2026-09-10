@props(['paginator'])

@php
    $isLengthAware = $paginator instanceof \Illuminate\Pagination\LengthAwarePaginator;
    $current = $paginator->currentPage();
    $last = $paginator->lastPage();
    $side = 1;
    $start = $isLengthAware ? max(1, $current - $side) : 1;
    $end = $isLengthAware ? min($last, $current + $side) : $start;

    $button = 'inline-flex min-w-[2.25rem] items-center justify-center rounded-lg border px-3 py-2 text-sm font-medium transition-colors';
    $neutral = 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50';
    $active = 'border-indigo-600 bg-indigo-600 text-white';
    $disabled = 'border-slate-200 bg-slate-50 text-slate-400 cursor-not-allowed';
@endphp

@if ($paginator instanceof \Illuminate\Pagination\Paginator && $paginator->hasPages())
    <nav {{ $attributes->merge(['class' => 'mt-6 flex items-center justify-center gap-1']) }} aria-label="Pagination">
        @if ($paginator->onFirstPage())
            <span class="{{ $button }} {{ $disabled }}" aria-disabled="true">Précédent</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="{{ $button }} {{ $neutral }}">Précédent</a>
        @endif

        @for ($page = $start; $page <= $end; $page++)
            @if ($page === $current)
                <span class="{{ $button }} {{ $active }}" aria-current="page">{{ $page }}</span>
            @else
                <a href="{{ $paginator->url($page) }}" class="{{ $button }} {{ $neutral }}">{{ $page }}</a>
            @endif
        @endfor

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="{{ $button }} {{ $neutral }}">Suivant</a>
        @else
            <span class="{{ $button }} {{ $disabled }}" aria-disabled="true">Suivant</span>
        @endif
    </nav>
@endif
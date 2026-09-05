@props(['paginator'])

@if ($paginator instanceof \Illuminate\Pagination\Paginator || $paginator instanceof \Illuminate\Pagination\LengthAwarePaginator)
    @if ($paginator->hasPages())
        <div {{ $attributes->merge(['class' => 'mt-6']) }}>
            {{ $paginator->links('pagination::tailwind') }}
        </div>
    @endif
@endif
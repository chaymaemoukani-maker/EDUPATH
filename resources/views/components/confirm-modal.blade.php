@props(['name', 'title' => null, 'maxWidth' => 'md'])

@php
    $maxWidth = [
        'sm' => 'sm:max-w-sm',
        'md' => 'sm:max-w-md',
        'lg' => 'sm:max-w-lg',
    ][$maxWidth] ?? 'sm:max-w-md';
@endphp

<div
    x-data="{ show: false }"
    x-init="$watch('show', value => {
        if (value) {
            document.body.classList.add('overflow-y-hidden');
        } else {
            document.body.classList.remove('overflow-y-hidden');
        }
    })"
    x-on:open-modal.window="$event.detail == '{{ $name }}' ? show = true : null"
    x-on:close-modal.window="$event.detail == '{{ $name }}' ? show = false : null"
    x-on:keydown.escape.window="show = false"
    x-show="show"
    class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0"
    style="display: none;"
    role="dialog"
    aria-modal="true"
    @if ($title)
        aria-labelledby="{{ $name }}-title"
    @endif
>
    <div
        x-show="show"
        class="fixed inset-0 bg-black/50"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        x-on:click="show = false"
    ></div>

    <div
        x-show="show"
        class="relative mx-auto w-full overflow-hidden rounded-2xl bg-white shadow-xl transition-all sm:my-8 {{ $maxWidth }}"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
    >
        @if ($title)
            <div class="border-b border-slate-100 px-6 py-4">
                <h3 id="{{ $name }}-title" class="text-base font-semibold text-slate-900">{{ $title }}</h3>
            </div>
        @endif
        <div class="px-6 py-5">
            {{ $slot }}
        </div>
    </div>
</div>
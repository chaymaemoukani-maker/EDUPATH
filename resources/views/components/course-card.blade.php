@props([
    'course',
    'showProgress' => false,
    'progress' => 0,
    'linkTo' => null,
    'totalModules' => null,
])

@php
    use Illuminate\Support\Facades\Route;

    if (is_array($course)) {
        $course = (object) $course;
    }

    $categoryName = $course->category?->name;
    $instructorName = $course->instructor?->name;

    if ($totalModules === null) {
        $totalModules = $course->sections->every(fn ($section) => $section->relationLoaded('modules'))
            ? $course->sections->sum(fn ($section) => $section->modules->count())
            : $course->sections()->withCount('modules')->get()->sum('modules_count');
    }

    $href = $linkTo
        ?? (Route::has('catalog.show') && ($course->status ?? null) === 'published' ? route('catalog.show', $course) : '#');

    $ctaLabel = $showProgress && $progress > 0 ? 'Continuer' : 'Voir le cours';
@endphp

<div class="flex flex-col overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm transition hover:shadow-md">
    <div class="relative block aspect-[16/9] w-full overflow-hidden bg-slate-100">
        @if ($course->image)
            <img src="{{ $course->image }}" alt="{{ $course->title }}" class="h-full w-full object-cover" />
        @else
            <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-indigo-50 to-slate-100">
                <svg class="h-10 w-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                </svg>
            </div>
        @endif

        @if (isset($course->status) && $course->status)
            <div class="absolute right-3 top-3">
                <x-badge :variant="$course->status" />
            </div>
        @endif
    </div>

    <div class="flex flex-1 flex-col gap-3 p-5">
        @if ($categoryName)
            <span class="text-xs font-semibold uppercase tracking-wide text-indigo-600">{{ $categoryName }}</span>
        @endif

        <h3 class="text-lg font-semibold leading-snug text-slate-900">{{ $course->title }}</h3>

        @if ($course->description)
            <p class="text-sm leading-relaxed text-slate-500 line-clamp-2">{{ $course->description }}</p>
        @endif

        <div class="mt-auto space-y-4">
            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-500">
                @if ($instructorName)
                    <span class="inline-flex items-center gap-1.5">
                        <svg class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        {{ $instructorName }}
                    </span>
                @endif

                @if ($totalModules)
                    <span class="inline-flex items-center gap-1.5">
                        <svg class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                        {{ $totalModules }} module{{ $totalModules > 1 ? 's' : '' }}
                    </span>
                @endif
            </div>

            @if ($showProgress)
                <x-progress-bar :percent="$progress" :size="'sm'" />
            @endif

            <x-button :href="$href" variant="{{ $showProgress ? 'secondary' : 'primary' }}" size="sm" class="w-full">
                {{ $ctaLabel }}
            </x-button>
        </div>
    </div>
</div>
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
    <div class="relative block h-44 w-full overflow-hidden bg-slate-100">
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
            <div class="absolute left-2 top-2">
                <x-badge :variant="$course->status" />
            </div>
        @endif
    </div>

    <div class="flex flex-1 flex-col p-5">
        <span class="text-xs font-medium text-slate-500">
            {{ $categoryName ?? 'EduPath' }}
        </span>

        <h3 class="mt-1.5 text-lg font-semibold leading-snug text-slate-900">{{ $course->title }}</h3>

        @if ($course->description)
            <p class="mb-3 mt-2 line-clamp-2 flex-1 text-sm text-slate-500">{{ $course->description }}</p>
        @else
            <div class="mb-3 mt-2 flex-1"></div>
        @endif

        @if ($instructorName)
            <p class="mb-3 text-xs text-slate-400">Par <span class="font-medium text-slate-600">{{ $instructorName }}</span></p>
        @endif

        @if ($showProgress)
            <div class="mb-3">
                <x-progress-bar :percent="$progress" :size="'sm'" />
            </div>
        @endif

        <div class="mb-4 flex items-center justify-between text-xs text-slate-400">
            @if ($totalModules)
                <span>{{ $totalModules }} module{{ $totalModules > 1 ? 's' : '' }}</span>
            @endif
        </div>

        <x-button :href="$href" variant="primary" size="md" class="w-full">
            {{ $ctaLabel }}
        </x-button>
    </div>
</div>
<x-dashboard-layout>
    <a href="{{ route('learner.courses.index') }}" class="inline-flex items-center gap-1 text-sm font-medium text-indigo-600 hover:text-indigo-700">
        ← Mes cours
    </a>

    <h1 class="mt-4 text-3xl font-bold text-slate-900">{{ $course->title }}</h1>
    <p class="mt-1 text-sm text-slate-500">
        {{ $course->instructor?->name ?? '—' }}
        &middot;
        {{ $course->category?->name ?? '—' }}
    </p>

    <div class="mt-6">
        <x-progress-bar :percent="$percent" />
    </div>

    @if (session('success'))
        <x-alert type="success" class="mt-6">{{ session('success') }}</x-alert>
    @endif

    @if ($certificate)
        <x-alert type="success" class="mt-6">
            Félicitations ! Vous avez terminé ce cours à 100 %.
            <x-button href="{{ route('learner.certificates.index') }}" variant="success" size="sm" class="ml-3">Voir mon certificat</x-button>
        </x-alert>
    @endif

    <div class="mt-8">
        <h2 class="text-2xl font-semibold text-slate-900">Programme du cours</h2>

        @foreach ($course->sections->sortBy('order') as $section)
            <div class="mt-6">
                <h3 class="text-sm font-semibold text-slate-700">Section {{ $loop->iteration }} — {{ $section->title }}</h3>
                <div class="mt-3 space-y-3">
                    @foreach ($section->modules->sortBy('order') as $module)
                        @php
                            $typeLabels = ['text' => 'Texte', 'video' => 'Vidéo', 'pdf' => 'PDF'];
                        @endphp
                        <a href="{{ route('learner.modules.show', $module) }}" class="flex items-center gap-3 rounded-lg border border-slate-200 bg-white px-4 py-3 transition hover:border-indigo-300 hover:shadow-sm">
                            @if (in_array($module->id, $completedModuleIds))
                                <svg class="h-5 w-5 flex-shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            @else
                                <svg class="h-5 w-5 flex-shrink-0 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            @endif
                            <div class="min-w-0 flex-1">
                                <p class="font-medium text-slate-900">{{ $module->title }}</p>
                                <p class="text-xs text-slate-500">{{ $typeLabels[$module->type] ?? $module->type }}</p>
                            </div>
                            @if ($module->quiz)
                                <x-badge variant="in-progress">Quiz</x-badge>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</x-dashboard-layout>

<x-dashboard-layout>
    <a href="{{ route('learner.courses.show', $course) }}" class="inline-flex items-center gap-1 text-sm font-medium text-indigo-600 hover:text-indigo-700">
        ← Retour au cours
    </a>

    <p class="mt-4 text-xs font-semibold uppercase tracking-wide text-slate-400">{{ $module->section->title }}</p>

    <h1 class="mt-1 text-3xl font-bold text-slate-900">{{ $module->title }}</h1>
    @php $typeLabels = ['text' => 'Texte', 'video' => 'Vidéo', 'pdf' => 'PDF']; @endphp
    <span class="mt-2 inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600">{{ $typeLabels[$module->type] ?? $module->type }}</span>

    @if (session('success'))
        <x-alert type="success" class="mt-6">{{ session('success') }}</x-alert>
    @endif
    @if (session('error'))
        <x-alert type="error" class="mt-6">{{ session('error') }}</x-alert>
    @endif

    <div class="mt-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        @if ($module->type === 'text')
            <p class="whitespace-pre-line text-lg leading-relaxed text-slate-700">{{ $module->content }}</p>
        @elseif ($module->type === 'video')
            @php
                $videoContent = $module->content;
                $isYouTube = \Illuminate\Support\Str::contains($videoContent, ['youtube.com', 'youtu.be']);
            @endphp
            @if ($isYouTube)
                @php
                    if (Str::contains($videoContent, 'youtu.be')) {
                        $videoId = collect(explode('/', $videoContent))->last();
                    } elseif (Str::contains($videoContent, 'v=')) {
                        $videoId = Str::after($videoContent, 'v=');
                    } else {
                        $videoId = collect(explode('/', $videoContent))->last();
                    }
                    $videoId = Str::before($videoId, '?');
                @endphp
                <iframe class="aspect-video w-full rounded-xl" src="https://www.youtube.com/embed/{{ $videoId }}" frameborder="0" allowfullscreen></iframe>
            @else
                <video class="aspect-video w-full rounded-xl" controls src="{{ $videoContent }}"></video>
            @endif
        @elseif ($module->type === 'pdf')
            <x-button href="{{ asset('storage/' . $module->content) }}" variant="secondary" target="_blank">Ouvrir le PDF</x-button>
            <p class="mt-2 text-xs text-slate-400">Le PDF s'ouvrira dans un nouvel onglet.</p>
        @endif
    </div>

    <div class="mt-6">
        <x-progress-bar :percent="$percent" />
    </div>

    @if ($completed)
        <x-alert type="success" class="mt-6">Vous avez terminé ce module.</x-alert>
    @else
        <div class="mt-6 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <form method="POST" action="{{ route('learner.progress.store', $module) }}">
                @csrf
                <div class="flex items-center justify-between">
                    <p class="text-sm text-slate-600">Marquez ce module comme terminé une fois que vous l'avez étudié.</p>
                    <x-button type="submit" variant="primary" size="sm">Marquer comme terminé</x-button>
                </div>
            </form>
        </div>
    @endif

    @if ($quiz)
        <div class="mt-8 border-t border-slate-200 pt-8">
            <h2 class="text-2xl font-semibold text-slate-900">Quiz du module</h2>
            <p class="mt-1 text-sm text-slate-500">Passez le quiz pour valider vos connaissances.</p>
            <x-button href="{{ route('learner.quizzes.show', $quiz) }}" variant="primary" size="sm" class="mt-4">Commencer le quiz</x-button>
        </div>
    @endif

    @if ($certificate)
        <x-alert type="success" class="mt-6">
            Félicitations ! Vous avez terminé ce cours à 100 %.
            <a href="{{ route('learner.certificates.index') }}" class="ml-1 font-medium underline">Voir mon certificat</a>
        </x-alert>
    @endif
</x-dashboard-layout>

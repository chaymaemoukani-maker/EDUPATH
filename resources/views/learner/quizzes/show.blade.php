<x-dashboard-layout>
    <a href="{{ route('learner.modules.show', $quiz->module) }}" class="inline-flex items-center gap-1 text-sm font-medium text-indigo-600 hover:text-indigo-700">
        ← Retour au module
    </a>

    <h1 class="mt-4 text-2xl font-semibold text-slate-900">{{ $quiz->title }}</h1>

    <div class="mt-2 flex flex-wrap items-center gap-4 text-sm text-slate-500">
        <span>{{ $quiz->questions->count() }} questions</span>
        <span>Score minimum : {{ $quiz->pass_score }} %</span>
        <span>Tentatives restantes : {{ $attemptsRemaining }}</span>
    </div>

    @if ($passedAlready)
        <x-alert type="success" title="Quiz réussi" class="mt-6">
            Vous avez validé ce quiz, félicitations !
        </x-alert>
        <div class="mt-4 flex gap-3">
            <x-button href="{{ route('learner.modules.show', $quiz->module) }}" variant="primary" size="sm">Revenir au module</x-button>
            <x-button href="{{ route('learner.courses.show', $course) }}" variant="secondary" size="sm">Revenir au cours</x-button>
        </div>
    @elseif ($attemptsRemaining <= 0)
        <x-alert type="error" title="Tentatives épuisées" class="mt-6">
            Vous avez épuisé toutes vos tentatives ({{ $attemptsUsed }}).
        </x-alert>
        <div class="mt-4">
            <x-button href="{{ route('learner.courses.show', $course) }}" variant="primary" size="sm">Revenir au cours</x-button>
        </div>
    @else
        @if ($lastAttempt)
            <x-alert type="{{ $lastAttempt->passed ? 'success' : 'error' }}" title="{{ $lastAttempt->passed ? 'Quiz réussi' : 'Quiz échoué' }}" class="mt-6">
                Score : {{ $lastAttempt->score }} %. Tentatives restantes : {{ $attemptsRemaining }}.
            </x-alert>
        @endif

        <form method="POST" action="{{ route('learner.quizzes.store', $quiz) }}" class="mt-6">
            @csrf

            <div class="space-y-6">
                @foreach ($quiz->questions as $question)
                    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                        <p class="font-medium text-slate-900">{{ $question->text }}</p>
                        <div class="mt-4 space-y-3">
                            @foreach ($question->answers as $answer)
                                <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-slate-200 px-4 py-3 transition hover:border-indigo-300 hover:bg-indigo-50/30">
                                    <input type="radio" name="answers[{{ $question->id }}]" value="{{ $answer->id }}" {{ $loop->first ? 'required' : '' }} class="form-radio mr-2 text-indigo-600" />
                                    <span class="text-sm text-slate-700">{{ $answer->text }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('answers.'.$question->id)
                            <x-input-error :messages="$errors->get('answers.'.$question->id)" class="mt-2" />
                        @enderror
                    </div>
                @endforeach
            </div>

            <div class="mt-6">
                <x-button type="submit" variant="primary">Valider mes réponses</x-button>
            </div>
        </form>
    @endif
</x-dashboard-layout>

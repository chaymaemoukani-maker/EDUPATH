<x-dashboard-layout>
    @php
        $quiz = $module->quiz;
        $moduleTypeLabel = match ($module->type) {
            'video' => 'Vidéo',
            'pdf' => 'PDF',
            default => 'Texte',
        };
        $initialQuestions = [];
        if ($quiz) {
            foreach ($quiz->questions as $q) {
                $answers = $q->answers->pluck('text')->values()->all();
                $correct = 0;
                foreach ($q->answers as $i => $a) {
                    if ($a->is_correct) {
                        $correct = $i;
                        break;
                    }
                }
                $initialQuestions[] = ['text' => $q->text, 'answers' => $answers, 'correct_answer' => $correct];
            }
        }
    @endphp

    <header class="mb-8">
        <h1 class="text-3xl font-bold text-slate-900">Quiz du module : {{ $module->title }}</h1>
        <div class="mt-2 flex flex-wrap items-center gap-3">
            <a href="{{ route('instructor.courses.curriculum', $module->section->course) }}" class="text-sm text-slate-500 hover:text-slate-700">&larr; Retour au curriculum</a>
            <x-badge :variant="$module->section->course->status" />
        </div>
    </header>

    @if (session('success'))
        <x-alert type="success" class="mb-6">{{ session('success') }}</x-alert>
    @endif
    @if (session('error'))
        <x-alert type="error" class="mb-6">{{ session('error') }}</x-alert>
    @endif

    @if ($errors->any())
        <x-alert type="error" class="mb-6">
            <span class="font-medium text-red-700">Erreur de validation :</span>
            <ul class="mt-1 list-disc ps-4">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-alert>
    @endif

    <x-card class="mb-6">
        <p class="text-sm text-slate-600">
            <span class="font-medium text-slate-900">Module associé :</span> {{ $module->title }} ({{ $moduleTypeLabel }})
        </p>
    </x-card>

    <x-card>
        <form method="POST"
              action="{{ $quiz ? route('instructor.quizzes.update', $quiz) : route('instructor.quizzes.store') }}"
              x-data="quizBuilder(@js($initialQuestions))"
              @submit="document.getElementById('questions-json').value = JSON.stringify(questions);">
            @csrf
            @method($quiz ? 'PATCH' : 'POST')
            @if (!$quiz)
                <input type="hidden" name="module_id" value="{{ $module->id }}">
            @endif

            <div>
                <x-input-label for="quiz-title" value="Titre du quiz" />
                <x-input id="quiz-title" name="title" value="{{ old('title', $quiz?->title) }}" class="mt-1" required />
                <x-input-error :messages="$errors->get('title')" class="mt-2" />
            </div>

            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <x-input-label for="pass_score" value="Score minimum (%)" />
                    <x-input id="pass_score" name="pass_score" type="number" min="0" max="100" value="{{ old('pass_score', $quiz?->pass_score ?? 50) }}" class="mt-1" />
                    <x-input-error :messages="$errors->get('pass_score')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="max_attempts" value="Tentatives maximum" />
                    <x-input id="max_attempts" name="max_attempts" type="number" min="1" max="10" value="{{ old('max_attempts', $quiz?->max_attempts ?? 3) }}" class="mt-1" />
                    <x-input-error :messages="$errors->get('max_attempts')" class="mt-2" />
                </div>
            </div>

            <div class="mt-6">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h2 class="text-base font-semibold text-slate-900">Questions</h2>
                    <x-button type="button" variant="secondary" size="sm" @click="addQuestion()">Ajouter une question</x-button>
                </div>

                <div class="mt-4 space-y-4">
                    <template x-for="(question, qIndex) in questions" x-bind:key="qIndex">
                        <div class="rounded-lg border border-slate-200 p-4">
                            <div class="flex items-center justify-between">
                                <h3 class="text-sm font-semibold text-slate-900" x-text="'Question ' + (qIndex + 1)"></h3>
                                <button type="button" @click="removeQuestion(qIndex)" class="text-sm text-red-600 hover:text-red-700">Supprimer</button>
                            </div>
                            <div class="mt-3">
                                <x-input-label value="Intitulé de la question" />
                                <x-textarea x-model="question.text" rows="2" class="mt-1" required></x-textarea>
                            </div>
                            <div class="mt-4">
                                <div class="flex items-center justify-between">
                                    <x-input-label value="Réponses" />
                                    <button type="button" @click="addAnswer(qIndex)" class="text-xs font-medium text-indigo-600 hover:text-indigo-700">+ Ajouter une réponse</button>
                                </div>
                                <div class="mt-2 space-y-2">
                                    <template x-for="(answer, aIndex) in question.answers" x-bind:key="aIndex">
                                        <div class="flex items-center gap-3">
                                            <input type="radio" x-bind:value="aIndex" x-model="question.correct_answer" class="h-4 w-4 border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                            <input type="text" x-model="question.answers[aIndex]" class="flex-1 rounded-lg border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 focus:ring-opacity-40" placeholder="Réponse" required>
                                            <button type="button" @click="removeAnswer(qIndex, aIndex)" x-show="question.answers.length > 2" class="text-xs text-red-600 hover:text-red-700">Retirer</button>
                                        </div>
                                    </template>
                                </div>
                                <p class="mt-2 text-xs text-slate-500">Cochez la réponse correcte.</p>
                            </div>
                        </div>
                    </template>

                    <p x-show="questions.length === 0" class="rounded-lg border border-dashed border-slate-200 p-6 text-center text-sm text-slate-500">
                        Aucune question pour le moment. Ajoutez-en au moins une.
                    </p>
                </div>

                <input type="hidden" name="questions_json" id="questions-json" value="{{ old('questions_json') }}">
            </div>

            <div class="mt-6">
                <x-button type="submit" variant="primary">{{ $quiz ? 'Enregistrer le quiz' : 'Créer le quiz' }}</x-button>
            </div>
        </form>

        <p class="mt-4 text-xs text-slate-500">
            Chaque question doit comporter au moins 2 réponses et une réponse correcte. Le quiz est rattaché au module ; il n'est jamais un type de module.
        </p>
    </x-card>

    <script>
        document.addEventListener('alpine:init', () => {
            window.Alpine.data('quizBuilder', (initialQuestions) => ({
                questions: initialQuestions,
                addQuestion() {
                    this.questions.push({ text: '', answers: ['', ''], correct_answer: 0 });
                },
                removeQuestion(qIndex) {
                    this.questions.splice(qIndex, 1);
                },
                addAnswer(qIndex) {
                    this.questions[qIndex].answers.push('');
                },
                removeAnswer(qIndex, aIndex) {
                    this.questions[qIndex].answers.splice(aIndex, 1);
                },
            }));
        });
    </script>
</x-dashboard-layout>
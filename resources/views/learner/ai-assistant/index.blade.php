<x-dashboard-layout :pageTitle="'Assistant IA'">
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-600">
                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456z" />
                </svg>
            </span>
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Assistant IA</h1>
                <p class="text-sm text-slate-500">Posez vos questions sur vos cours, simplifiez les concepts, préparez vos quiz.</p>
            </div>
        </div>
    </x-slot>

    @if (session('success'))
        <x-alert type="success" class="mb-6">{{ session('success') }}</x-alert>
    @endif
    @if (session('error'))
        <x-alert type="error" class="mb-6">{{ session('error') }}</x-alert>
    @endif

    @if ($courses->isEmpty())
        <div class="rounded-xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center">
            <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-indigo-50">
                <svg class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456z" />
                </svg>
            </div>
            <h3 class="text-base font-semibold text-slate-900">Aucun cours en cours</h3>
            <p class="mx-auto mt-1 max-w-md text-sm text-slate-500">
                Vous devez être inscrit à au moins un cours pour utiliser l’assistant IA.
            </p>
            <x-button href="{{ route('catalog') }}" variant="primary" size="sm" class="mt-5">
                Parcourir le catalogue
            </x-button>
        </div>
    @else
        @php
            $selectedCourse = $courses->first(fn ($item) => $item->course->id === $selectedCourseId);
            $selectedPercent = $selectedCourse?->percent ?? 0;
        @endphp

        <div x-data="{
            question: @js(old('question', '')),
            loading: false,
            suggestions: [
                'Explique-moi simplement le module que je suis en train d’étudier.',
                'Quels sont les concepts les plus importants de ce module ?',
                'Quelle est ma prochaine étape d’apprentissage dans ce cours ?',
                'Aide-moi à me préparer pour le quiz de ce module.',
            ],
            setQuestion(q) {
                this.question = q;
                this.$refs.question.focus();
            },
        }" class="mx-auto max-w-3xl">
            <form
                method="GET"
                action="{{ route('learner.ai-assistant.index') }}"
                class="flex items-end gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm"
            >
                <div class="min-w-0 flex-1">
                    <label for="course_id" class="mb-1.5 block text-sm font-medium text-slate-700">Cours</label>
                    <x-select
                        name="course"
                        id="course_id"
                        x-on:change="window.location = $event.target.value ? '?course=' + $event.target.value : window.location.pathname"
                    >
                        @foreach ($courses as $item)
                            <option value="{{ $item->course->id }}" @selected($item->course->id === $selectedCourseId)>
                                {{ $item->course->title }} ({{ $item->percent }} %)
                            </option>
                        @endforeach
                    </x-select>
                </div>
                <x-button variant="secondary" size="md" class="shrink-0">Changer</x-button>
            </form>

            <div class="mt-4">
                <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-400">Suggestions</p>
                <div class="flex flex-wrap gap-2">
                    <template x-for="suggestion in suggestions" :key="suggestion">
                        <button
                            type="button"
                            @click="setQuestion(suggestion)"
                            class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-sm text-slate-600 shadow-sm transition-colors hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700"
                        >
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.126" />
                            </svg>
                            <span x-text="suggestion"></span>
                        </button>
                    </template>
                </div>
            </div>

            <div class="mt-4 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center justify-between gap-3 border-b border-slate-200 bg-white px-4 py-3 sm:px-5">
                    <div class="flex min-w-0 items-center gap-2.5">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-indigo-50">
                            <svg class="h-4 w-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                            </svg>
                        </span>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-slate-900">{{ $selectedCourse?->course?->title }}</p>
                            <p class="text-xs text-slate-400">Contexte de l’assistant</p>
                        </div>
                    </div>
                    <span class="inline-flex shrink-0 items-center rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700">
                        {{ $selectedPercent }} % terminé
                    </span>
                </div>

                <div class="max-h-[60vh] space-y-5 overflow-y-auto bg-slate-50 px-4 py-6 sm:px-5">
                    @forelse ($conversation as $message)
                        @if ($message['role'] === 'user')
                            <div class="flex justify-end">
                                <div class="max-w-[85%] rounded-2xl rounded-br-md bg-indigo-600 px-4 py-2.5 text-sm leading-relaxed text-white shadow-sm">
                                    <p class="whitespace-pre-line">{{ $message['content'] }}</p>
                                </div>
                            </div>
                        @else
                            <div class="flex items-start gap-2.5">
                                <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-indigo-600">
                                    <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456z" />
                                    </svg>
                                </span>
                                <div class="max-w-[85%] rounded-2xl rounded-tl-md border border-slate-200 bg-white px-4 py-2.5 text-sm leading-relaxed text-slate-700 shadow-sm">
                                    <p class="whitespace-pre-line">{!! nl2br(e($message['content'])) !!}</p>
                                </div>
                            </div>
                        @endif
                    @empty
                        <div class="flex flex-col items-center px-4 py-10 text-center">
                            <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-white shadow-sm">
                                <svg class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456z" />
                                </svg>
                            </div>
                            <p class="mx-auto max-w-md text-sm text-slate-500">
                                Bonjour ! Posez une question sur ce cours ci-dessous, ou utilisez une suggestion pour démarrer.
                            </p>
                        </div>
                    @endforelse
                </div>

                <form
                    method="POST"
                    action="{{ route('learner.ai-assistant.ask') }}"
                    class="border-t border-slate-200 bg-white p-4 sm:p-5"
                    @submit="loading = true"
                >
                    @csrf
                    <input type="hidden" name="course_id" value="{{ $selectedCourseId }}">

                    <div class="flex items-end gap-3">
                        <label for="question" class="sr-only">Votre question</label>
                        <div class="flex-1">
                            <x-textarea
                                id="question"
                                name="question"
                                x-ref="question"
                                x-model="question"
                                rows="2"
                                placeholder="Ex. : explique-moi la notion d’authentification dans ce module…"
                                class="resize-y"
                            >{{ old('question') }}</x-textarea>
                        </div>
                        <x-button
                            type="submit"
                            variant="primary"
                            class="shrink-0"
                            x-bind:disabled="loading || question.trim().length < 3"
                        >
                            <svg x-show="!loading" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                            </svg>
                            <span x-show="!loading">Envoyer</span>
                            <svg x-show="loading" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            <span x-show="loading">Envoi…</span>
                        </x-button>
                    </div>

                    @error('question')
                        <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                    @error('course_id')
                        <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                    @enderror

                    <p class="mt-3 text-xs text-slate-400">L’assistant répond uniquement à partir du contenu de ce cours.</p>
                </form>
            </div>
        </div>
    @endif
</x-dashboard-layout>
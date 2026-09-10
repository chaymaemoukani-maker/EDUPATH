<x-dashboard-layout>
    <header class="mb-8">
        <div class="flex flex-wrap items-center gap-3">
            <h1 class="text-3xl font-bold text-slate-900">Curriculum — {{ $course->title }}</h1>
            <x-badge :variant="$course->status" />
        </div>
        <div class="mt-2 flex flex-wrap items-center gap-4">
            <a href="{{ route('instructor.courses.index') }}" class="text-sm text-slate-500 hover:text-slate-700">&larr; Mes cours</a>
            <a href="{{ route('instructor.courses.edit', $course) }}" class="text-sm text-indigo-600 hover:text-indigo-700">Modifier le cours</a>
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

    @if ($course->sections->isEmpty())
        <div class="mb-8 rounded-xl border border-dashed border-slate-200 bg-white p-10 text-center text-sm text-slate-500">
            Aucune section pour le moment. Ajoutez la première section.
        </div>
    @endif

    <x-card class="mb-8">
        <h2 class="mb-4 text-base font-semibold text-slate-900">Nouvelle section</h2>
        <form method="POST" action="{{ route('instructor.sections.store') }}" class="flex flex-wrap items-end gap-4">
            @csrf
            <input type="hidden" name="course_id" value="{{ $course->id }}">
            <div class="flex-1 min-w-56">
                <x-input-label for="new-section-title" value="Titre de la section" />
                <x-input id="new-section-title" name="title" value="{{ old('title') }}" class="mt-1" placeholder="Ex. Introduction" />
                <x-input-error :messages="$errors->get('title')" class="mt-2" />
            </div>
            <x-button type="submit" variant="primary" size="sm">Ajouter la section</x-button>
        </form>
    </x-card>

    @foreach ($course->sections as $section)
        <x-card class="mb-6" x-data="{ editingSection: false, editingNewModule: false }">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="min-w-0">
                    <h2 class="font-semibold text-slate-900">Section {{ $loop->iteration }} — {{ $section->title }}</h2>
                    <p class="mt-0.5 text-xs text-slate-500">Ordre {{ $section->order }}</p>
                </div>
                <div class="flex shrink-0 items-center gap-2">
                    <x-button type="button" variant="secondary" size="sm" @click="editingSection = !editingSection">Modifier</x-button>
                    <x-button type="button" variant="danger" size="sm" @click="$dispatch('open-modal', 'delete-section-{{ $section->id }}')">Supprimer</x-button>
                </div>
            </div>

            <form method="POST" action="{{ route('instructor.sections.update', $section) }}"
                  x-show="editingSection" style="display: none;" class="mt-4 flex flex-wrap items-end gap-4">
                @csrf
                @method('PATCH')
                <div class="flex-1 min-w-56">
                    <x-input name="title" :value="$section->title" />
                    <x-input-error :messages="$errors->get('title')" class="mt-2" />
                </div>
                <x-button type="submit" variant="primary" size="sm">Enregistrer</x-button>
            </form>

            @if ($section->modules->isEmpty())
                <div class="mt-4 rounded-lg border border-dashed border-slate-200 p-4 text-center text-sm text-slate-500">
                    Aucun module pour le moment.
                </div>
            @endif

            <ul class="mt-4 divide-y divide-slate-100 rounded-lg border border-slate-100">
                @foreach ($section->modules as $module)
                    <li x-data="{ editingModule: false }">
                        <div class="flex items-center justify-between gap-4 px-4 py-3">
                            <div class="flex min-w-0 items-center gap-3">
                                <span class="text-slate-400">
                                    @if ($module->type === 'video')
                                        ▶
                                    @elseif ($module->type === 'text')
                                        ¶
                                    @else
                                        ▤
                                    @endif
                                </span>
                                <div class="min-w-0">
                                    <div class="truncate text-sm font-medium text-slate-900">{{ $module->title }}</div>
                                    <div class="text-xs text-slate-500">
                                        @if ($module->type === 'video')
                                            Vidéo
                                        @elseif ($module->type === 'text')
                                            Texte
                                        @else
                                            PDF
                                        @endif
                                        ·
                                        @if ($module->quiz)
                                            Quiz : {{ $module->quiz->title }}
                                        @else
                                            Aucun quiz
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="flex shrink-0 items-center gap-2">
                                <x-button href="{{ route('instructor.quizzes.edit', $module) }}" variant="secondary" size="sm">Quiz</x-button>
                                <x-button type="button" variant="secondary" size="sm" @click="editingModule = !editingModule">Modifier</x-button>
                                <x-button type="button" variant="danger" size="sm" @click="$dispatch('open-modal', 'delete-module-{{ $module->id }}')">Supprimer</x-button>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('instructor.modules.update', $module) }}"
                              x-show="editingModule" style="display: none;" x-data="{ type: '{{ $module->type }}' }"
                              class="border-t border-slate-100 bg-slate-50/50 px-4 py-4">
                            @csrf
                            @method('PATCH')
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                <div>
                                    <x-input-label for="module-title-{{ $module->id }}" value="Titre du module" />
                                    <x-input id="module-title-{{ $module->id }}" name="title" value="{{ old('title', $module->title) }}" class="mt-1" required />
                                    <x-input-error :messages="$errors->get('title')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label value="Type" />
                                    <x-select name="type" x-model="type" class="mt-1">
                                        <option value="text" @selected(old('type', $module->type) === 'text')>Texte</option>
                                        <option value="video" @selected(old('type', $module->type) === 'video')>Vidéo</option>
                                        <option value="pdf" @selected(old('type', $module->type) === 'pdf')>PDF</option>
                                    </x-select>
                                </div>
                            </div>

                            <div class="mt-3" x-show="type === 'text'">
                                <x-input-label value="Contenu" />
                                <x-textarea name="content" rows="3" class="mt-1">{{ $module->type === 'text' ? $module->content : '' }}</x-textarea>
                            </div>
                            <div class="mt-3" x-show="type === 'video'">
                                <x-input-label value="URL de la vidéo" />
                                <x-input name="content" type="url" value="{{ $module->type === 'video' ? $module->content : '' }}" class="mt-1" placeholder="https://..." />
                            </div>
                            <div class="mt-3" x-show="type === 'pdf'">
                                <x-input-label value="Fichier PDF" />
                                <x-input name="content" type="file" accept="application/pdf" class="mt-1" />
                                @if ($module->type === 'pdf' && $module->content)
                                    <p class="mt-1 text-xs text-slate-500">Fichier actuel : {{ $module->content }}</p>
                                @endif
                            </div>

                            <div class="mt-4 flex items-center gap-3">
                                <x-button type="submit" variant="primary" size="sm">Enregistrer</x-button>
                                <x-button type="button" variant="secondary" size="sm" @click="editingModule = false">Annuler</x-button>
                            </div>
                        </form>
                    </li>
                @endforeach
            </ul>

            <div class="mt-4">
                <x-button type="button" variant="secondary" size="sm" @click="editingNewModule = !editingNewModule">Ajouter un module</x-button>

                <form method="POST" action="{{ route('instructor.modules.store') }}"
                      x-show="editingNewModule" style="display: none;"
                      class="mt-4 space-y-4 rounded-lg border border-slate-200 bg-slate-50 p-4">
                    @csrf
                    <input type="hidden" name="section_id" value="{{ $section->id }}">
                    <div x-data="{ type: 'text' }">
                        <div>
                            <x-input-label for="module-title-{{ $section->id }}" value="Titre du module" />
                            <x-input id="module-title-{{ $section->id }}" name="title" class="mt-1" required />
                            <x-input-error :messages="$errors->get('title')" class="mt-2" />
                        </div>
                        <div class="mt-3">
                            <x-input-label value="Type" />
                            <x-select name="type" x-model="type" class="mt-1">
                                <option value="text" @selected(old('type') === 'text')>Texte</option>
                                <option value="video" @selected(old('type') === 'video')>Vidéo</option>
                                <option value="pdf" @selected(old('type') === 'pdf')>PDF</option>
                            </x-select>
                        </div>
                        <div class="mt-3" x-show="type === 'text'">
                            <x-input-label value="Contenu" />
                            <x-textarea name="content" rows="3" class="mt-1">{{ old('content') }}</x-textarea>
                        </div>
                        <div class="mt-3" x-show="type === 'video'">
                            <x-input-label value="URL de la vidéo" />
                            <x-input name="content" type="url" value="{{ old('content') }}" class="mt-1" placeholder="https://..." />
                        </div>
                        <div class="mt-3" x-show="type === 'pdf'">
                            <x-input-label value="Fichier PDF" />
                            <x-input name="content" type="file" accept="application/pdf" class="mt-1" />
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <x-button type="submit" size="sm" variant="primary">Ajouter</x-button>
                        <x-button type="button" size="sm" variant="secondary" @click="editingNewModule = false">Annuler</x-button>
                    </div>
                </form>
            </div>
        </x-card>

        <x-confirm-modal name="delete-section-{{ $section->id }}" title="Supprimer la section">
            <p class="text-sm text-slate-600">
                Voulez-vous vraiment supprimer la section &laquo; {{ $section->title }} &raquo; ? Ses modules et quiz seront supprimés.
            </p>
            <div class="mt-6 flex justify-end gap-3">
                <x-button type="button" variant="secondary" @click="$dispatch('close-modal', 'delete-section-{{ $section->id }}')">Annuler</x-button>
                <form method="POST" action="{{ route('instructor.sections.destroy', $section) }}">
                    @csrf
                    @method('DELETE')
                    <x-button type="submit" variant="danger">Supprimer</x-button>
                </form>
            </div>
        </x-confirm-modal>

        @foreach ($section->modules as $module)
            <x-confirm-modal name="delete-module-{{ $module->id }}" title="Supprimer le module">
                <p class="text-sm text-slate-600">
                    Voulez-vous vraiment supprimer le module &laquo; {{ $module->title }} &raquo; ? Son quiz sera supprimé.
                </p>
                <div class="mt-6 flex justify-end gap-3">
                    <x-button type="button" variant="secondary" @click="$dispatch('close-modal', 'delete-module-{{ $module->id }}')">Annuler</x-button>
                    <form method="POST" action="{{ route('instructor.modules.destroy', $module) }}">
                        @csrf
                        @method('DELETE')
                        <x-button type="submit" variant="danger">Supprimer</x-button>
                    </form>
                </div>
            </x-confirm-modal>
        @endforeach
    @endforeach
</x-dashboard-layout>
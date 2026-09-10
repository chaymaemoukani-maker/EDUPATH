<x-dashboard-layout>
    <div class="mb-8 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('instructor.courses.index') }}" class="text-sm text-slate-500 hover:text-slate-700">&larr; Retour</a>
            <h1 class="text-3xl font-bold text-slate-900">Modifier le cours</h1>
            <x-badge :variant="$course->status" />
        </div>
    </div>

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

    <x-card class="max-w-2xl">
        <form method="POST" action="{{ route('instructor.courses.update', $course) }}">
            @csrf
            @method('PATCH')

            <div class="space-y-6">
                <div>
                    <x-input-label for="title" value="Titre" />
                    <x-input id="title" name="title" value="{{ old('title', $course->title) }}" class="mt-1" required />
                    <x-input-error :messages="$errors->get('title')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="description" value="Description" />
                    <x-textarea id="description" name="description" rows="5" class="mt-1">{{ old('description', $course->description) }}</x-textarea>
                    <x-input-error :messages="$errors->get('description')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="category_id" value="Catégorie" />
                    <x-select name="category_id" class="mt-1" placeholder="Sélectionner une catégorie">
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id', $course->category_id) == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </x-select>
                    <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="image" value="Image (URL)" />
                    <x-input id="image" name="image" value="{{ old('image', $course->image) }}" class="mt-1" placeholder="https://..." />
                    <p class="mt-1 text-xs text-slate-500">Facultatif. Une URL d'image sera affichée sur le catalogue.</p>
                    @if ($course->image)
                        <img src="{{ $course->image }}" alt="" class="mt-1 h-24 w-40 rounded-lg border border-slate-200 object-cover">
                    @endif
                    <x-input-error :messages="$errors->get('image')" class="mt-2" />
                </div>

                <div class="pt-2">
                    <x-button type="submit" variant="primary">Enregistrer les modifications</x-button>
                </div>
            </div>
        </form>
    </x-card>

    <x-card class="mt-6 max-w-2xl">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-base font-semibold text-slate-900">Curriculum</h2>
                <p class="mt-1 text-sm text-slate-500">Gérez les sections et modules de votre cours.</p>
            </div>
            <x-button href="{{ route('instructor.courses.curriculum', $course) }}" variant="secondary">
                Gérer les sections et modules
            </x-button>
        </div>
    </x-card>
</x-dashboard-layout>

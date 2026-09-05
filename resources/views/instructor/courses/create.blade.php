<x-dashboard-layout>
    <div class="mb-8 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('instructor.courses.index') }}" class="text-sm text-slate-500 hover:text-slate-700">&larr; Retour</a>
            <h1 class="text-2xl font-semibold text-slate-900">Créer un cours</h1>
        </div>
    </div>

    <x-card class="max-w-2xl">
        <form method="POST" action="{{ route('instructor.courses.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="space-y-6">
                <div>
                    <x-input-label for="title" value="Titre" />
                    <x-input id="title" name="title" value="{{ old('title') }}" class="mt-1" required />
                    <x-input-error :messages="$errors->get('title')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="description" value="Description" />
                    <x-textarea id="description" name="description" rows="5" class="mt-1">{{ old('description') }}</x-textarea>
                    <x-input-error :messages="$errors->get('description')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="category_id" value="Catégorie" />
                    <x-select name="category_id" class="mt-1" placeholder="Sélectionner une catégorie">
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </x-select>
                    <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="image" value="Image (URL)" />
                    <x-input id="image" name="image" value="{{ old('image') }}" class="mt-1" placeholder="https://..." />
                    <p class="mt-1 text-xs text-slate-500">Facultatif. Une URL d'image sera affichée sur le catalogue.</p>
                    <x-input-error :messages="$errors->get('image')" class="mt-2" />
                </div>

                <div class="pt-2">
                    <x-button type="submit" variant="primary">Créer le cours</x-button>
                </div>
            </div>
        </form>
    </x-card>
</x-dashboard-layout>

<x-dashboard-layout>
    <div class="mb-8 flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-slate-900">Gestion des catégories</h1>
    </div>

    @if (session('success'))
        <x-alert type="success" class="mb-6">{{ session('success') }}</x-alert>
    @endif
    @if (session('error'))
        <x-alert type="error" class="mb-6">{{ session('error') }}</x-alert>
    @endif

    <x-card class="mb-6">
        <h2 class="mb-4 text-base font-semibold text-slate-900">Nouvelle catégorie</h2>
        <form method="POST" action="{{ route('admin.categories.store') }}">
            @csrf
            <x-input-label for="name" value="Nom" />
            <x-input id="name" name="name" value="{{ old('name') }}" class="mt-1" required />
            @error('name')
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            @enderror
            <div class="mt-4">
                <x-button type="submit" variant="primary" size="sm">Créer</x-button>
            </div>
        </form>
    </x-card>

    <x-card :padding="false">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Nom</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Slug</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Nombre de cours</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold uppercase text-slate-500">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
                @forelse ($categories as $category)
                    <tr class="hover:bg-slate-50">
                        <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-slate-900">{{ $category->name }}</td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-500">{{ $category->slug }}</td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-500">{{ $category->courses_count }}</td>
                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                            <div class="flex items-center justify-end gap-2">
                                <x-button variant="secondary" size="sm" type="button" @click="$dispatch('open-modal', 'edit-category-{{ $category->id }}')">Modifier</x-button>
                                <x-button variant="danger" size="sm" type="button" @click="$dispatch('open-modal', 'delete-category-{{ $category->id }}')">Supprimer</x-button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-sm text-slate-500">Aucune catégorie trouvée.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-card>

    @foreach ($categories as $category)
        <x-confirm-modal name="edit-category-{{ $category->id }}" title="Modifier la catégorie">
            <form method="POST" action="{{ route('admin.categories.update', $category) }}">
                @csrf @method('PATCH')
                <x-input-label for="name-{{ $category->id }}" value="Nom" />
                <x-input id="name-{{ $category->id }}" name="name" value="{{ $category->name }}" class="mt-1" required />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                <div class="mt-6 flex justify-end gap-3">
                    <x-button type="button" variant="secondary" @click="$dispatch('close-modal', 'edit-category-{{ $category->id }}')">Annuler</x-button>
                    <x-button type="submit" variant="primary">Enregistrer</x-button>
                </div>
            </form>
        </x-confirm-modal>

        <x-confirm-modal name="delete-category-{{ $category->id }}" title="Supprimer la catégorie">
            <p class="text-sm text-slate-600">Voulez-vous vraiment supprimer la catégorie « {{ $category->name }} » ? Les cours associés ne seront pas supprimés.</p>
            <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" class="mt-6 flex justify-end gap-3">
                @csrf @method('DELETE')
                <x-button type="button" variant="secondary" @click="$dispatch('close-modal', 'delete-category-{{ $category->id }}')">Annuler</x-button>
                <x-button type="submit" variant="danger">Supprimer</x-button>
            </form>
        </x-confirm-modal>
    @endforeach

    <x-pagination :paginator="$categories" />
</x-dashboard-layout>

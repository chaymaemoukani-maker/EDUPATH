<x-dashboard-layout>
    <div class="mb-8 flex items-center justify-between">
        <h1 class="text-3xl font-bold text-slate-900">Contrôle des cours</h1>
    </div>

    @if (session('success'))
        <x-alert type="success" class="mb-6">{{ session('success') }}</x-alert>
    @endif
    @if (session('error'))
        <x-alert type="error" class="mb-6">{{ session('error') }}</x-alert>
    @endif

    <form method="GET" action="{{ route('admin.courses.index') }}" class="mb-6 flex flex-wrap items-end gap-4">
        <div>
            <x-input-label value="Recherche" for="search" />
            <x-input id="search" name="search" value="{{ request('search') }}" class="mt-1 w-64" placeholder="Rechercher un cours..." />
        </div>
        <div>
            <x-input-label value="Statut" for="status" />
            <x-select id="status" name="status" class="mt-1 w-48">
                <option value="">Tous les statuts</option>
                <option value="published" @selected(request('status') === 'published')>Publiés</option>
                <option value="draft" @selected(request('status') === 'draft')>Brouillons</option>
            </x-select>
        </div>
        <x-button type="submit" variant="primary" size="sm">Filtrer</x-button>
    </form>

    <x-card :padding="false">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Titre</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Formateur</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Catégorie</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Statut</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Inscrits</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold uppercase text-slate-500">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
                @forelse ($courses as $course)
                    <tr class="hover:bg-slate-50">
                        <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-slate-900">{{ $course->title }}</td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-500">{{ $course->instructor?->name }}</td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-500">{{ $course->category?->name }}</td>
                        <td class="whitespace-nowrap px-6 py-4"><x-badge :variant="$course->status" /></td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-500">{{ $course->enrollments_count }}</td>
                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                            @if ($course->status === 'published')
                                <form method="POST" action="{{ route('admin.courses.unpublish', $course) }}">
                                    @csrf @method('PATCH')
                                    <x-button type="submit" variant="secondary" size="sm">Dépublier</x-button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.courses.publish', $course) }}">
                                    @csrf @method('PATCH')
                                    <x-button type="submit" variant="success" size="sm">Publier</x-button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-sm text-slate-500">Aucun cours trouvé.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-card>

    <x-pagination :paginator="$courses" />
</x-dashboard-layout>

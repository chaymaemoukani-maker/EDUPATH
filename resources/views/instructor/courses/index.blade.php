<x-dashboard-layout>
    <div class="mb-8 flex items-center justify-between">
        <h1 class="text-3xl font-bold text-slate-900">Mes cours</h1>
        <x-button href="{{ route('instructor.courses.create') }}" variant="primary">
            Créer un cours
        </x-button>
    </div>

    @if (session('success'))
        <x-alert type="success" class="mb-6">{{ session('success') }}</x-alert>
    @endif
    @if (session('error'))
        <x-alert type="error" class="mb-6">{{ session('error') }}</x-alert>
    @endif

    <x-card padding="false">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50 text-xs font-semibold uppercase text-slate-500">
                        <th class="px-6 py-3">Titre</th>
                        <th class="px-6 py-3">Catégorie</th>
                        <th class="px-6 py-3">Statut</th>
                        <th class="px-6 py-3 text-right">Inscrits</th>
                        <th class="px-6 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($courses as $course)
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-4">
                                <div class="font-medium text-slate-900">{{ $course->title }}</div>
                                <div class="text-xs text-slate-500">{{ Str::limit($course->description, 60) }}</div>
                            </td>
                            <td class="px-6 py-4 text-slate-600">{{ $course->category?->name ?? '—' }}</td>
                            <td class="px-6 py-4"><x-badge :variant="$course->status" /></td>
                            <td class="px-6 py-4 text-right text-slate-600">{{ $course->enrollments_count }}</td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <x-button href="{{ route('instructor.courses.edit', $course) }}" variant="secondary" size="sm">
                                        Modifier
                                    </x-button>
                                    <x-button href="{{ route('instructor.courses.curriculum', $course) }}" variant="secondary" size="sm">
                                        Sections
                                    </x-button>
                                    <x-button type="button" variant="danger" size="sm" @click="$dispatch('open-modal', 'delete-course-{{ $course->id }}')">
                                        Supprimer
                                    </x-button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                                Vous n'avez pas encore de cours.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>

    <x-pagination :paginator="$courses" />

    @foreach ($courses as $course)
        <x-confirm-modal name="delete-course-{{ $course->id }}" title="Supprimer le cours">
            <p class="text-sm text-slate-600">
                Voulez-vous vraiment supprimer &laquo; {{ $course->title }} &raquo; ? Toutes les sections, les modules et les quiz associés seront supprimés.
            </p>
            <div class="mt-6 flex justify-end gap-3">
                <x-button type="button" variant="secondary" @click="$dispatch('close-modal', 'delete-course-{{ $course->id }}')">
                    Annuler
                </x-button>
                <form method="POST" action="{{ route('instructor.courses.destroy', $course) }}">
                    @csrf
                    @method('DELETE')
                    <x-button type="submit" variant="danger">Supprimer</x-button>
                </form>
            </div>
        </x-confirm-modal>
    @endforeach
</x-dashboard-layout>

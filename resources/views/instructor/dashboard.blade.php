<x-dashboard-layout>
    <div class="mb-8 flex items-center justify-between">
        <h1 class="text-3xl font-bold text-slate-900">Dashboard</h1>
        <x-button href="{{ route('instructor.courses.create') }}" variant="primary" size="sm">
            Créer un cours
        </x-button>
    </div>

    @if (session('success'))
        <x-alert type="success" class="mb-6">{{ session('success') }}</x-alert>
    @endif
    @if (session('error'))
        <x-alert type="error" class="mb-6">{{ session('error') }}</x-alert>
    @endif

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <x-card>
            <div class="text-3xl font-bold text-slate-900">{{ $totalCourses }}</div>
            <div class="mt-1 text-sm text-slate-500">Cours</div>
        </x-card>
        <x-card>
            <div class="text-3xl font-bold text-slate-900">{{ $publishedCourses }}</div>
            <div class="mt-1 text-sm text-slate-500">Cours publiés</div>
        </x-card>
        <x-card>
            <div class="text-3xl font-bold text-slate-900">{{ $draftCourses }}</div>
            <div class="mt-1 text-sm text-slate-500">Brouillons</div>
        </x-card>
        <x-card>
            <div class="text-3xl font-bold text-slate-900">{{ $totalLearners }}</div>
            <div class="mt-1 text-sm text-slate-500">Apprenants inscrits</div>
        </x-card>
    </div>

    <div class="mt-10">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-2xl font-semibold text-slate-900">Mes cours</h2>
            <a href="{{ route('instructor.courses.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-700">
                Voir tous
            </a>
        </div>

        <x-card padding="false">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50 text-xs font-semibold uppercase text-slate-500">
                            <th class="px-6 py-3">Titre</th>
                            <th class="px-6 py-3">Catégorie</th>
                            <th class="px-6 py-3">Statut</th>
                            <th class="px-6 py-3 text-right">Inscrits</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($recentCourses as $course)
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4 font-medium text-slate-900">{{ $course->title }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $course->category?->name ?? '—' }}</td>
                                <td class="px-6 py-4"><x-badge :variant="$course->status" /></td>
                                <td class="px-6 py-4 text-right text-slate-600">{{ $course->enrollments_count }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-slate-500">
                                    Vous n'avez pas encore de cours.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>
    </div>
</x-dashboard-layout>

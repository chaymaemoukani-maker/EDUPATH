<x-dashboard-layout>
    <div class="mb-8 flex items-center justify-between">
        <h1 class="text-3xl font-bold text-slate-900">Apprenants inscrits</h1>
    </div>

    @if (session('success'))
        <x-alert type="success" class="mb-6">{{ session('success') }}</x-alert>
    @endif
    @if (session('error'))
        <x-alert type="error" class="mb-6">{{ session('error') }}</x-alert>
    @endif

    <form method="GET" action="{{ route('instructor.learners.index') }}" class="mb-6 flex flex-wrap items-end gap-3">
        <div>
            <x-input-label for="course" value="Cours" />
            <x-select name="course" id="course" class="mt-1 w-64" onchange="this.form.submit()">
                <option value="">Tous mes cours</option>
                @foreach ($courses as $c)
                    <option value="{{ $c->id }}" @selected($course && $course->id === $c->id)>{{ $c->title }}</option>
                @endforeach
            </x-select>
        </div>
        <x-button type="submit" size="sm" variant="secondary">Filtrer</x-button>
        @if ($course)
            <a href="{{ route('instructor.learners.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-700">Réinitialiser</a>
        @endif
    </form>

    <x-card :padding="false">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50 text-xs font-semibold uppercase text-slate-500">
                        <th class="px-6 py-3">Apprenant</th>
                        <th class="px-6 py-3">Cours</th>
                        <th class="px-6 py-3">Date d'inscription</th>
                        <th class="px-6 py-3">Progression</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($items as $item)
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-4">
                                <div class="font-medium text-slate-900">{{ $item->learner->name }}</div>
                                <div class="text-xs text-slate-500">{{ $item->learner->email }}</div>
                            </td>
                            <td class="px-6 py-4 text-slate-600">{{ $item->course->title }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $item->enrolled_at?->format('d/m/Y') ?? '—' }}</td>
                            <td class="px-6 py-4">
                                <div class="w-40">
                                    <x-progress-bar :percent="$item->percent" size="sm" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-slate-500">
                                Aucun apprenant inscrit pour le moment.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>

    <x-pagination :paginator="$items" />
</x-dashboard-layout>

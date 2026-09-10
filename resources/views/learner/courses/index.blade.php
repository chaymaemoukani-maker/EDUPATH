<x-dashboard-layout>
    <h1 class="text-3xl font-bold text-slate-900">Mes cours</h1>
    <p class="mt-1 text-sm text-slate-500">Retrouvez tous les cours auxquels vous êtes inscrit.</p>

    @if (session('success'))
        <x-alert type="success" class="mb-6">{{ session('success') }}</x-alert>
    @endif
    @if (session('error'))
        <x-alert type="error" class="mb-6">{{ session('error') }}</x-alert>
    @endif

    <div class="mt-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($items as $item)
            <x-course-card :course="$item->course" :show-progress="true" :progress="$item->percent" :link-to="route('learner.courses.show', $item->course)" />
        @empty
            <div class="col-span-full rounded-xl border border-dashed border-slate-300 bg-white p-8 text-center">
                <p class="text-sm text-slate-500">Vous n'êtes inscrit à aucun cours pour le moment.</p>
                <x-button href="{{ route('catalog') }}" variant="primary" size="sm" class="mt-4">Parcourir le catalogue</x-button>
            </div>
        @endforelse
    </div>
</x-dashboard-layout>

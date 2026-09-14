<x-dashboard-layout>
    <h1 class="text-3xl font-bold text-slate-900">Bonjour, {{ auth()->user()->name }}</h1>
    <p class="mt-1 text-sm text-slate-500">Voici un aperçu de votre activité d'apprentissage.</p>

    @if (session('success'))
        <x-alert type="success" class="mb-6">{{ session('success') }}</x-alert>
    @endif
    @if (session('error'))
        <x-alert type="error" class="mb-6">{{ session('error') }}</x-alert>
    @endif

    <div class="mt-8 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Cours inscrits</p>
            <p class="mt-1 text-3xl font-bold text-slate-900">{{ $courses->count() }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">En cours</p>
            <p class="mt-1 text-3xl font-bold text-slate-900">{{ $inProgress->count() }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Cours terminés</p>
            <p class="mt-1 text-3xl font-bold text-slate-900">{{ $completed->count() }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Certificats</p>
            <p class="mt-1 text-3xl font-bold text-slate-900">{{ $certificates->count() }}</p>
        </div>
    </div>

    <div class="mt-10 flex flex-wrap items-center justify-between gap-4 rounded-xl border border-indigo-100 bg-indigo-50 p-6">
        <div>
            <h2 class="text-xl font-semibold text-slate-900">Assistant IA</h2>
            <p class="mt-1 text-sm text-slate-600">
                Posez vos questions sur vos cours en cours, simplifiez les concepts et préparez vos quiz.
            </p>
        </div>
        <x-button href="{{ route('learner.ai-assistant.index') }}" variant="primary" size="md">
            Ouvrir l’assistant
        </x-button>
    </div>

    <div class="mt-10">
        <h2 class="text-2xl font-semibold text-slate-900">Cours en cours</h2>
        <div class="mt-4 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($inProgress as $item)
                <x-course-card :course="$item->course" :show-progress="true" :progress="$item->percent" :link-to="route('learner.courses.show', $item->course)" />
            @empty
                <div class="col-span-full rounded-xl border border-dashed border-slate-300 bg-white p-8 text-center">
                    <p class="text-sm text-slate-500">Aucun cours en cours.</p>
                    <x-button href="{{ route('catalog') }}" variant="primary" size="sm" class="mt-4">Parcourir le catalogue</x-button>
                </div>
            @endforelse
        </div>
    </div>

    <div class="mt-10">
        <h2 class="text-2xl font-semibold text-slate-900">Cours terminés</h2>
        <div class="mt-4 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($completed as $item)
                <x-course-card :course="$item->course" :show-progress="true" :progress="100" :link-to="route('learner.courses.show', $item->course)" />
            @empty
                <div class="col-span-full rounded-xl border border-dashed border-slate-300 bg-white p-8 text-center">
                    <p class="text-sm text-slate-500">Aucun cours terminé pour le moment.</p>
                </div>
            @endforelse
        </div>
    </div>

    <div class="mt-10">
        <h2 class="text-2xl font-semibold text-slate-900">Certificats</h2>
        <div class="mt-4">
            @if ($certificates->isNotEmpty())
                @php $latest = $certificates->first(); @endphp
                <x-certificate-card :certificate="$latest" :download-url="route('learner.certificates.download', $latest)" :verify-url="route('verify')" class="mb-4" />
                <x-button href="{{ route('learner.certificates.index') }}" variant="secondary" size="sm">Tous mes certificats</x-button>
            @else
                <div class="rounded-xl border border-dashed border-slate-300 bg-white p-8 text-center">
                    <p class="text-sm text-slate-500">Terminez un cours à 100 % pour obtenir votre certificat.</p>
                </div>
            @endif
        </div>
    </div>
</x-dashboard-layout>

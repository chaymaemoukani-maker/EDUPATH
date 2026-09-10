<x-dashboard-layout>
    <h1 class="text-3xl font-bold text-slate-900">Mes certificats</h1>
    <p class="mt-1 text-sm text-slate-500">Tous les certificats que vous avez obtenus en terminant vos cours.</p>

    @if (session('success'))
        <x-alert type="success" class="mb-6">{{ session('success') }}</x-alert>
    @endif
    @if (session('error'))
        <x-alert type="error" class="mb-6">{{ session('error') }}</x-alert>
    @endif

    <div class="mt-8 space-y-6">
        @forelse ($certificates as $certificate)
            <x-certificate-card :certificate="$certificate" :download-url="route('learner.certificates.download', $certificate)" :verify-url="route('verify')" class="mb-6" />
        @empty
            <div class="rounded-xl border border-dashed border-slate-300 bg-white p-8 text-center">
                <p class="text-sm text-slate-500">Terminez un cours à 100 % pour obtenir votre certificat.</p>
                <x-button href="{{ route('catalog') }}" variant="primary" size="sm" class="mt-4">Parcourir le catalogue</x-button>
            </div>
        @endforelse
    </div>

    <x-pagination :paginator="$certificates" />
</x-dashboard-layout>

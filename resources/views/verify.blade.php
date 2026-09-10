<x-app-layout
    :page-title="$pageTitle ?? null"
    :meta-description="$metaDescription ?? null"
    :canonical="$canonical ?? null"
>
    <h1 class="text-3xl font-bold text-slate-900">Vérifier un certificat</h1>
    <p class="mt-1 text-sm text-slate-500">Saisissez le code unique imprimé sur le certificat.</p>

    <form action="{{ route('verify') }}" method="GET" class="mt-6 flex flex-col gap-4 sm:flex-row sm:items-end">
        <div class="flex-1">
            <label for="code" class="mb-1 block text-sm font-medium text-slate-700">Code</label>
            <input
                type="text"
                id="code"
                name="code"
                value="{{ $code ?? '' }}"
                placeholder="Code du certificat"
                class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
            />
        </div>
        <div>
            <x-button type="submit" variant="primary">Vérifier</x-button>
        </div>
    </form>

    @if ($code && $found)
        <div class="mt-8">
            <x-alert type="success" title="Certificat valide">
                <ul class="mt-2 space-y-1 text-sm">
                    <li><span class="font-medium">Titulaire :</span> {{ $certificate->user->name }}</li>
                    <li><span class="font-medium">Cours :</span> {{ $certificate->course->title }}</li>
                    <li><span class="font-medium">Formateur :</span> {{ $certificate->course->instructor?->name ?? '—' }}</li>
                    <li><span class="font-medium">Émis le :</span> {{ $certificate->issued_at->format('d/m/Y') }}</li>
                    <li><span class="font-medium">Code :</span> {{ $certificate->unique_code }}</li>
                </ul>
            </x-alert>
        </div>
    @elseif ($code && ! $found)
        <div class="mt-8">
            <x-alert type="error" title="Certificat introuvable">
                Aucun certificat ne correspond à ce code. Vérifiez la saisie.
            </x-alert>
        </div>
    @endif
</x-app-layout>

@props([
    'certificate',
    'showDownload' => true,
    'downloadUrl' => null,
    'verifyUrl' => null,
])

@php
    use Illuminate\Support\Facades\Route;

    $course = $certificate->course;
    $learner = $certificate->user;
    $instructor = $course?->instructor;
    $verifyHref = $verifyUrl ?? (Route::has('verify') ? route('verify') : '#');
@endphp

<div class="overflow-hidden rounded-2xl border-2 border-emerald-200 bg-white shadow-sm">
    <div class="border-b border-slate-100 bg-gradient-to-br from-indigo-50 to-emerald-50 px-6 py-10 text-center sm:px-10">
        <p class="text-xs font-semibold uppercase tracking-widest text-emerald-600">EduPath</p>
        <h2 class="mt-2 text-2xl font-bold text-slate-900">Certificat de réussite</h2>

        <p class="mt-6 text-sm text-slate-500">Ce certificat atteste que</p>
        <p class="mt-1 text-xl font-semibold text-slate-900">{{ $learner->name }}</p>

        <p class="mt-4 text-sm text-slate-500">a complété avec succès le cours</p>
        <p class="mt-1 text-lg font-semibold text-indigo-600">{{ $course->title }}</p>

        <p class="mt-4 text-xs text-slate-500">
            Formateur : {{ $instructor?->name ?? '—' }}
            &middot;
            Émis le {{ $certificate->issued_at?->format('d/m/Y') ?? '—' }}
        </p>
    </div>

    <div class="flex flex-wrap items-center justify-between gap-4 px-6 py-4 sm:px-10">
        <p class="font-mono text-xs text-slate-400">Code : {{ $certificate->unique_code }}</p>

        <div class="flex flex-wrap gap-3">
            @if ($showDownload && $downloadUrl)
                <x-button :href="$downloadUrl" variant="success" size="sm">Télécharger le PDF</x-button>
            @endif
            <x-button :href="$verifyHref" variant="secondary" size="sm">Vérifier</x-button>
        </div>
    </div>
</div>
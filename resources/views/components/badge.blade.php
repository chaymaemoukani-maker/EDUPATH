@props(['variant' => 'draft'])

@php
    $badges = [
        'published' => ['label' => 'Publié', 'class' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20'],
        'draft' => ['label' => 'Brouillon', 'class' => 'bg-amber-50 text-amber-700 ring-amber-600/20'],
        'completed' => ['label' => 'Terminé', 'class' => 'bg-indigo-50 text-indigo-700 ring-indigo-600/20'],
        'in-progress' => ['label' => 'En cours', 'class' => 'bg-blue-50 text-blue-700 ring-blue-600/20'],
        'not-started' => ['label' => 'Non commencé', 'class' => 'bg-slate-100 text-slate-600 ring-slate-500/20'],
        'passed' => ['label' => 'Réussi', 'class' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20'],
        'failed' => ['label' => 'Échoué', 'class' => 'bg-red-50 text-red-700 ring-red-600/20'],
        'learner' => ['label' => 'Apprenant', 'class' => 'bg-blue-50 text-blue-700 ring-blue-600/20'],
        'instructor' => ['label' => 'Formateur', 'class' => 'bg-purple-50 text-purple-700 ring-purple-600/20'],
        'admin' => ['label' => 'Admin', 'class' => 'bg-slate-100 text-slate-700 ring-slate-500/20'],
    ];
    $entry = $badges[$variant] ?? $badges['draft'];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset '.$entry['class']]) }}>
    {{ trim((string) $slot) !== '' ? $slot : $entry['label'] }}
</span>
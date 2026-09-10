@props(['variant' => 'draft'])

@php
    $badges = [
        'published' => ['label' => 'Publié', 'class' => 'bg-emerald-600'],
        'draft' => ['label' => 'Brouillon', 'class' => 'bg-amber-600'],
        'completed' => ['label' => 'Terminé', 'class' => 'bg-indigo-600'],
        'in-progress' => ['label' => 'En cours', 'class' => 'bg-blue-600'],
        'not-started' => ['label' => 'Non commencé', 'class' => 'bg-slate-500'],
        'passed' => ['label' => 'Réussi', 'class' => 'bg-emerald-600'],
        'failed' => ['label' => 'Échoué', 'class' => 'bg-red-600'],
        'learner' => ['label' => 'Apprenant', 'class' => 'bg-blue-600'],
        'instructor' => ['label' => 'Formateur', 'class' => 'bg-purple-600'],
        'admin' => ['label' => 'Admin', 'class' => 'bg-slate-700'],
    ];
    $entry = $badges[$variant] ?? $badges['draft'];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium text-white '.$entry['class']]) }}>
    {{ trim((string) $slot) !== '' ? $slot : $entry['label'] }}
</span>
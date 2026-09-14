@props(['items' => null, 'responsive' => true])

@php
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\Route;

    $url = fn (string $name, string $fallback = '#'): string => Route::has($name) ? route($name) : $fallback;

    $roleItems = [];
    if (Auth::check()) {
        if (Auth::user()->hasRole('admin')) {
            $roleItems = [
                ['label' => 'Dashboard', 'url' => $url('admin.dashboard'), 'route' => 'admin.dashboard', 'icon' => 'dashboard'],
                ['label' => 'Utilisateurs', 'url' => $url('admin.users.index'), 'route' => 'admin.users.index', 'icon' => 'users'],
                ['label' => 'Catégories', 'url' => $url('admin.categories.index'), 'route' => 'admin.categories.index', 'icon' => 'category'],
                ['label' => 'Cours', 'url' => $url('admin.courses.index'), 'route' => 'admin.courses.index', 'icon' => 'course'],
            ];
        } elseif (Auth::user()->hasRole('instructor')) {
            $roleItems = [
                ['label' => 'Dashboard', 'url' => $url('instructor.dashboard'), 'route' => 'instructor.dashboard', 'icon' => 'dashboard'],
                ['label' => 'Mes cours', 'url' => $url('instructor.courses.index'), 'route' => 'instructor.courses.index', 'icon' => 'course'],
                ['label' => 'Apprenants', 'url' => $url('instructor.learners.index'), 'route' => 'instructor.learners.index', 'icon' => 'users'],
            ];
        } else {
            $roleItems = [
                ['label' => 'Dashboard', 'url' => $url('learner.dashboard'), 'route' => 'learner.dashboard', 'icon' => 'dashboard'],
                ['label' => 'Mes cours', 'url' => $url('learner.courses.index'), 'route' => 'learner.courses.index', 'icon' => 'course'],
                ['label' => 'Assistant IA', 'url' => $url('learner.ai-assistant.index'), 'route' => 'learner.ai-assistant.index', 'icon' => 'ai'],
                ['label' => 'Certificats', 'url' => $url('learner.certificates.index'), 'route' => 'learner.certificates.index', 'icon' => 'certificate'],
                ['label' => 'Profil', 'url' => $url('profile.edit'), 'route' => 'profile.edit', 'icon' => 'profile'],
            ];
        }
    }

    $items = $items ?? $roleItems;
    $at = fn (array $item): bool => isset($item['route']) && Route::has($item['route'])
        ? request()->routeIs($item['route'])
        : request()->url() === ($item['url'] ?? '');

    $icons = [
        'dashboard' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/>',
        'users' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.125-.952 4.125 4.125 0 00-7.5-3.974M15 19.128a9.375 9.375 0 01-6 0M15 19.128a4.125 4.125 0 01-7.5-3.974M9 15a3.375 3.375 0 103.375 0A3.375 3.375 0 009 15z"/>',
        'category' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4.098 19.902a3.75 3.75 0 005.304 0l6.401-6.402a3.75 3.75 0 001.097-2.653V6.75h-4.097a3.75 3.75 0 00-2.653 1.097L4.098 14.598a3.75 3.75 0 000 5.304zM16.5 4.5l4.313-1.313-1.313 4.313L18.75 8.25 21 10.5 15.75 15.75 13.5 13.5l3-3z"/>',
        'course' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/>',
        'certificate' => '<path stroke-linecap="round" stroke-linejoin="round" d="M16.5 3.75V16.5L12 14.25 7.5 16.5V3.75m9 0H18A2.25 2.25 0 0120.25 6v12A2.25 2.25 0 0118 20.25H6A2.25 2.25 0 013.75 18V6A2.25 2.25 0 016 3.75h1.5m9 0h-9"/>',
        'ai' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456z"/>',
        'profile' => '<path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z"/>',
    ];
@endphp

<aside {{ $attributes->merge(['class' => ($responsive ? 'hidden md:flex ' : 'flex ').'w-60 shrink-0 flex-col border-r border-slate-200 bg-white']) }}>
    <nav class="flex-1 space-y-0.5 p-3">
        @foreach ($items as $item)
            <a
                href="{{ $item['url'] }}"
                class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors {{ $at($item) ? 'bg-indigo-50 text-indigo-600' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}"
            >
                <svg class="h-4 w-4 shrink-0 {{ $at($item) ? 'text-indigo-600' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    {!! $icons[$item['icon'] ?? 'course'] ?? '' !!}
                </svg>
                {{ $item['label'] }}
            </a>
        @endforeach
    </nav>
</aside>
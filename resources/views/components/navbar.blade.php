@props([])

@php
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\Route;

    $url = function (string $name, string $fallback = '#'): string {
        return Route::has($name) ? route($name) : $fallback;
    };

    $links = [];
    $isActive = function (array $link) use ($url): bool {
        if (($link['route'] ?? null) && Route::has($link['route'])) {
            return request()->routeIs($link['route']);
        }

        return ($link['url'] ?? '') === url('/') && request()->path() === '/';
    };

    if (Auth::guest()) {
        $links = [
            ['label' => 'Accueil', 'url' => url('/')],
            ['label' => 'Catalogue', 'url' => $url('catalog')],
        ];
    } elseif (Auth::user()->hasRole('admin')) {
        $links = [
            ['label' => 'Dashboard', 'url' => $url('admin.dashboard'), 'route' => 'admin.dashboard'],
            ['label' => 'Utilisateurs', 'url' => $url('admin.users.index'), 'route' => 'admin.users.index'],
            ['label' => 'Catégories', 'url' => $url('admin.categories.index'), 'route' => 'admin.categories.index'],
            ['label' => 'Cours', 'url' => $url('admin.courses.index'), 'route' => 'admin.courses.index'],
        ];
    } elseif (Auth::user()->hasRole('instructor')) {
        $links = [
            ['label' => 'Dashboard', 'url' => $url('instructor.dashboard'), 'route' => 'instructor.dashboard'],
            ['label' => 'Mes cours', 'url' => $url('instructor.courses.index'), 'route' => 'instructor.courses.index'],
            ['label' => 'Apprenants', 'url' => $url('instructor.learners.index'), 'route' => 'instructor.learners.index'],
        ];
    } else {
        $links = [
            ['label' => 'Dashboard', 'url' => $url('learner.dashboard'), 'route' => 'learner.dashboard'],
            ['label' => 'Mes cours', 'url' => $url('learner.courses.index'), 'route' => 'learner.courses.index'],
            ['label' => 'Assistant IA', 'url' => $url('learner.ai-assistant.index'), 'route' => 'learner.ai-assistant.index'],
            ['label' => 'Catalogue', 'url' => $url('catalog'), 'route' => 'catalog'],
        ];
    }

    $initials = function (?string $name): string {
        $name = trim((string) $name);
        $parts = $name !== '' ? preg_split('/\s+/', $name) : [];

        $letters = '';
        foreach (array_slice(is_array($parts) ? $parts : [], 0, 2) as $part) {
            $letters .= mb_strtoupper(mb_substr($part, 0, 1));
        }

        return $letters !== '' ? $letters : '?';
    };
@endphp

<nav x-data="{ open: false, logoutModal: false }" {{ $attributes->merge(['class' => 'sticky top-0 z-50 border-b border-slate-200 bg-white']) }}>
    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-1">
            <a href="{{ url('/') }}" class="flex shrink-0 items-center gap-2 text-xl font-bold text-indigo-600">
                <x-application-logo class="h-8 w-8" />
                EduPath
            </a>

            <div class="hidden items-center gap-1 md:flex">
                @foreach ($links as $link)
                    <a
                        href="{{ $link['url'] }}"
                        class="rounded-lg px-3 py-2 text-sm font-medium transition-colors {{ $isActive($link) ? 'bg-indigo-50 text-indigo-600' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}"
                    >
                        {{ $link['label'] }}
                    </a>
                @endforeach
            </div>
        </div>

        <div class="flex items-center gap-2">
            @guest
                <div class="hidden items-center gap-2 md:flex">
                    @if (Route::has('login'))
                        <a href="{{ route('login') }}" class="rounded-lg px-3 py-2 text-sm font-medium text-slate-600 transition-colors hover:bg-slate-50 hover:text-slate-900">Connexion</a>
                    @endif

                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-indigo-700">Inscription</a>
                    @endif
                </div>
            @endguest

            @auth
                <x-dropdown align="right" width="52">
                    <x-slot name="trigger">
                        <button type="button" class="inline-flex items-center gap-2 rounded-xl py-1 pe-3 ps-1 text-sm font-medium text-slate-700 transition-colors hover:bg-slate-100" aria-haspopup="true">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-600 text-sm font-semibold text-white">
                                {{ $initials(Auth::user()->name) }}
                            </span>
                            <span class="hidden max-w-[120px] truncate sm:inline">{{ Auth::user()->name }}</span>
                            <svg class="hidden h-3.5 w-3.5 text-slate-400 sm:block" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="border-b border-slate-100 px-4 py-3">
                            <div class="flex items-center gap-3">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-indigo-600 text-sm font-semibold text-white">
                                    {{ $initials(Auth::user()->name) }}
                                </span>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-slate-900">{{ Auth::user()->name }}</p>
                                    <p class="truncate text-xs text-slate-500">{{ Auth::user()->email }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="py-1">
                            <x-dropdown-link :href="$url('profile.edit')">
                                {{ __('Profil') }}
                            </x-dropdown-link>
                        </div>

                        <div class="border-t border-slate-100 py-1">
                            <button
                                type="button"
                                @click="logoutModal = true"
                                class="flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm text-red-600 transition-colors hover:bg-red-50"
                            >
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                                </svg>
                                {{ __('Déconnexion') }}
                            </button>
                        </div>
                    </x-slot>
                </x-dropdown>
            @endauth

            <button
                type="button"
                @click="open = ! open"
                class="rounded-lg p-2 text-slate-500 transition-colors hover:bg-slate-100 md:hidden"
                aria-label="Ouvrir le menu"
            >
                <svg class="h-5 w-5" :class="{ 'hidden': open, 'block': ! open }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
                <svg class="hidden h-5 w-5" :class="{ 'block': open, 'hidden': ! open }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    {{-- Mobile menu --}}
    <div :class="{ 'block': open, 'hidden': ! open }" class="hidden border-t border-slate-100 py-3 md:hidden">
        <div class="space-y-0.5 px-3">
            @foreach ($links as $link)
                <a
                    href="{{ $link['url'] }}"
                    class="block rounded-lg px-3 py-2 text-sm font-medium transition-colors {{ $isActive($link) ? 'bg-indigo-50 text-indigo-600' : 'text-slate-600 hover:bg-slate-50' }}"
                >
                    {{ $link['label'] }}
                </a>
            @endforeach

            @guest
                <div class="flex gap-2 border-t border-slate-100 pt-3">
                    @if (Route::has('login'))
                        <a href="{{ route('login') }}" class="flex-1 rounded-lg border border-slate-300 py-2 text-center text-sm font-medium text-slate-700 transition-colors hover:bg-slate-50">Connexion</a>
                    @endif

                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="flex-1 rounded-lg bg-indigo-600 py-2 text-center text-sm font-medium text-white transition-colors hover:bg-indigo-700">Inscription</a>
                    @endif
                </div>
            @endguest

            @auth
                <div class="space-y-0.5 border-t border-slate-100 pt-2">
                    <a href="{{ $url('profile.edit') }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-slate-600 transition-colors hover:bg-slate-50">
                        Profil
                    </a>
                    <button
                        type="button"
                        @click="logoutModal = true"
                        class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-left text-sm font-medium text-red-600 transition-colors hover:bg-red-50"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                        </svg>
                        Déconnexion
                    </button>
                </div>
            @endauth
        </div>
    </div>

    {{-- Logout confirmation modal --}}
    <div x-show="logoutModal" style="display: none;" class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50 p-4">
        <div class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-xl">
            <div class="mb-5 text-center">
                <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-red-50">
                    <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                    </svg>
                </div>
                <h3 class="mb-1 font-bold text-slate-900">Se déconnecter ?</h3>
                <p class="text-sm text-slate-500">Votre progression est sauvegardée. Vous pouvez vous reconnecter à tout moment.</p>
            </div>
            <div class="flex gap-3">
                <button
                    type="button"
                    @click="logoutModal = false"
                    class="flex-1 rounded-xl border border-slate-300 py-2.5 text-sm font-medium text-slate-700 transition-colors hover:bg-slate-50"
                >Annuler</button>

                <form method="POST" action="{{ $url('logout') }}" class="flex-1">
                    @csrf
                    <button type="submit" class="w-full rounded-xl bg-red-600 py-2.5 text-sm font-medium text-white transition-colors hover:bg-red-700">Déconnexion</button>
                </form>
            </div>
        </div>
    </div>
</nav>
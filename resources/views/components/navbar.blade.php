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
            ['label' => 'Dashboard', 'url' => $url('dashboard'), 'route' => 'dashboard'],
            ['label' => 'Utilisateurs', 'url' => $url('admin.users.index'), 'route' => 'admin.users.index'],
            ['label' => 'Catégories', 'url' => $url('admin.categories.index'), 'route' => 'admin.categories.index'],
            ['label' => 'Cours', 'url' => $url('admin.courses.index'), 'route' => 'admin.courses.index'],
        ];
    } elseif (Auth::user()->hasRole('instructor')) {
        $links = [
            ['label' => 'Dashboard', 'url' => $url('dashboard'), 'route' => 'dashboard'],
            ['label' => 'Mes cours', 'url' => $url('instructor.courses.index'), 'route' => 'instructor.courses.index'],
        ];
    } else {
        $links = [
            ['label' => 'Dashboard', 'url' => $url('dashboard'), 'route' => 'dashboard'],
            ['label' => 'Mes cours', 'url' => $url('learner.courses.index'), 'route' => 'learner.courses.index'],
            ['label' => 'Catalogue', 'url' => $url('catalog'), 'route' => 'catalog'],
        ];
    }
@endphp

<nav x-data="{ open: false }" {{ $attributes->merge(['class' => 'sticky top-0 z-30 border-b border-slate-200 bg-white']) }}>
    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-3">
            <a href="{{ url('/') }}" class="flex items-center">
                <x-application-logo class="block h-9 w-auto fill-current text-indigo-600" />
                <span class="ml-2 text-lg font-semibold text-slate-900">EduPath</span>
            </a>

            @auth
                <button
                    type="button"
                    @click="open = ! open"
                    class="-mr-1 inline-flex items-center justify-center rounded-md p-2 text-slate-500 hover:bg-slate-100 focus:outline-none md:hidden"
                    aria-label="Ouvrir le menu"
                >
                    <svg class="h-6 w-6" :class="{ 'hidden': open, 'inline-flex': ! open }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg class="hidden h-6 w-6" :class="{ 'inline-flex': open, 'hidden': ! open }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            @endauth

            <div class="hidden items-center gap-8 md:flex">
                @foreach ($links as $link)
                    <a
                        href="{{ $link['url'] }}"
                        class="text-sm font-medium transition {{ $isActive($link) ? 'text-indigo-600' : 'text-slate-600 hover:text-slate-900' }}"
                    >
                        {{ $link['label'] }}
                    </a>
                @endforeach
            </div>
        </div>

        <div class="flex items-center gap-3">
            @guest
                @if (Route::has('login'))
                    <a href="{{ route('login') }}" class="hidden text-sm font-medium text-slate-600 hover:text-slate-900 sm:inline-flex">Connexion</a>
                    <x-button href="{{ route('login') }}" variant="ghost" size="sm" class="sm:hidden">Connexion</x-button>
                @endif

                @if (Route::has('register'))
                    <x-button href="{{ route('register') }}" size="sm" class="hidden sm:inline-flex">Inscription</x-button>
                    <x-button href="{{ route('register') }}" size="sm" class="sm:hidden">Inscription</x-button>
                @endif
            @endguest

            @auth
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center gap-2 rounded-full py-1 pl-1 pr-2 text-sm font-medium text-slate-700 transition hover:text-slate-900">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-600 text-xs font-semibold text-white">
                                {{ mb_strtoupper(mb_substr(Auth::user()->name, 0, 1)) }}
                            </span>
                            <span class="hidden sm:inline">{{ Auth::user()->name }}</span>
                            <svg class="h-4 w-4 fill-current text-slate-400" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="$url('profile.edit')">
                            {{ __('Profil') }}
                        </x-dropdown-link>

                        <form method="POST" action="{{ $url('logout') }}" class="md:hidden">
                            @csrf
                            <x-dropdown-link :href="$url('logout')"
                                onclick="event.preventDefault(); this.closest('form').submit();"
                            >
                                {{ __('Déconnexion') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            @endauth
        </div>
    </div>

    {{-- Mobile menu --}}
    @auth
        <div :class="{ 'block': open, 'hidden': ! open }" class="hidden border-t border-slate-100 md:hidden">
            <div class="space-y-1 px-4 py-3">
                @foreach ($links as $link)
                    <a
                        href="{{ $link['url'] }}"
                        class="block rounded-lg px-3 py-2 text-sm font-medium {{ $isActive($link) ? 'bg-indigo-50 text-indigo-700' : 'text-slate-700 hover:bg-slate-50' }}"
                    >
                        {{ $link['label'] }}
                    </a>
                @endforeach

                <div class="mt-2 border-t border-slate-100 pt-2">
                    <a href="{{ $url('profile.edit') }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                        Profil
                    </a>
                    <form method="POST" action="{{ $url('logout') }}">
                        @csrf
                        <button type="submit" class="block w-full rounded-lg px-3 py-2 text-left text-sm font-medium text-red-600 hover:bg-red-50">
                            Déconnexion
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endauth
</nav>
<x-app-layout
    :page-title="$pageTitle"
    :meta-description="$metaDescription"
    :canonical="$canonical"
>
    {{-- Hero --}}
    <section class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-600 to-indigo-800 px-6 py-16 text-center sm:px-12 md:py-20">
        <div class="relative mx-auto max-w-3xl">
            <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-1.5 text-sm font-medium text-indigo-100 ring-1 ring-white/20">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5" />
                </svg>
                Plateforme e-learning
            </span>

            <h1 class="mt-6 text-4xl font-bold tracking-tight text-white sm:text-5xl">
                Apprenez à votre rythme, étape par étape.
            </h1>

            <p class="mt-6 text-lg leading-relaxed text-indigo-100">
                EduPath réunit des cours en ligne créés par des formateurs, organisés en sections et modules.
                Progressez à votre rythme, validez vos connaissances avec des quiz et obtenez un certificat PDF vérifiable.
            </p>

            <div class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row">
                @auth
                    <x-button href="{{ route('dashboard') }}" size="lg" class="w-full sm:w-auto">
                        Accéder à mon espace
                    </x-button>
                @else
                    <x-button href="{{ route('catalog') }}" size="lg" class="w-full sm:w-auto">
                        Explorer le catalogue
                    </x-button>
                    <x-button href="{{ route('register') }}" variant="secondary" size="lg" class="w-full sm:w-auto">
                        Créer un compte gratuit
                    </x-button>
                @endauth
            </div>

            <dl class="mx-auto mt-12 grid max-w-2xl grid-cols-2 gap-6 sm:grid-cols-4">
                <div>
                    <dt class="text-sm text-indigo-200">Cours publiés</dt>
                    <dd class="text-2xl font-bold text-white">{{ $publishedCoursesCount }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-indigo-200">Catégories</dt>
                    <dd class="text-2xl font-bold text-white">{{ $categories->count() }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-indigo-200">Apprenants inscrits</dt>
                    <dd class="text-2xl font-bold text-white">{{ $learnersCount }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-indigo-200">Certificats délivrés</dt>
                    <dd class="text-2xl font-bold text-white">{{ $certificatesCount }}</dd>
                </div>
            </dl>
        </div>
    </section>

    {{-- Cours mis en avant --}}
    @if ($featuredCourses->isNotEmpty())
        <section class="mt-16">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900">Cours à découvrir</h2>
                    <p class="mt-1 text-sm text-slate-500">Parcourez les formations publiées et inscrivez-vous en un clic.</p>
                </div>
                <x-button href="{{ route('catalog') }}" variant="secondary" size="sm">
                    Voir tout le catalogue
                </x-button>
            </div>

            <div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($featuredCourses as $course)
                    <x-course-card :course="$course" />
                @endforeach
            </div>
        </section>
    @endif

    {{-- Comment ça marche --}}
    <section class="mt-16">
        <div class="text-center">
            <h2 class="text-2xl font-bold text-slate-900">Comment ça marche ?</h2>
            <p class="mx-auto mt-2 max-w-2xl text-sm text-slate-500">
                Un parcours simple, du premier clic au certificat.
            </p>
        </div>

        <ol class="mt-8 grid grid-cols-1 gap-6 md:grid-cols-3">
            <li class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-600 text-lg font-bold text-white">1</span>
                <h3 class="mt-4 text-lg font-semibold text-slate-900">Créez votre compte</h3>
                <p class="mt-2 text-sm leading-relaxed text-slate-500">
                    L'inscription est gratuite et ouverte à tous. Vous êtes automatiquement apprenant, sans sélection de rôle.
                </p>
            </li>
            <li class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-600 text-lg font-bold text-white">2</span>
                <h3 class="mt-4 text-lg font-semibold text-slate-900">Choisissez votre cours</h3>
                <p class="mt-2 text-sm leading-relaxed text-slate-500">
                    Parcourez le catalogue, filtrez par catégorie et inscrivez-vous au cours qui vous correspond.
                </p>
            </li>
            <li class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-600 text-lg font-bold text-white">3</span>
                <h3 class="mt-4 text-lg font-semibold text-slate-900">Progressez et obtenez votre certificat</h3>
                <p class="mt-2 text-sm leading-relaxed text-slate-500">
                    Terminez les modules, passez les quiz et récupérez votre certificat PDF à 100 % de progression.
                </p>
            </li>
        </ol>
    </section>

    {{-- Bénéfices --}}
    <section class="mt-16">
        <div class="text-center">
            <h2 class="text-2xl font-bold text-slate-900">Pourquoi choisir EduPath ?</h2>
            <p class="mx-auto mt-2 max-w-2xl text-sm text-slate-500">
                Des fondamentaux pensés pour un apprentissage efficace et vérifiable.
            </p>
        </div>

        <div class="mt-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </div>
                <h3 class="mt-4 font-semibold text-slate-900">Cours structurés</h3>
                <p class="mt-2 text-sm leading-relaxed text-slate-500">
                    Des sections et des modules clairs — texte, vidéo ou PDF.
                </p>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="mt-4 font-semibold text-slate-900">Progression suivie</h3>
                <p class="mt-2 text-sm leading-relaxed text-slate-500">
                    Votre avancement est calculé automatiquement module par module.
                </p>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                </div>
                <h3 class="mt-4 font-semibold text-slate-900">Quiz de validation</h3>
                <p class="mt-2 text-sm leading-relaxed text-slate-500">
                    Des quiz à choix multiples pour valider chaque module.
                </p>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 013 3h-15a3 3 0 013-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 01-.982-3.172M9.497 14.25a7.454 7.454 0 00.981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 007.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 002.748 1.35m8.272-6.842V4.5c0 2.108-.966 3.99-2.48 5.228m2.48-5.492a46.32 46.32 0 012.916.52 6.003 6.003 0 01-5.395 4.972m0 0a6.726 6.726 0 01-2.749 1.35m0 0a6.772 6.772 0 01-3.044 0" />
                    </svg>
                </div>
                <h3 class="mt-4 font-semibold text-slate-900">Certificat vérifiable</h3>
                <p class="mt-2 text-sm leading-relaxed text-slate-500">
                    Un certificat PDF unique, vérifiable publiquement par identifiant.
                </p>
            </div>
        </div>
    </section>

    {{-- Catégories --}}
    @if ($categories->isNotEmpty())
        <section class="mt-16">
            <div class="flow-root">
                <h2 class="text-2xl font-bold text-slate-900">Explorer par catégorie</h2>
                <div class="mt-6 flex flex-wrap gap-3">
                    @foreach ($categories as $category)
                        <x-button
                            href="{{ route('catalog', ['category' => $category->id]) }}"
                            variant="secondary"
                            size="sm"
                            class="gap-1.5"
                        >
                            {{ $category->name }}
                            <span class="text-xs text-slate-400">({{ $category->courses_count }})</span>
                        </x-button>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- CTA final --}}
    <section class="mt-16 rounded-2xl border border-slate-200 bg-white px-6 py-14 text-center shadow-sm">
        <h2 class="text-2xl font-bold text-slate-900">Prêt à commencer votre apprentissage ?</h2>
        <p class="mx-auto mt-3 max-w-xl text-sm text-slate-500">
            Rejoignez EduPath gratuitement et avancez à votre rythme jusqu'au certificat.
        </p>
        <div class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row">
            @auth
                <x-button href="{{ route('catalog') }}" size="lg" class="w-full sm:w-auto">
                    Explorer le catalogue
                </x-button>
            @else
                <x-button href="{{ route('register') }}" size="lg" class="w-full sm:w-auto">
                    Créer un compte gratuit
                </x-button>
                <x-button href="{{ route('catalog') }}" variant="secondary" size="lg" class="w-full sm:w-auto">
                    Parcourir le catalogue
                </x-button>
            @endauth
        </div>
    </section>
</x-app-layout>
<x-app-layout
    :page-title="$pageTitle"
    :meta-description="$metaDescription"
    :canonical="$canonical"
>
    {{-- Hero — fond blanc, 2 colonnes, image droite --}}
    <section class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex max-w-6xl flex-col items-center gap-12 px-4 py-20 sm:px-6 sm:py-28 md:flex-row lg:px-0">
            <div class="flex-1 text-center md:text-left">
                <span class="mb-4 inline-block rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">
                    Plateforme e-learning gratuite
                </span>

                <h1 class="mb-4 text-4xl font-bold leading-tight text-slate-900">
                    Apprenez à votre rythme,<br class="hidden md:block">
                    <span class="text-indigo-600">en ligne.</span>
                </h1>

                <p class="mb-8 max-w-lg text-lg text-slate-500">
                    Accédez à des formations de qualité en développement web, bases de données et design.
                    Progressez avec des cours structurés et obtenez vos certificats.
                </p>

                <div class="flex flex-wrap justify-center gap-3 md:justify-start">
                    @auth
                        <x-button href="{{ route('dashboard') }}" size="lg">
                            Accéder à mon espace
                        </x-button>
                    @else
                        <x-button href="{{ route('catalog') }}" size="lg">
                            Voir les cours
                        </x-button>
                        <x-button href="{{ route('register') }}" variant="secondary" size="lg">
                            Créer un compte
                        </x-button>
                    @endauth
                </div>
            </div>

            <div class="hidden flex-1 md:block">
                <img
                    src="https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=600&h=400&fit=crop&auto=format"
                    alt="Apprentissage en ligne"
                    class="h-80 w-full rounded-2xl object-cover bg-slate-100 shadow-lg"
                />
            </div>
        </div>
    </section>

    {{-- Stats — bande indigo --}}
    <section class="bg-indigo-600 py-10">
        <div class="mx-auto grid max-w-6xl grid-cols-2 gap-6 px-4 text-center sm:px-6 md:grid-cols-4 lg:px-0">
            <div>
                <p class="text-3xl font-bold text-white">{{ $publishedCoursesCount }}</p>
                <p class="mt-1 text-sm text-indigo-200">Cours disponibles</p>
            </div>
            <div>
                <p class="text-3xl font-bold text-white">{{ $learnersCount }}</p>
                <p class="mt-1 text-sm text-indigo-200">Apprenants inscrits</p>
            </div>
            <div>
                <p class="text-3xl font-bold text-white">{{ $instructorsCount }}</p>
                <p class="mt-1 text-sm text-indigo-200">Formateurs experts</p>
            </div>
            <div>
                <p class="text-3xl font-bold text-white">{{ $categories->count() }}</p>
                <p class="mt-1 text-sm text-indigo-200">Catégories</p>
            </div>
        </div>
    </section>

    {{-- Catégories cliquables --}}
    @if ($categories->isNotEmpty())
        <section class="mx-auto max-w-6xl px-4 py-12 sm:px-6 lg:px-0">
            <h2 class="mb-5 text-2xl font-semibold text-slate-900">Parcourir par catégorie</h2>
            <div class="flex flex-wrap gap-3">
                @foreach ($categories as $category)
                    <a
                        href="{{ route('catalog', ['category' => $category->id]) }}"
                        class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm transition-colors hover:border-indigo-300 hover:bg-indigo-50 hover:text-indigo-700"
                    >
                        {{ $category->name }}
                        <span class="text-xs text-slate-400">{{ $category->courses_count }}</span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Cours mis en avant --}}
    @if ($featuredCourses->isNotEmpty())
        <section class="mx-auto max-w-6xl px-4 pb-16 sm:px-6 lg:px-0">
            <div class="mb-6 flex items-center justify-between">
                <h2 class="text-2xl font-semibold text-slate-900">Cours mis en avant</h2>
                <a href="{{ route('catalog') }}" class="text-sm font-medium text-indigo-600 hover:underline">Voir tous les cours →</a>
            </div>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($featuredCourses as $course)
                    <x-course-card :course="$course" />
                @endforeach
            </div>
        </section>
    @endif

    {{-- CTA final — bande foncée --}}
    <section class="bg-slate-900 py-16">
        <div class="mx-auto max-w-2xl px-4 text-center">
            <h2 class="mb-4 text-3xl font-bold text-white">Prêt à commencer ?</h2>
            <p class="mb-8 text-slate-400">Inscrivez-vous gratuitement et commencez à apprendre dès aujourd'hui.</p>
            @auth
                <x-button href="{{ route('catalog') }}" size="lg">
                    Explorer le catalogue
                </x-button>
            @else
                <x-button href="{{ route('register') }}" size="lg">
                    S'inscrire gratuitement
                </x-button>
            @endauth
        </div>
    </section>
</x-app-layout>
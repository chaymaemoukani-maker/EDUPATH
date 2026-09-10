<x-app-layout
    :page-title="$pageTitle ?? null"
    :meta-description="$metaDescription ?? null"
    :canonical="$canonical ?? null"
>
    @if (session('success'))
        <x-alert type="success" class="mb-6">{{ session('success') }}</x-alert>
    @endif

    <h1 class="text-3xl font-bold text-slate-900">Tous les cours</h1>
    <p class="mt-1 text-sm text-slate-500">Explorez notre catalogue et trouvez le cours qui vous correspond.</p>

    <form action="{{ route('catalog') }}" method="GET" class="mt-6 flex flex-col gap-4 sm:flex-row sm:items-end">
        <div class="flex-1">
            <label for="search" class="mb-1 block text-sm font-medium text-slate-700">Recherche</label>
            <input
                type="text"
                id="search"
                name="search"
                value="{{ request('search') }}"
                placeholder="Rechercher un cours…"
                class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
            />
        </div>
        <div class="sm:w-64">
            <label for="category" class="mb-1 block text-sm font-medium text-slate-700">Catégorie</label>
            <select
                id="category"
                name="category"
                class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
            >
                <option value="">Toutes les catégories</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" {{ (string) request('category') === (string) $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <x-button type="submit" variant="primary">Filtrer</x-button>
        </div>
    </form>

    <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($courses as $course)
            <x-course-card :course="$course" />
        @empty
            <div class="col-span-full flex flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 bg-white py-16 text-center">
                <svg class="h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
                <p class="mt-4 text-sm font-medium text-slate-700">Aucun cours ne correspond à votre recherche.</p>
                <x-button href="{{ route('catalog') }}" variant="secondary" size="sm" class="mt-4">Réinitialiser les filtres</x-button>
            </div>
        @endforelse
    </div>

    <x-pagination :paginator="$courses" />
</x-app-layout>

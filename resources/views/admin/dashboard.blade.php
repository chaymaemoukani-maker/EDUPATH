<x-dashboard-layout>
    <div class="mb-8 flex items-center justify-between">
        <h1 class="text-3xl font-bold text-slate-900">Dashboard</h1>
    </div>

    @if (session('success'))
        <x-alert type="success" class="mb-6">{{ session('success') }}</x-alert>
    @endif
    @if (session('error'))
        <x-alert type="error" class="mb-6">{{ session('error') }}</x-alert>
    @endif

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <x-card>
            <div class="text-3xl font-bold text-slate-900">{{ $totalUsers }}</div>
            <div class="mt-1 text-sm text-slate-500">Utilisateurs</div>
        </x-card>
        <x-card>
            <div class="text-3xl font-bold text-slate-900">{{ $publishedCourses }}</div>
            <div class="mt-1 text-sm text-slate-500">Cours publiés</div>
        </x-card>
        <x-card>
            <div class="text-3xl font-bold text-slate-900">{{ $draftCourses }}</div>
            <div class="mt-1 text-sm text-slate-500">Brouillons</div>
        </x-card>
        <x-card>
            <div class="text-3xl font-bold text-slate-900">{{ $totalCategories }}</div>
            <div class="mt-1 text-sm text-slate-500">Catégories</div>
        </x-card>
    </div>

    <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-3">
        <x-card>
            <h2 class="mb-4 text-base font-semibold text-slate-900">Utilisateurs récents</h2>
            @forelse ($recentUsers as $user)
                <div class="{{ !$loop->last ? 'border-b border-slate-100 pb-3 mb-3' : '' }}">
                    <div class="font-medium text-slate-900">{{ $user->name }}</div>
                    <div class="text-sm text-slate-500">{{ $user->email }}</div>
                </div>
            @empty
                <p class="text-sm text-slate-500">Aucun utilisateur pour le moment.</p>
            @endforelse
            <div class="mt-4">
                <a href="{{ route('admin.users.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-700">Voir tous</a>
            </div>
        </x-card>

        <x-card>
            <h2 class="mb-4 text-base font-semibold text-slate-900">Cours récents</h2>
            @forelse ($recentCourses as $course)
                <div class="{{ !$loop->last ? 'border-b border-slate-100 pb-3 mb-3' : '' }}">
                    <div class="font-medium text-slate-900">{{ $course->title }}</div>
                    <div class="mt-1"><x-badge :variant="$course->status" /></div>
                </div>
            @empty
                <p class="text-sm text-slate-500">Aucun cours pour le moment.</p>
            @endforelse
            <div class="mt-4">
                <a href="{{ route('admin.courses.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-700">Voir tous</a>
            </div>
        </x-card>

        <x-card>
            <h2 class="mb-4 text-base font-semibold text-slate-900">Catégories</h2>
            @forelse ($categories as $category)
                <div class="{{ !$loop->last ? 'border-b border-slate-100 pb-3 mb-3' : '' }}">
                    <div class="font-medium text-slate-900">{{ $category->name }}</div>
                    <div class="text-sm text-slate-500">{{ $category->courses_count }} cours</div>
                </div>
            @empty
                <p class="text-sm text-slate-500">Aucune catégorie pour le moment.</p>
            @endforelse
            <div class="mt-4">
                <a href="{{ route('admin.categories.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-700">Voir toutes</a>
            </div>
        </x-card>
    </div>
</x-dashboard-layout>

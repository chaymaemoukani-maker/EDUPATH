<x-dashboard-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-slate-900">Mon profil</h2>
    </x-slot>

    @php
        $initials = function (string $name): string {
            $name = trim($name);

            $letters = '';
            foreach (array_slice(preg_split('/\s+/', $name) ?: [], 0, 2) as $part) {
                $letters .= mb_strtoupper(mb_substr($part, 0, 1));
            }

            return $letters !== '' ? $letters : '?';
        };

        $roleVariant = $user->hasRole('admin')
            ? 'admin'
            : ($user->hasRole('instructor') ? 'instructor' : 'learner');
    @endphp

    <div class="mx-auto max-w-3xl space-y-6">
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="h-24 bg-gradient-to-r from-indigo-600 to-indigo-500"></div>
            <div class="px-6 pb-6">
                <div class="-mt-10 mb-4 flex h-16 w-16 items-center justify-center rounded-2xl border-4 border-white bg-indigo-600 text-xl font-bold text-white shadow-sm">
                    {{ $initials($user->name) }}
                </div>
                <h3 class="text-xl font-bold text-slate-900">{{ $user->name }}</h3>
                <p class="mt-0.5 text-sm text-slate-500">{{ $user->email }}</p>
                <div class="mt-3">
                    <x-badge :variant="$roleVariant" />
                </div>
            </div>
        </div>

        <div class="grid gap-6 md:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                @include('profile.partials.update-profile-information-form')
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        <div class="rounded-2xl border border-red-200 bg-white p-6 shadow-sm">
            @include('profile.partials.delete-user-form')
        </div>
    </div>
</x-dashboard-layout>
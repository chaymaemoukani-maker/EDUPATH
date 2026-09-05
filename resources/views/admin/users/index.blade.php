<x-dashboard-layout>
    <div class="mb-8 flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-slate-900">Gestion des utilisateurs</h1>
    </div>

    @if (session('success'))
        <x-alert type="success" class="mb-6">{{ session('success') }}</x-alert>
    @endif
    @if (session('error'))
        <x-alert type="error" class="mb-6">{{ session('error') }}</x-alert>
    @endif

    <x-card :padding="false">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Nom</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Rôle</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Date d'inscription</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold uppercase text-slate-500">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
                @forelse ($users as $user)
                    @php
                        $roleBadge = $user->hasRole('admin') ? 'admin' : ($user->hasRole('instructor') ? 'instructor' : 'learner');
                    @endphp
                    <tr class="hover:bg-slate-50">
                        <td class="whitespace-nowrap px-6 py-4">
                            <div class="text-sm font-medium text-slate-900">{{ $user->name }}</div>
                            <div class="text-sm text-slate-500">{{ $user->email }}</div>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4">
                            <form method="POST" action="{{ route('admin.users.role', $user) }}" class="flex items-center gap-2">
                                @csrf @method('PATCH')
                                <x-select name="role" class="w-36">
                                    <option value="learner" @selected($user->hasRole('learner'))>Apprenant</option>
                                    <option value="instructor" @selected($user->hasRole('instructor'))>Formateur</option>
                                    <option value="admin" @selected($user->hasRole('admin'))>Admin</option>
                                </x-select>
                                <x-button type="submit" size="sm" variant="secondary">OK</x-button>
                            </form>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-500">{{ $user->created_at->format('d/m/Y') }}</td>
                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                            <x-button variant="danger" size="sm" type="button" @click="$dispatch('open-modal', 'delete-user-{{ $user->id }}')">Supprimer</x-button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-sm text-slate-500">Aucun utilisateur trouvé.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-card>

    @foreach ($users as $user)
        <x-confirm-modal name="delete-user-{{ $user->id }}" title="Supprimer l'utilisateur">
            <p class="text-sm text-slate-600">Voulez-vous vraiment supprimer l'utilisateur « {{ $user->name }} » ?</p>
            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="mt-6 flex justify-end gap-3">
                @csrf @method('DELETE')
                <x-button type="button" variant="secondary" @click="$dispatch('close-modal', 'delete-user-{{ $user->id }}')">Annuler</x-button>
                <x-button type="submit" variant="danger">Supprimer</x-button>
            </form>
        </x-confirm-modal>
    @endforeach

    <x-pagination :paginator="$users" />
</x-dashboard-layout>

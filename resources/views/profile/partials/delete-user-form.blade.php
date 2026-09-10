<section class="space-y-6">
    <header>
        <h2 class="text-lg font-semibold text-red-700">Supprimer le compte</h2>

        <p class="mt-1 text-sm text-slate-500">
            Une fois votre compte supprimé, toutes ses ressources et données seront définitivement effacées. Avant de supprimer votre compte, téléchargez toute donnée ou information que vous souhaitez conserver.
        </p>
    </header>

    <x-button
        variant="danger"
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >Supprimer le compte</x-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-semibold text-slate-900">
                Voulez-vous vraiment supprimer votre compte ?
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Une fois votre compte supprimé, toutes ses ressources et données seront définitivement effacées. Saisissez votre mot de passe pour confirmer la suppression définitive de votre compte.
            </p>

            <div class="mt-6">
                <x-input-label for="password" value="Mot de passe" class="sr-only" />

                <x-input
                    id="password"
                    name="password"
                    type="password"
                    placeholder="Mot de passe"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end">
                <x-button type="button" variant="secondary" x-on:click="$dispatch('close')">
                    Annuler
                </x-button>

                <x-button type="submit" variant="danger" class="ms-3">
                    Supprimer le compte
                </x-button>
            </div>
        </form>
    </x-modal>
</section>
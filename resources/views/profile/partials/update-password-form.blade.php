<section>
    <h2 class="text-sm font-semibold text-slate-900">Changer le mot de passe</h2>

    <form method="post" action="{{ route('password.update') }}" class="mt-5 space-y-5">
        @csrf
        @method('put')

        <div>
            <x-input-label for="update_password_current_password" class="mb-1.5" value="Mot de passe actuel" />
            <x-input id="update_password_current_password" name="current_password" type="password" autocomplete="current-password" :error="$errors->updatePassword->has('current_password')" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password" class="mb-1.5" value="Nouveau mot de passe" />
            <x-input id="update_password_password" name="password" type="password" autocomplete="new-password" :error="$errors->updatePassword->has('password')" />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password_confirmation" class="mb-1.5" value="Confirmez le nouveau mot de passe" />
            <x-input id="update_password_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" :error="$errors->updatePassword->has('password_confirmation')" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <button type="submit" class="w-full rounded-lg bg-slate-700 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-400 focus:ring-offset-1">
            Mettre à jour le mot de passe
        </button>

        @if (session('status') === 'password-updated')
            <p
                x-data="{ show: true }"
                x-show="show"
                x-transition
                x-init="setTimeout(() => show = false, 2000)"
                class="text-center text-sm text-slate-500"
            >Enregistré.</p>
        @endif
    </form>
</section>
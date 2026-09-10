<section>
    <h2 class="text-sm font-semibold text-slate-900">Informations personnelles</h2>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-5 space-y-5">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" class="mb-1.5" value="Nom" />
            <x-input id="name" name="name" type="text" :value="old('name', $user->name)" placeholder="Marie Dupont" required autofocus autocomplete="name" :error="$errors->has('name')" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" class="mb-1.5" value="Adresse e-mail" />
            <x-input id="email" name="email" type="email" :value="old('email', $user->email)" placeholder="alice@exemple.com" required autocomplete="username" :error="$errors->has('email')" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-4">
                    <p class="text-sm text-slate-500">
                        Votre adresse e-mail n'est pas vérifiée.

                        <button form="send-verification" class="rounded-md text-sm font-medium text-indigo-600 hover:text-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-indigo-500">
                            Cliquez ici pour renvoyer l'e-mail de vérification.
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 text-sm font-medium text-emerald-600">
                            Un nouveau lien de vérification a été envoyé à votre adresse e-mail.
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <button type="submit" class="w-full rounded-lg bg-indigo-600 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
            Enregistrer
        </button>

        @if (session('status') === 'profile-updated')
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
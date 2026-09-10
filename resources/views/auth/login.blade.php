<x-guest-layout title="Connexion" description="Accédez à votre espace d'apprentissage.">
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="email" class="mb-1.5" value="Adresse e-mail" />
            <x-input id="email" type="email" name="email" :value="old('email')" placeholder="alice@exemple.com" required autofocus autocomplete="username" :error="$errors->has('email')" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />
        </div>

        <div>
            <div class="mb-1.5 flex items-center justify-between">
                <x-input-label for="password" value="Mot de passe" />

                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-700">
                        Mot de passe oublié ?
                    </a>
                @endif
            </div>
            <x-input id="password" type="password" name="password" required autocomplete="current-password" :error="$errors->has('password')" />
            <x-input-error class="mt-2" :messages="$errors->get('password')" />
        </div>

        <label for="remember_me" class="flex items-center">
            <input id="remember_me" type="checkbox" name="remember" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
            <span class="ms-2 text-sm text-slate-600">Se souvenir de moi</span>
        </label>

        <button type="submit" class="w-full rounded-lg bg-indigo-600 py-2.5 text-sm font-medium text-white transition-colors hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
            Se connecter
        </button>

        <p class="text-center text-sm text-slate-500">
            Pas encore de compte ?
            <a href="{{ route('register') }}" class="font-medium text-indigo-600 hover:text-indigo-700">S'inscrire</a>
        </p>
    </form>
</x-guest-layout>
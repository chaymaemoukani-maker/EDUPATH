<x-guest-layout title="Créer un compte" description="Rejoignez EduPath gratuitement et suivez vos cours.">
    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="name" class="mb-1.5" value="Nom complet" />
            <x-input id="name" type="text" name="name" :value="old('name')" placeholder="Marie Dupont" required autofocus autocomplete="name" :error="$errors->has('name')" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" class="mb-1.5" value="Adresse e-mail" />
            <x-input id="email" type="email" name="email" :value="old('email')" placeholder="alice@exemple.com" required autocomplete="username" :error="$errors->has('email')" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />
        </div>

        <div>
            <x-input-label for="password" class="mb-1.5" value="Mot de passe" />
            <x-input id="password" type="password" name="password" required autocomplete="new-password" :error="$errors->has('password')" />
            <x-input-error class="mt-2" :messages="$errors->get('password')" />
        </div>

        <div>
            <x-input-label for="password_confirmation" class="mb-1.5" value="Confirmez le mot de passe" />
            <x-input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" :error="$errors->has('password_confirmation')" />
            <x-input-error class="mt-2" :messages="$errors->get('password_confirmation')" />
        </div>

        <button type="submit" class="w-full rounded-lg bg-indigo-600 py-2.5 text-sm font-medium text-white transition-colors hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
            S'inscrire
        </button>

        <p class="text-center text-sm text-slate-500">
            Déjà inscrit ?
            <a href="{{ route('login') }}" class="font-medium text-indigo-600 hover:text-indigo-700">Se connecter</a>
        </p>
    </form>
</x-guest-layout>
<x-guest-layout title="Mot de passe oublié ?" description="Indiquez votre adresse e-mail et nous vous enverrons un lien pour réinitialiser votre mot de passe.">
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="email" class="mb-1.5" value="Adresse e-mail" />
            <x-input id="email" type="email" name="email" :value="old('email')" placeholder="alice@exemple.com" required autofocus :error="$errors->has('email')" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />
        </div>

        <button type="submit" class="w-full rounded-lg bg-indigo-600 py-2.5 text-sm font-medium text-white transition-colors hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
            Envoyer le lien de réinitialisation
        </button>

        <p class="text-center text-sm text-slate-500">
            <a href="{{ route('login') }}" class="font-medium text-indigo-600 hover:text-indigo-700">Retour à la connexion</a>
        </p>
    </form>
</x-guest-layout>
<x-guest-layout title="Confirmez votre mot de passe" description="Il s'agit d'une zone sécurisée de l'application. Veuillez confirmer votre mot de passe avant de continuer.">
    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="password" class="mb-1.5" value="Mot de passe" />
            <x-input id="password" type="password" name="password" required autocomplete="current-password" :error="$errors->has('password')" />
            <x-input-error class="mt-2" :messages="$errors->get('password')" />
        </div>

        <button type="submit" class="w-full rounded-lg bg-indigo-600 py-2.5 text-sm font-medium text-white transition-colors hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
            Confirmer
        </button>
    </form>
</x-guest-layout>
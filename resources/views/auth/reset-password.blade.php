<x-guest-layout title="Réinitialiser le mot de passe" description="Choisissez un nouveau mot de passe pour votre compte.">
    <form method="POST" action="{{ route('password.store') }}" class="space-y-5">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <x-input-label for="email" class="mb-1.5" value="Adresse e-mail" />
            <x-input id="email" type="email" name="email" :value="old('email', $request->email)" placeholder="alice@exemple.com" required autofocus autocomplete="username" :error="$errors->has('email')" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />
        </div>

        <div>
            <x-input-label for="password" class="mb-1.5" value="Nouveau mot de passe" />
            <x-input id="password" type="password" name="password" required autocomplete="new-password" :error="$errors->has('password')" />
            <x-input-error class="mt-2" :messages="$errors->get('password')" />
        </div>

        <div>
            <x-input-label for="password_confirmation" class="mb-1.5" value="Confirmez le nouveau mot de passe" />
            <x-input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" :error="$errors->has('password_confirmation')" />
            <x-input-error class="mt-2" :messages="$errors->get('password_confirmation')" />
        </div>

        <button type="submit" class="w-full rounded-lg bg-indigo-600 py-2.5 text-sm font-medium text-white transition-colors hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
            Réinitialiser le mot de passe
        </button>
    </form>
</x-guest-layout>
<x-guest-layout title="Vérification de votre adresse e-mail" description="Merci pour votre inscription ! Avant de commencer, vérifiez votre adresse e-mail en cliquant sur le lien que nous venons de vous envoyer.">
    @if (session('status') == 'verification-link-sent')
        <x-alert type="success" title="Lien envoyé" class="mb-6">Un nouveau lien de vérification a été envoyé à votre adresse e-mail.</x-alert>
    @endif

    <div class="space-y-4">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <button type="submit" class="w-full rounded-lg bg-indigo-600 py-2.5 text-sm font-medium text-white transition-colors hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                Renvoyer l'e-mail de vérification
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="w-full py-1 text-sm font-medium text-slate-600 hover:text-slate-900">
                Se déconnecter
            </button>
        </form>
    </div>
</x-guest-layout>
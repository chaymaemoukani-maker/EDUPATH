<?php

namespace App\Exceptions;

use RuntimeException;

class AiAssistantException extends RuntimeException
{
    public static function missingApiKey(): self
    {
        return new self('L’assistant IA n’est pas encore configuré : la clé API est absente du fichier .env (GROQ_API_KEY).');
    }

    public static function apiError(string $detail): self
    {
        return new self('Le service IA a renvoyé une erreur ('.$detail.'). Réessayez dans un instant.');
    }

    public static function unreachable(): self
    {
        return new self('Impossible de joindre le service IA. Vérifiez votre connexion puis réessayez.');
    }
}

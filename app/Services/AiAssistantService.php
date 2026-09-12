<?php

namespace App\Services;

use App\Exceptions\AiAssistantException;
use App\Models\Course;
use App\Models\Module;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class AiAssistantService
{
    private const CURRENT_MODULE_CONTENT_CHARS = 1500;

    private const HISTORY_LIMIT = 6;

    public function __construct(private readonly ProgressService $progress) {}

    /**
     * Ask the AI tutor a question on an enrolled course, with a short
     * conversation history for follow-ups.
     */
    public function ask(User $user, Course $course, string $question, array $history = []): string
    {
        $apiKey = (string) config('services.groq.key');

        if ($apiKey === '') {
            throw AiAssistantException::missingApiKey();
        }

        $course->loadMissing(['category', 'instructor', 'sections.modules.quiz']);

        $messages = [
            ['role' => 'system', 'content' => $this->buildSystemMessage($user, $course)],
        ];

        foreach (array_slice($history, -self::HISTORY_LIMIT) as $message) {
            $messages[] = ['role' => $message['role'], 'content' => $message['content']];
        }

        $messages[] = ['role' => 'user', 'content' => $question];

        try {
            $baseUrl = (string) config('services.groq.base_url');
            $response = Http::timeout((int) config('services.groq.timeout', 30))
                ->acceptJson()
                ->withToken($apiKey)
                ->post($baseUrl.'/chat/completions', [
                    'model' => config('services.groq.model'),
                    'messages' => $messages,
                    'temperature' => 0.3,
                    'max_tokens' => 700,
                ]);
        } catch (ConnectionException $e) {
            throw AiAssistantException::unreachable();
        }

        if (! $response->successful()) {
            throw AiAssistantException::apiError((string) $response->status());
        }

        $answer = $response->json('choices.0.message.content');

        if (! is_string($answer) || $answer === '') {
            throw AiAssistantException::apiError('réponse vide');
        }

        return $answer;
    }

    private function buildSystemMessage(User $user, Course $course): string
    {
        $completedModuleIds = $user->progress()->pluck('module_id')->all();
        $total = $this->progress->totalModules($course);
        $completedCount = count($completedModuleIds);
        $percent = $this->progress->percent($user, $course);
        $currentModule = $this->currentModule($course, $completedModuleIds);

        $completedTitles = $course->sections
            ->flatMap(fn ($section) => $section->modules)
            ->filter(fn (Module $module) => in_array($module->id, $completedModuleIds, true))
            ->pluck('title')
            ->all();

        return implode(PHP_EOL, [
            'Tu es l’assistant IA d’EduPath, un tuteur pédagogique patient qui accompagne l’apprenant dans un cours.',
            'Réponds en français, de façon claire et concise, en markdown léger (listes, paragraphes courts, **gras**).',
            'Base toutes tes réponses UNIQUEMENT sur le contexte fourni ci-dessous. N’invente jamais de contenu, de module ou de sujet absent du contexte.',
            'Si le contexte ne permet pas de répondre, dis-le honnêtement au lieu d’improviser.',
            'Soutien attendu : expliquer une question de cours, simplifier un concept difficile, faire ressortir les concepts importants, indiquer la prochaine étape d’apprentissage selon la progression, et aider à se préparer au quiz.',
            'Ne révèle JAMAIS les questions ni les réponses du quiz : propose plutôt des révisions sur les notions déjà étudiées.',
            '',
            'CONTEXTE DE L’APPRENANT',
            'Cours : '.$course->title,
            'Description : '.$course->description,
            'Catégorie : '.($course->category->name ?? '—'),
            'Formateur : '.($course->instructor->name ?? '—'),
            'Statut : '.($course->status === 'published' ? 'Publié' : 'Brouillon'),
            'Progression de l’apprenant : '.$completedCount.' module(s) terminé(s) sur '.$total.' ('.$percent.' %).',
            'Modules déjà terminés : '.($completedTitles !== [] ? implode(' ; ', $completedTitles) : 'aucun'),
            'Curriculum (en ordre) :',
            ...$this->curriculumLines($course, $completedModuleIds),
            '',
            'MODULE COURANT : '.($currentModule ? $this->moduleLine($currentModule, in_array($currentModule->id, $completedModuleIds, true)) : 'aucun module en cours'),
        ] + ($currentModule ? $this->currentContentLines($currentModule) : [])
          + ($currentModule && $currentModule->quiz ? $this->quizLines($currentModule->quiz, $user) : []));
    }

    /**
     * @return list<string>
     */
    private function curriculumLines(Course $course, array $completedModuleIds): array
    {
        $lines = [];

        foreach ($course->sections->sortBy('order') as $section) {
            $lines[] = 'Section « '.$section->title.' » :';

            foreach ($section->modules->sortBy('order') as $module) {
                $lines[] = '  - '.$this->moduleLine($module, in_array($module->id, $completedModuleIds, true));
            }
        }

        return $lines !== [] ? $lines : ['(aucune section)'];
    }

    private function moduleLine(Module $module, bool $completed): string
    {
        $typeLabel = ['text' => 'Texte', 'video' => 'Vidéo', 'pdf' => 'PDF'][$module->type] ?? $module->type;
        $state = $completed ? 'TERMINÉ' : 'EN COURS / NON COMMENCÉ';

        return '['.$state.'] « '.$module->title.' » ('.$typeLabel.')';
    }

    private function currentModule(Course $course, array $completedModuleIds): ?Module
    {
        return $course->sections
            ->sortBy('order')
            ->flatMap(fn ($section) => $section->modules->sortBy('order'))
            ->first(fn (Module $module) => ! in_array($module->id, $completedModuleIds, true));
    }

    /**
     * @return list<string>
     */
    private function currentContentLines(Module $module): array
    {
        if ($module->type !== 'text' || $module->content === null || $module->content === '') {
            return [];
        }

        return [
            'CONTENU DU MODULE COURANT (extrait) :',
            Str::limit($module->content, self::CURRENT_MODULE_CONTENT_CHARS),
        ];
    }

    /**
     * @return list<string>
     */
    private function quizLines(Quiz $quiz, User $user): array
    {
        $usedAttempts = $quiz->quizAttempts()->where('user_id', $user->id)->count();

        return [
            'QUIZ ASSOCIÉ AU MODULE COURANT : « '.$quiz->title.' » — score de réussite '.$quiz->pass_score.' %, '.$quiz->questions()->count().' question(s), tentatives déjà utilisées : '.$usedAttempts.'/'.$quiz->max_attempts.' (les questions et réponses ne doivent jamais être révélées).',
        ];
    }
}

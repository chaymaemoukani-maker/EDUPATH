<?php

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Module;
use App\Models\Progress;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\Section;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);

    Config::set('services.groq.key', 'gsk-test-key');
    Config::set('services.groq.model', 'openai/gpt-oss-20b');
    Config::set('services.groq.timeout', 30);
    Config::set('services.groq.base_url', 'https://api.groq.com/openai/v1');

    $this->learner = User::factory()->create();
    $this->learner->addRole('learner');

    $this->instructor = User::factory()->create();
    $this->instructor->addRole('instructor');

    $this->admin = User::factory()->create();
    $this->admin->addRole('admin');

    $this->course = Course::factory()->published()->create(['title' => 'Cours IA de test']);
    $section = Section::factory()->create(['course_id' => $this->course->id, 'order' => 1]);
    $this->module = Module::factory()->text()->create(['section_id' => $section->id, 'order' => 1, 'title' => 'Module introduction']);
    $this->quizModule = Module::factory()->text()->create(['section_id' => $section->id, 'order' => 2, 'title' => 'Module quiz']);
    $this->quiz = Quiz::factory()->create(['module_id' => $this->quizModule->id, 'pass_score' => 70, 'max_attempts' => 2]);
    Question::factory()->create(['quiz_id' => $this->quiz->id, 'text' => 'SECRET_QUESTION_QUIZ']);

    Enrollment::factory()->create(['user_id' => $this->learner->id, 'course_id' => $this->course->id]);
});

function aiAssistantUrl(): string
{
    return route('learner.ai-assistant.index');
}

test('page de l’assistant IA est accessible à un apprenant inscrit', function () {
    $this->actingAs($this->learner)
        ->get(aiAssistantUrl())
        ->assertOk()
        ->assertSee('Assistant IA')
        ->assertSee('Cours IA de test');
});

test('la page redirige un visiteur non connecté vers la connexion', function () {
    $this->get(aiAssistantUrl())
        ->assertRedirect(route('login'));
});

test('la page est interdite aux formateurs et aux admins', function () {
    $this->actingAs($this->instructor)->get(aiAssistantUrl())->assertForbidden();
    $this->actingAs($this->admin)->get(aiAssistantUrl())->assertForbidden();
});

test('un apprenant sans cours inscrit voit l’état vide sans formulaire', function () {
    $bareLearner = User::factory()->create();
    $bareLearner->addRole('learner');

    $this->actingAs($bareLearner)
        ->get(aiAssistantUrl())
        ->assertOk()
        ->assertSee('Vous devez être inscrit à au moins un cours');
});

test('la question est obligatoire et limitée à 1000 caractères', function () {
    $this->actingAs($this->learner)
        ->from(aiAssistantUrl())
        ->post(route('learner.ai-assistant.ask'), [
            'course_id' => $this->course->id,
            'question' => '',
        ])
        ->assertSessionHasErrors('question');

    $this->actingAs($this->learner)
        ->from(aiAssistantUrl())
        ->post(route('learner.ai-assistant.ask'), [
            'course_id' => $this->course->id,
            'question' => str_repeat('a', 1001),
        ])
        ->assertSessionHasErrors('question');
});

test('le course_id doit exister et appartenir à un cours inscrit', function () {
    $other = Course::factory()->published()->create();

    $this->actingAs($this->learner)
        ->from(aiAssistantUrl())
        ->post(route('learner.ai-assistant.ask'), [
            'course_id' => 999999,
            'question' => 'Explique la notion d’authentification.',
        ])
        ->assertSessionHasErrors('course_id');

    $this->actingAs($this->learner)
        ->from(aiAssistantUrl())
        ->post(route('learner.ai-assistant.ask'), [
            'course_id' => $other->id,
            'question' => 'Explique la notion d’authentification.',
        ])
        ->assertForbidden();
});

test('une question reçoit une réponse et la conversation est conservée', function () {
    Http::fake([
        'https://api.groq.com/openai/v1/*' => Http::response([
            'choices' => [
                ['message' => ['content' => 'Voici la réponse pédagogique.']],
            ],
        ], 200),
    ]);

    $this->actingAs($this->learner)
        ->from(aiAssistantUrl())
        ->post(route('learner.ai-assistant.ask'), [
            'course_id' => $this->course->id,
            'question' => 'Explique les routes en Laravel.',
        ])
        ->assertRedirect(route('learner.ai-assistant.index', ['course' => $this->course->id]));

    $this->actingAs($this->learner)
        ->get(route('learner.ai-assistant.index', ['course' => $this->course->id]))
        ->assertOk()
        ->assertSee('Explique les routes en Laravel.')
        ->assertSee('Voici la réponse pédagogique.');

    Http::assertSent(
        fn (Request $request): bool => $request->url() === 'https://api.groq.com/openai/v1/chat/completions'
            && ($request->header('Authorization')[0] ?? null) === 'Bearer '.Config::get('services.groq.key')
            && $request->data()['model'] === Config::get('services.groq.model')
            && collect($request->data()['messages'])->contains(fn ($message) => $message['role'] === 'system' && str_contains($message['content'], 'Cours IA de test'))
    );

    Http::assertNotSent(fn (Request $request) => str_contains(json_encode($request->data()), 'SECRET_QUESTION_QUIZ'));
});

test('la conversation en session alimente la relance sans révéler les questions du quiz', function () {
    Http::fake([
        'https://api.groq.com/openai/v1/*' => Http::response([
            'choices' => [
                ['message' => ['content' => 'Première réponse.']],
            ],
        ], 200),
    ]);

    $this->actingAs($this->learner)->post(route('learner.ai-assistant.ask'), [
        'course_id' => $this->course->id,
        'question' => 'Première question.',
    ]);
    $this->actingAs($this->learner)->post(route('learner.ai-assistant.ask'), [
        'course_id' => $this->course->id,
        'question' => 'Relance.',
    ]);

    // The second call must include the system message + the two stored messages (user/assistant) + the new question.
    $requests = Http::recorded();

    $secondCall = $requests[1][0];
    $messages = collect($secondCall->data()['messages']);
    $contents = implode(' ', $messages->pluck('content')->all());

    expect($messages->count())->toBe(4)
        ->and($contents)->toContain('Première question.')
        ->and($contents)->not->toContain('SECRET_QUESTION_QUIZ');
});

test('une erreur d’API renvoie un message d’erreur clair', function () {
    Http::fake([
        'https://api.groq.com/openai/v1/*' => Http::response(['error' => ['message' => 'boom']], 500),
    ]);

    $this->actingAs($this->learner)
        ->from(aiAssistantUrl())
        ->post(route('learner.ai-assistant.ask'), [
            'course_id' => $this->course->id,
            'question' => 'Explique ce module.',
        ])
        ->assertRedirect(aiAssistantUrl())
        ->assertSessionHas('error');
});

test('un timeout ou une panne réseau est géré proprement', function () {
    Http::fake([
        'https://api.groq.com/openai/v1/*' => function () {
            throw new ConnectionException('cURL error 28: Operation timed out');
        },
    ]);

    $this->actingAs($this->learner)
        ->from(aiAssistantUrl())
        ->post(route('learner.ai-assistant.ask'), [
            'course_id' => $this->course->id,
            'question' => 'Explique ce module.',
        ])
        ->assertRedirect(aiAssistantUrl())
        ->assertSessionHas('error');
});

test('l’app n’appelle pas l’API et affiche une erreur si la clé Groq est absente', function () {
    Config::set('services.groq.key', '');

    $this->actingAs($this->learner)
        ->from(aiAssistantUrl())
        ->post(route('learner.ai-assistant.ask'), [
            'course_id' => $this->course->id,
            'question' => 'Explique ce module.',
        ])
        ->assertRedirect(aiAssistantUrl())
        ->assertSessionHas('error');

    Http::assertNothingSent();
});

test('une réponse vide ou invalide de l’API est gérée proprement', function () {
    Http::fake([
        'https://api.groq.com/openai/v1/*' => Http::response(['choices' => []], 200),
    ]);

    $this->actingAs($this->learner)
        ->from(aiAssistantUrl())
        ->post(route('learner.ai-assistant.ask'), [
            'course_id' => $this->course->id,
            'question' => 'Explique ce module.',
        ])
        ->assertRedirect(aiAssistantUrl())
        ->assertSessionHas('error');

    $this->actingAs($this->learner)
        ->get(route('learner.ai-assistant.index', ['course' => $this->course->id]))
        ->assertOk()
        ->assertSee('Le service IA a renvoyé une erreur (réponse vide)')
        ->assertSee('Bonjour ! Posez une question');
});

test('la progression de l’apprenant est transmise dans le contexte envoyé à l’API', function () {
    Progress::factory()->create(['user_id' => $this->learner->id, 'module_id' => $this->module->id]);

    Http::fake([
        'https://api.groq.com/openai/v1/*' => Http::response([
            'choices' => [
                ['message' => ['content' => 'OK']],
            ],
        ], 200),
    ]);

    $this->actingAs($this->learner)->post(route('learner.ai-assistant.ask'), [
        'course_id' => $this->course->id,
        'question' => 'Que dois-je faire ensuite ?',
    ]);

    Http::assertSent(function (Request $request) {
        $system = collect($request->data()['messages'])->firstWhere('role', 'system')['content'];

        return str_contains($system, '1 module(s) terminé(s) sur 2 (50 %).')
            && str_contains($system, 'Module introduction');
    });
});

<?php

namespace Database\Seeders;

use App\Models\Answer;
use App\Models\Category;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Module;
use App\Models\Progress;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Section;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Seeds a realistic, French-speaking demo dataset covering the learning paths A–F:
 * A) instructor draft course, B) admin-published course, C) enrolled learner,
 * D) partial progression, E) 100% completion + certificate, F) rich multi-question quiz.
 *
 * Constraints respected: no course rating, no certificate score, no "quiz" module type,
 * courses only ever pass to "published" as the admin does it, Laratrust is the sole role source.
 */
class DemoDataSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->makeDemoPdfs();
        $admin = $this->user('Admin EduPath', 'admin@example.com', 'admin');
        $this->user('Instructor Demo', 'instructor@example.com', 'instructor');

        $sophie = $this->user('Sophie Martin', 'sophie.martin@example.com', 'instructor');
        $karim = $this->user('Karim Benali', 'karim.benali@example.com', 'instructor');

        $lea = $this->user('Léa Dubois', 'lea.dubois@example.com', 'learner');
        $hugo = $this->user('Hugo Lefèvre', 'hugo.lefevre@example.com', 'learner');
        $ines = $this->user('Inès Moreau', 'ines.moreau@example.com', 'learner');

        $categories = [
            'developpement-web' => ['name' => 'Développement Web'],
            'design-graphique' => ['name' => 'Design Graphique'],
            'marketing-digital' => ['name' => 'Marketing Digital'],
            'data-ia' => ['name' => 'Data & IA'],
        ];

        foreach ($categories as $slug => $props) {
            Category::firstOrCreate(['slug' => $slug], $props);
        }

        $web = Category::where('slug', 'developpement-web')->first();
        $design = Category::where('slug', 'design-graphique')->first();
        $marketing = Category::where('slug', 'marketing-digital')->first();
        $data = Category::where('slug', 'data-ia')->first();

        // ------------------------------------------------------------------
        // A) Instructor draft course (never published by its owner).
        // ------------------------------------------------------------------
        $draft = $this->course($sophie, $web, 'Introduction à PHP 8', 'draft', [
            $this->section('Les bases', [
                $this->module('Syntaxe et variables', 'text', "PHP 8 est un langage de script côté serveur.\n\nLes variables commencent par le symbole \$ et ne sont pas typées au déclaration, mais l'usage est fortement recommandé."),
                $this->module('Les conditions', 'text', "if / else, switch, et l'opérateur ternaire permettent de brancher la logique.\n\nPensez à toujours couvrir le cas par défaut."),
            ]),
            $this->section('Aller plus loin', [
                $this->module('Les fonctions', 'video', 'https://www.youtube.com/embed/dQw4w9WgXcQ'),
            ]),
        ]);

        // ------------------------------------------------------------------
        // B) Admin-published course, rich content + quiz (scenario F shares this quiz).
        // ------------------------------------------------------------------
        $laravel = $this->course($karim, $web, 'Maîtriser Laravel 12', 'published', [
            $this->section('Démarrage', [
                $this->module('Installation', 'text', "Installation de Laravel via Composer :\n\n   composer create-project laravel/laravel mon-app\n\nPuis vérifiez que la commande artisan répond : php artisan --version."),
                $this->module('Architecture MVC', 'video', 'https://www.youtube.com/embed/dQw4w9WgXcQ'),
                $this->module('Premières routes', 'text', "Les routes vivent dans routes/web.php.\n\nRoute::get('/hello', fn () => 'Bonjour'); suffit pour une première réponse HTTP."),
            ]),
            $this->section('Modèles et base de données', [
                $this->module('Eloquent et migrations', 'text', "Les migrations décrivent le schéma, Eloquent manipule les données.\n\nphp artisan make:model Article -m crée le modèle et sa migration en une commande."),
                $this->module('Relations', 'video', 'https://www.youtube.com/embed/dQw4w9WgXcQ'),
                $this->module('Guide pratique', 'pdf', 'modules/laravel-guide-pratique.pdf'),
                $this->module('Quiz de validation', 'text', "Validez vos connaissances avec le quiz ci-dessous avant de passer à la suite."),
            ]),
        ]);

        $this->attachQuiz($laravel, 'Quiz — Laravel 12', 60, 3, [
            ['Quel fichier définit les routes web ?', ['routes/web.php', 'routes/api.php', 'app/Http/web.php', 'config/web.php'], 0],
            ['Quelle commande applique les migrations ?', ['php artisan migrate', 'php artisan schema', 'composer migrate', 'npm run migrate'], 0],
            ['Quelle classe permet d\'interagir avec une table ?', ['Eloquent Model', 'Blade Component', 'Middleware', 'Service Provider'], 0],
            ['Quel langage alimente les vues Blade ?', ['PHP', 'JavaScript', 'Python', 'Ruby'], 0],
            ['Quel est l\'utilitaire officiel de gestion de dépendances ?', ['Composer', 'npm', 'Yarn', 'Pip'], 0],
        ]);

        // ------------------------------------------------------------------
        // C) Enrolled learner (no progress yet) on the published Laravel course.
        // ------------------------------------------------------------------
        $this->enroll($lea, $laravel);

        // ------------------------------------------------------------------
        // D) Partial progression (~50%) on the Design course.
        // ------------------------------------------------------------------
        $designCourse = $this->course($sophie, $design, 'Design UX/UI : les fondamentaux', 'published', [
            $this->section('Concevoir', [
                $this->module('Couleurs et contraste', 'text', "La couleur guide le regard. Respectez un contraste AA minimum entre texte et fond pour rester accessible."),
                $this->module('Typographie', 'text', "Une hiérarchie claire (titres, sous-titres, corps) rend la lecture confortable. Limitez-vous à deux familles."),
            ]),
            $this->section('Prototyper', [
                $this->module('Wireframes', 'video', 'https://www.youtube.com/embed/dQw4w9WgXcQ'),
                $this->module('Tests utilisateurs', 'pdf', 'modules/guides-tests-utilisateurs.pdf'),
            ]),
        ]);

        $this->enroll($hugo, $designCourse);

        $designSections = $designCourse->sections()->orderBy('order')->get();
        $firstHalf = $designSections->first()->modules()->orderBy('order')->get();
        foreach ($firstHalf as $module) {
            Progress::firstOrCreate([
                'user_id' => $hugo->id,
                'module_id' => $module->id,
            ], ['completed_at' => now()]);
        }

        // ------------------------------------------------------------------
        // E) 100% completion → certificate (no score anywhere on the certificate).
        // ------------------------------------------------------------------
        $marketingCourse = $this->course($karim, $marketing, 'Marketing Digital 101', 'published', [
            $this->section('Stratégie', [
                $this->module('Définir ses personas', 'text', "Un persona est une représentation semi-fictive de votre client idéal, fondée sur des données réelles."),
                $this->module('Analyser le marché', 'video', 'https://www.youtube.com/embed/dQw4w9WgXcQ'),
            ]),
            $this->section('Acquisition', [
                $this->module('SEO et contenu', 'text', "Le référencement naturel repose sur du contenu utile, une technique saine et des backlinks qualitatifs."),
                $this->module('Quiz — Marketing Digital', 'text', "Dernière étape : passez le quiz pour valider le module."),
            ]),
        ]);

        $this->attachQuiz($marketingCourse, 'Quiz — Marketing Digital', 60, 3, [
            ['Qu\'est-ce qu\'un persona ?', ['Une représentation du client idéal', 'Un logo', 'Un fichier marketing', 'Une campagne payante'], 0],
            ['Que signifie SEO ?', ['Search Engine Optimization', 'Social Engine Online', 'Site Enhancement Order', 'Simple Email Output'], 0],
        ]);

        $this->enroll($ines, $marketingCourse);

        foreach ($marketingCourse->sections()->orderBy('order')->get() as $section) {
            foreach ($section->modules()->orderBy('order')->get() as $module) {
                Progress::firstOrCreate([
                    'user_id' => $ines->id,
                    'module_id' => $module->id,
                ], ['completed_at' => now()]);
            }
        }

        $certificate = $this->certificate($ines, $marketingCourse);

        $quizMarketing = $marketingCourse->sections()
            ->orderBy('order')->get()
            ->last()->modules()->orderBy('order')->get()
            ->last()
            ->quiz;

        if ($quizMarketing) {
            QuizAttempt::firstOrCreate([
                'user_id' => $ines->id,
                'quiz_id' => $quizMarketing->id,
            ], [
                'score' => 100,
                'passed' => true,
                'attempted_at' => now(),
            ]);
        }

        // ------------------------------------------------------------------
        // F) The Laravel quiz above already carries 5 questions x 4 answers.
        // The QuizAttempt against quizzes_id is satisfied by the module quiz.
        // ------------------------------------------------------------------

        // ------------------------------------------------------------------
        // G) Published courses matching the design reference cover images.
        // ------------------------------------------------------------------
        $this->course($karim, $web, 'Laravel pour débutants', 'published', [
            $this->section('Démarrer', [
                $this->module('Installer Laravel', 'text', "Installation de Laravel via Composer, premières routes et structure d'un projet."),
                $this->module('Premier contrôleur', 'video', 'https://www.youtube.com/embed/dQw4w9WgXcQ'),
            ]),
        ]);

        $this->course($karim, $web, 'JavaScript moderne — ES6+', 'published', [
            $this->section('Les bases', [
                $this->module('Variables et types', 'text', "let, const, templates literals et typage dynamique pour manipuler les données."),
                $this->module('Fonctions et flèches', 'text', "Fonctions fléchées, closures et méthodes de tableau pour un code moderne et concis."),
            ]),
        ]);

        $this->course($sophie, $web, 'HTML & CSS — Les fondamentaux', 'published', [
            $this->section('Structurer', [
                $this->module('Balises sémantiques', 'text', "header, nav, main, section et footer : une structure claire et accessible."),
                $this->module('Mise en page CSS', 'video', 'https://www.youtube.com/embed/dQw4w9WgXcQ'),
            ]),
        ]);

        $this->course($sophie, $data, 'MySQL pour débutants', 'published', [
            $this->section('Modéliser', [
                $this->module('Tables et clés', 'text', "Créer des tables, définir des clés primaires et étrangères pour modéliser des données relationnelles."),
                $this->module('Requêtes SQL', 'text', "SELECT, INSERT, UPDATE et jointures pour interroger et maintenir vos données."),
            ]),
        ]);

        $this->course($karim, $web, 'React.js — De zéro à héros', 'published', [
            $this->section('Composants', [
                $this->module('Créer un composant', 'text', "Composants, props et JSX : les briques de toute interface React."),
                $this->module('État et effets', 'video', 'https://www.youtube.com/embed/dQw4w9WgXcQ'),
            ]),
        ]);

        $this->course($sophie, $web, 'Tailwind CSS — Design rapide', 'published', [
            $this->section('Les bases', [
                $this->module('Classes utilitaires', 'text', "Styliser directement dans le markup avec les classes utilitaires de Tailwind."),
                $this->module('Responsive et thème', 'text', "Variantes responsive, thème configurable et composants propres à l'aide de @apply."),
            ]),
        ]);

        $this->command?->info('DemoDataSeeder terminé (draft, published, enrollment, progression 50%, 100% + certificat, quiz complet).');
    }

    // ----- Helpers ---------------------------------------------------------

    private function user(string $name, string $email, string $role): User
    {
        $user = User::firstOrCreate(
            ['email' => $email],
            ['name' => $name, 'password' => Hash::make('password')]
        );

        if (! $user->hasRole($role)) {
            $user->addRole($role);
        }

        return $user;
    }

    private function course(User $instructor, Category $category, string $title, string $status, array $sections): Course
    {
        $descriptions = [
            'Introduction à PHP 8' => 'Découvrez le langage PHP 8 côté serveur : syntaxe, variables, conditions, fonctions et bonnes pratiques pour écrire du code maintenable.',
            'Maîtriser Laravel 12' => 'Apprenez à construire des applications web professionnelles avec Laravel 12 : routing, contrôleurs, Eloquent, migrations, Blade et bien plus.',
            'Design UX/UI : les fondamentaux' => 'Les bases du design d\'interface : couleurs et contraste, typographie, wireframes et tests utilisateurs pour créer des produits clairs et accessibles.',
            'Marketing Digital 101' => 'Les fondamentaux du marketing digital : définition des personas, analyse de marché, SEO et stratégie de contenu pour développer votre activité en ligne.',
            'Laravel pour débutants' => 'Construisez vos premières applications web avec Laravel : installation, routes, contrôleurs, Blade et Eloquent pas à pas.',
            'JavaScript moderne — ES6+' => 'Maîtrisez les bases de JavaScript moderne : variables, fonctions, objets, promesses et syntaxe ES6+ pour des interfaces dynamiques.',
            'HTML & CSS — Les fondamentaux' => 'Apprenez à structurer des pages avec HTML sémantique et à les styliser avec CSS : sélecteurs, flexbox et grid.',
            'MySQL pour débutants' => 'Modélisez et interrogez des bases de données relationnelles avec MySQL : tables, clés, jointures et requêtes SQL.',
            'React.js — De zéro à héros' => 'Créez des interfaces réactives avec React : composants, état, props, hooks et gestion des événements.',
            'Tailwind CSS — Design rapide' => 'Gagnez du temps en stylisant vos interfaces avec Tailwind CSS : classes utilitaires, responsive design et composants propres.',
        ];

        $description = $descriptions[$title] ?? 'Apprenez l\'essentiel de cette thématique avec des modules pratiques et progressifs.';

        $course = Course::firstOrCreate(
            ['title' => $title],
            [
                'instructor_id' => $instructor->id,
                'category_id' => $category->id,
                'status' => $status,
                'description' => $description,
            ]
        );

        $course->update([
            'instructor_id' => $instructor->id,
            'category_id' => $category->id,
            'description' => $description,
            'status' => $status,
            'published_at' => $status === 'published' ? $course->published_at ?? now() : null,
            'image' => match ($title) {
                'Laravel pour débutants' => 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?w=600&h=340&fit=crop&auto=format',
                'JavaScript moderne — ES6+' => 'https://images.unsplash.com/photo-1627398242454-45a1465c2479?w=600&h=340&fit=crop&auto=format',
                'HTML & CSS — Les fondamentaux' => 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=600&h=340&fit=crop&auto=format',
                'MySQL pour débutants' => 'https://images.unsplash.com/photo-1544383835-bda2bc66a55d?w=600&h=340&fit=crop&auto=format',
                'React.js — De zéro à héros' => 'https://images.unsplash.com/photo-1633356122544-f134324a6cee?w=600&h=340&fit=crop&auto=format',
                'Tailwind CSS — Design rapide' => 'https://images.unsplash.com/photo-1587440871875-191322ee64b0?w=600&h=340&fit=crop&auto=format',
                'Maîtriser Laravel 12' => 'https://images.unsplash.com/photo-1542831371-29b0f74f9713?w=600&h=340&fit=crop&auto=format',
                'Design UX/UI : les fondamentaux' => 'https://images.unsplash.com/photo-1561070791-2526d30994b5?w=600&h=340&fit=crop&auto=format',
                'Marketing Digital 101' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=600&h=340&fit=crop&auto=format',
                default => null,
            },
        ]);

        $course->sections()->delete();

        $order = 1;
        foreach ($sections as $section) {
            $courseSection = Section::create([
                'course_id' => $course->id,
                'title' => $section['title'],
                'order' => $order++,
            ]);

            $moduleOrder = 1;
            foreach ($section['modules'] as $module) {
                Module::create([
                    'section_id' => $courseSection->id,
                    'title' => $module['title'],
                    'type' => $module['type'],
                    'content' => $module['content'],
                    'order' => $moduleOrder++,
                ]);
            }
        }

        return $course;
    }

    private function section(string $title, array $modules): array
    {
        return ['title' => $title, 'modules' => $modules];
    }

    private function module(string $title, string $type, string $content): array
    {
        return ['title' => $title, 'type' => $type, 'content' => $content];
    }

    private function attachQuiz(Course $course, string $title, int $passScore, int $maxAttempts, array $questions): Quiz
    {
        $section = $course->sections()->orderBy('order')->get()->last();
        $module = $section->modules()->orderBy('order')->get()->last();

        $quiz = Quiz::create([
            'module_id' => $module->id,
            'title' => $title,
            'pass_score' => $passScore,
            'max_attempts' => $maxAttempts,
        ]);

        foreach ($questions as [$text, $answers, $correctIndex]) {
            $question = Question::create(['quiz_id' => $quiz->id, 'text' => $text]);

            foreach ($answers as $index => $answerText) {
                Answer::create([
                    'question_id' => $question->id,
                    'text' => $answerText,
                    'is_correct' => $index === $correctIndex,
                ]);
            }
        }

        return $quiz;
    }

    private function enroll(User $user, Course $course): Enrollment
    {
        return Enrollment::firstOrCreate([
            'user_id' => $user->id,
            'course_id' => $course->id,
        ], ['enrolled_at' => now()]);
    }

    private function certificate(User $user, Course $course): Certificate
    {
        do {
            $code = strtoupper(Str::random(12));
        } while (Certificate::where('unique_code', $code)->exists());

        Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->whereNull('completed_at')
            ->update(['completed_at' => now()]);

        return Certificate::firstOrCreate(
            ['user_id' => $user->id, 'course_id' => $course->id],
            ['unique_code' => $code, 'issued_at' => now()]
        );
    }

    /**
     * Writes the demo PDF module files referenced by the seeded courses so the
     * learner module page links ("Ouvrir le PDF") never point to a 404.
     */
    private function makeDemoPdfs(): void
    {
        $disk = Storage::disk('public');
        $files = [
            'modules/guide-pratique-laravel.pdf' => "Guide pratique Laravel 12" . PHP_EOL . PHP_EOL .
                "Introduction aux routes, controllers, Blade et Eloquent." . PHP_EOL .
                "Ce document est fourni à titre de démonstration pour EduPath." . PHP_EOL,
            'modules/guides-tests-utilisateurs.pdf' => "Guide des tests utilisateurs" . PHP_EOL . PHP_EOL .
                "Cadrage, scénarios, recueil des retours et itérations." . PHP_EOL .
                "Document de démonstration EduPath." . PHP_EOL,
        ];

        foreach ($files as $path => $text) {
            if ($disk->exists($path)) {
                continue;
            }
            $disk->put($path, "%PDF-1.4" . PHP_EOL . "% EduPath" . PHP_EOL . "%E%E%E%E" . PHP_EOL .
                "1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj" . PHP_EOL .
                "2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj" . PHP_EOL .
                "3 0 obj<</Type/Page/MediaBox[0 0 612 792]/Parent 2 0 R/Contents 4 0 R/Resources<</Font<</F1 5 0 R>>>>>>endobj" . PHP_EOL .
                "4 0 obj<</Length " . strlen($text) . ">>stream" . PHP_EOL . $text . "endstreamendobj" . PHP_EOL .
                "5 0 obj<</Type/Font/Subtype/Type1/BaseFont/Helvetica>>endobj" . PHP_EOL .
                "trailer<</Root 1 0 R>>" . PHP_EOL . "%%EOF" . PHP_EOL);
        }
    }
}
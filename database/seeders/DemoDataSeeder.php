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
        // Full content + quiz, so the admin can publish it live during a demo.
        // ------------------------------------------------------------------
        $draft = $this->course($sophie, $web, 'Introduction à PHP 8', 'draft', [
            $this->section('Les bases', [
                $this->module('Qu\'est-ce que PHP ?', 'text', "PHP est un langage de script côté serveur spécialisé dans le développement web.\n\nExécuté sur le serveur, il génère des pages HTML dynamiques, lit et écrit en base de données, et gère sessions et utilisateurs.\n\nVérifiez vos scripts avec la commande :  php -l mon-script.php"),
                $this->module('Syntaxe et variables', 'text', "Les variables commencent par le symbole \$ et ne sont pas typées à la déclaration, mais le typage est fortement recommandé.\n\n  \$nom = 'Léa';   // string\n  \$age = 24;      // int\n  \$actif = true;  // bool"),
                $this->module('Les conditions', 'text', "if / else, switch et l'opérateur ternaire permettent de brancher la logique.\n\nPensez à toujours couvrir le cas par défaut.\n\n  if (\$age >= 18) { echo 'Majeur'; } else { echo 'Mineur'; }"),
                $this->module('Les boucles', 'text', "for, foreach, while et do…while parcourent les données.\n\n  foreach (\$users as \$user) { echo \$user['name']; }\n\nPréférez foreach pour parcourir les tableaux : plus lisible et plus sûr sur les index."),
            ]),
            $this->section('Aller plus loin', [
                $this->module('Les fonctions', 'video', 'https://www.youtube.com/embed/7_FOIxYLF-s'),
                $this->module('Les tableaux', 'text', "Les tableaux peuvent être indexés numériquement ou associatifs.\n\n  \$config = ['env' => 'local', 'debug' => true];\n\narray_map, array_filter et array_reduce rendent le traitement des collections plus expressif."),
                $this->module('Les formulaires', 'text', "Les pages HTML envoient des données via GET ou POST.\n\n  \$nom = \$_POST['nom'] ?? null;\n\nValidez et échappez toujours les entrées utilisateur avant de les afficher ou de les utiliser dans une requête."),
                $this->module('Gestion des erreurs', 'text', "try / catch permet de gérer proprement les erreurs sans arrêter brutalement le script.\n\n  try { \$pdo->query(\$sql); } catch (PDOException \$e) { error_log(\$e->getMessage()); }\n\nActivez la journalisation en production ; n'affichez jamais de stack trace publique."),
            ]),
            $this->section('Projet et validation', [
                $this->module('Mini projet : carnet d\'adresses', 'text', "Application demandée lors de la soutenance : construisez un carnet d'adresses simple.\n\nEntraînez-vous sur l'affichage, l'ajout, la recherche et la suppression de contacts à partir d'un tableau associatif et d'un formulaire.\n\nCe module est une mise en pratique à réaliser pour valider le niveau « bases »."),
                $this->module('Quiz — PHP 8', 'text', "Validez vos connaissances du langage PHP 8 avec le quiz ci-dessous.\n\nScore minimal : 60 %. Vous disposez de 3 tentatives."),
            ]),
        ]);

        $this->attachQuiz($draft, 'Quiz — PHP 8', 60, 3, [
            ['Quelle instruction affiche du texte à l\'écran ?', ['print_r', 'echo', 'console.log', 'write'], 1],
            ['Comment déclare-t-on une variable en PHP ?', ['static nom', 'let nom', '$nom', 'var nom'], 2],
            ['Quelle boucle est idéale pour parcourir un tableau associatif ?', ['foreach', 'switch', 'goto', 'for'], 0],
            ['Quelle superglobale contient les données d\'un formulaire envoyé en POST ?', ['$_GET', '$_COOKIE', '$_SERVER', '$_POST'], 3],
            ['Que permet l\'instruction if / else ?', ['répéter une action', 'brancher la logique', 'déclarer une classe', 'interrompre un script'], 1],
        ]);

        // ------------------------------------------------------------------
        // B) Admin-published course, rich content + quiz (scenario F shares this quiz).
        // ------------------------------------------------------------------
        $laravel = $this->course($karim, $web, 'Maîtriser Laravel 12', 'published', [
            $this->section('Démarrer', [
                $this->module('Installation', 'text', "Installation de Laravel via Composer :\n\n   composer create-project laravel/laravel mon-app\n\nPuis vérifiez que la commande artisan répond : php artisan --version."),
                $this->module('Architecture MVC', 'video', 'https://www.youtube.com/embed/MYyJ4PuL4pY'),
                $this->module('Premières routes', 'text', "Les routes vivent dans routes/web.php.\n\nRoute::get('/hello', fn () => 'Bonjour'); suffit pour une première réponse HTTP."),
            ]),
            $this->section('Modèles et base de données', [
                $this->module('Migrations', 'text', "Les migrations décrivent le schéma de la base, version par version.\n\n  php artisan make:migration create_articles_table\n  php artisan migrate\n\nToute évolution passe par une nouvelle migration, jamais par l'édition d'une migration déjà appliquée."),
                $this->module('Eloquent', 'text', "Eloquent manipule les données grâce à des modèles.\n\n  php artisan make:model Article -m\n\n  \$articles = Article::where('published', true)->get();\n\nUtilisez les relations Eloquent existantes plutôt que des requêtes brutes dupliquées."),
                $this->module('Relations', 'video', 'https://www.youtube.com/embed/hJSzLAa34rM'),
                $this->module('Blade', 'text', "Blade est le moteur de templates de Laravel : @if, @foreach et les layouts organisent les vues.\n\n  @foreach (\$articles as \$article) <x-card :article=\"\$article\" /> @endforeach"),
            ]),
            $this->section('Sécurité et pratique', [
                $this->module('Middleware et authentification', 'text', "Les middlewares filtrent les requêtes avant le contrôleur.\n\n  Route::middleware(['auth', 'verified'])->group(...)\n\nBreeze fournit déjà inscription, connexion et vérification d'email."),
                $this->module('Policies et autorisation', 'text', "Les policies centralisent l'autorisation :\n\n  \$this->authorize('update', \$course);\n\nExemple EduPath : seul l'admin publie ou dépublie un cours (CoursePolicy::publish)."),
                $this->module('Guide pratique', 'pdf', 'modules/laravel-guide-pratique.pdf'),
                $this->module('Quiz de validation', 'text', "Validez vos connaissances avec le quiz ci-dessous avant de passer à la suite.\n\nScore minimal : 60 %. Vous disposez de 3 tentatives."),
            ]),
        ]);

        $this->attachQuiz($laravel, 'Quiz — Laravel 12', 60, 3, [
            ['Quel fichier définit les routes web ?', ['routes/web.php', 'routes/api.php', 'app/Http/web.php', 'config/web.php'], 0],
            ['Quelle commande applique les migrations ?', ['php artisan schema', 'php artisan migrate', 'composer migrate', 'npm run migrate'], 1],
            ['Quelle classe permet d\'interagir avec une table ?', ['Blade Component', 'Eloquent Model', 'Middleware', 'Service Provider'], 1],
            ['Quel langage alimente les vues Blade ?', ['JavaScript', 'Python', 'PHP', 'Ruby'], 2],
            ['Quel est l\'utilitaire officiel de gestion de dépendances ?', ['Composer', 'npm', 'Yarn', 'Pip'], 0],
        ]);

        // ------------------------------------------------------------------
        // C) Enrolled learner (no progress yet) on the published Laravel course.
        // ------------------------------------------------------------------
        $this->enroll($lea, $laravel);

        // Léa completes the first two sections (~64%) so her dashboard shows a
        // meaningful "1/3–2/3" progress bar on the flagship course.
        foreach ($laravel->sections()->orderBy('order')->get()->slice(0, 2) as $section) {
            foreach ($section->modules()->orderBy('order')->get() as $module) {
                Progress::firstOrCreate([
                    'user_id' => $lea->id,
                    'module_id' => $module->id,
                ], ['completed_at' => now()]);
            }
        }

        // ------------------------------------------------------------------
        // D) Partial progression (~50%) on the Design course.
        // ------------------------------------------------------------------
        $designCourse = $this->course($sophie, $design, 'Design UX/UI : les fondamentaux', 'published', [
            $this->section('Concevoir', [
                $this->module('UX vs UI', 'text', "L'UX (expérience utilisateur) recouvre le parcours dans son ensemble ; l'UI (interface) se concentre sur l'aspect visuel.\n\nUn bon produit combine les deux : une interface claire au service d'un parcours fluide."),
                $this->module('Recherche utilisateur', 'text', "La recherche utilisateur repose sur des entretiens, des questionnaires et des tests.\n\nObjectif : comprendre les besoins réels avant de concevoir. Mieux vaut un prototype testé tôt qu'une maquette parfaite jamais validée."),
                $this->module('Personas', 'text', "Un persona est une représentation semi-fictive de l'utilisateur type, fondée sur des données réelles.\n\nDonnez-lui un nom, un contexte, des objectifs et des points de friction pour guider vos choix de design."),
                $this->module('Parcours utilisateur', 'text', "Le user flow décrit les étapes suivies par l'utilisateur pour atteindre son objectif.\n\nIdentifiez les points d'abandon et simplifiez : chaque écran supplémentaire coûte de l'attention."),
                $this->module('Wireframes', 'video', 'https://www.youtube.com/embed/qpH7-KFWZRI'),
            ]),
            $this->section('Prototyper', [
                $this->module('Hiérarchie visuelle', 'text', "La hiérarchie visuelle guide le regard : titres, contrastes et espacements signalent l'importance de chaque élément.\n\nDéfinissez un élément principal par écran."),
                $this->module('Typographie', 'text', "Une hiérarchie claire (titres, sous-titres, corps) rend la lecture confortable.\n\nLimitez-vous à deux familles typographiques et visez au moins 16 px pour le texte courant."),
                $this->module('Couleurs et contraste', 'text', "La couleur guide le regard. Respectez un contraste AA minimum entre le texte et le fond pour rester accessible.\n\nLimitez la palette à 2 ou 3 couleurs structurantes plus les niveaux de gris."),
                $this->module('Responsive design', 'text', "Un design responsive s'adapte à toutes les tailles d'écran.\n\nPensez mobile-first : concevez d'abord pour les contraintes les plus fortes, puis enrichissez pour tablette et desktop."),
                $this->module('Guide des tests utilisateurs', 'pdf', 'modules/guides-tests-utilisateurs.pdf'),
            ]),
        ]);

        $this->enroll($hugo, $designCourse);

        $firstSection = $designCourse->sections()->orderBy('order')->get()->first();
        foreach ($firstSection->modules()->orderBy('order')->get() as $module) {
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
                $this->module('Introduction au marketing digital', 'text', "Le marketing digital regroupe toutes les actions visant à promouvoir un produit ou un service en ligne.\n\nIl s'articule autour de quatre axes : attraction, acquisition, conversion et fidélisation."),
                $this->module('Définir son audience', 'text', "Une stratégie efficace commence par une audience bien définie.\n\nSegmentez par données démographiques, comportement et intentions pour adapter le message et le canal."),
                $this->module('Analyser le marché', 'video', 'https://www.youtube.com/embed/kFM72UJhW8s'),
                $this->module('Personas et positionnement', 'text', "Un persona est une représentation du client idéal, fondée sur des données réelles.\n\nLe positionnement définit la promesse et l'angle qui vous distinguent de la concurrence sur ce segment."),
            ]),
            $this->section('Acquisition', [
                $this->module('SEO et contenu', 'text', "Le référencement naturel repose sur du contenu utile, une technique saine et des backlinks qualitatifs.\n\nVisez l'intention de recherche, pas seulement le volume de mots-clés."),
                $this->module('Réseaux sociaux', 'text', "Choisissez les plateformes où votre audience est réellement active.\n\nLa régularité et le ton valent mieux que la fréquence : contenu utile + format natif."),
                $this->module('Email marketing', 'text', "L'email reste un canal à fort retour sur investissement.\n\nSegmentez vos listes, personnalisez les messages et mesurez taux d'ouverture et taux de clic."),
            ]),
            $this->section('Mesure et validation', [
                $this->module('Analytics et KPIs', 'text', "Les KPIs traduisent vos objectifs en chiffres mesurables : taux de conversion, coût d'acquisition, taux de rebond, revenu par visiteur.\n\nDécidez toujours à partir des données."),
                $this->module('Planification de campagne', 'text', "Une campagne se planifie : objectif, budget, canaux, calendrier, tracking et revue.\n\nTestez de petites variations (A/B) puis déployez ce qui fonctionne."),
                $this->module('Quiz — Marketing Digital', 'text', "Dernière étape : passez le quiz pour valider le module.\n\nScore minimal : 60 %. Vous disposez de 3 tentatives."),
            ]),
        ]);

        $this->attachQuiz($marketingCourse, 'Quiz — Marketing Digital', 60, 3, [
            ['Qu\'est-ce qu\'un persona ?', ['Une représentation du client idéal', 'Un logo', 'Un fichier marketing', 'Une campagne payante'], 0],
            ['Que signifie SEO ?', ['Social Engine Online', 'Search Engine Optimization', 'Site Enhancement Order', 'Simple Email Output'], 1],
            ['Que mesure le taux de clic (CTR) ?', ['Le nombre de ventes', 'Le coût d\'un abonnement', 'Les clics rapportés aux impressions', 'Le temps passé sur le site'], 2],
            ['D\'où provient principalement le trafic organique ?', ['Des publicités', 'Des newsletters', 'Des réseaux sociaux payants', 'Des moteurs de recherche'], 3],
            ['Qu\'est-ce qu\'un KPI ?', ['Un mot-clé payant', 'Un indicateur clé de performance', 'Un canal d\'acquisition', 'Un format publicitaire'], 1],
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
                $this->module('Installer Laravel', 'text', "Installation via Composer :\n\n   composer create-project laravel/laravel mon-app\n\nPuis lancez le serveur intégré : php artisan serve."),
                $this->module('Structure du projet', 'text', "Un projet Laravel est organisé : routes dans routes/, contrôleurs dans app/Http/Controllers, vues dans resources/views, migrations dans database/migrations.\n\nRespectez cette structure : elle rend le projet lisible et maintenable."),
                $this->module('Premier contrôleur', 'video', 'https://www.youtube.com/embed/HNTsM2ZmoFQ'),
            ]),
            $this->section('Premières briques', [
                $this->module('Routes et vues', 'text', "Route::get('/produits', fn () => view('articles.index'));\n\nNommez vos routes (->name('articles.index')) : le helper route() reste alors stable même si l'URL change."),
                $this->module('Blade et layouts', 'text', "Blade permet de composer des vues avec @extends, @section et @foreach.\n\nExtrayez le code répété (navigation, pied de page) dans des layouts et des composants réutilisables."),
                $this->module('Migrations', 'text', "Les migrations versionnent le schéma SQL.\n\n  php artisan make:migration create_articles_table\n  php artisan migrate\n\nN'éditez jamais une migration déjà appliquée."),
            ]),
        ]);

        $this->course($karim, $web, 'JavaScript moderne — ES6+', 'published', [
            $this->section('Les bases', [
                $this->module('Variables et types', 'text', "let et const remplacent var : const pour les références stables, let pour les valeurs mutables.\n\nLes templates literals facilitent l'interpolation : `Bonjour \${nom}`."),
                $this->module('Fonctions et flèches', 'text', "Les fonctions fléchées offrent une syntaxe concise et ne créent pas leur propre this.\n\n  const double = (n) => n * 2;\n\nTrès utiles en callback : map, filter, reduce."),
                $this->module('Tableaux et méthodes', 'text', "map transforme, filter sélectionne, reduce agrège, find localise.\n\n  const adults = users.filter(u => u.age >= 18);\n\nCes méthodes remplacent la plupart des boucles manuelles."),
            ]),
            $this->section('Approfondir', [
                $this->module('Objets et destructuring', 'text', "Le destructuring extrait proprement des valeurs :\n\n  const { name, email } = user;\n\nLe spread ... copie et fusionne objets et tableaux de façon non destructive."),
                $this->module('Promesses et async/await', 'text', "Les promesses gèrent les opérations asynchrones.\n\n  const data = await fetch('/api/users').then(r => r.json());\n\nasync/await rend le code séquentiel et lisible, avec try/catch pour les erreurs."),
                $this->module('Manipulation du DOM', 'text', "querySelector et addEventListener relient le JavaScript à la page.\n\n  btn.addEventListener('click', () => form.classList.toggle('hidden'));\n\nPensez accessibilité : attributs aria et navigation clavier."),
            ]),
        ]);

        $this->course($sophie, $web, 'HTML & CSS — Les fondamentaux', 'published', [
            $this->section('Structurer', [
                $this->module('Balises sémantiques', 'text', "header, nav, main, section, article et footer décrivent la structure d'une page.\n\nLe HTML sémantique améliore l'accessibilité et le référencement, et rend le code plus lisible."),
                $this->module('Sélecteurs CSS', 'text', "Les sélecteurs ciblent les éléments : balise, classe (.card), id (#menu), attribut et pseudo-classe (:hover).\n\nConstruisez avec des classes simples et réutilisables."),
            ]),
            $this->section('Styliser', [
                $this->module('Mise en page CSS', 'video', 'https://www.youtube.com/embed/-Wlt8NRtOpo'),
                $this->module('Flexbox', 'text', "Flexbox aligne une rangée (ou une colonne) d'éléments.\n\ndisplay: flex, justify-content, align-items et gap suffisent pour la plupart des barres de navigation et des cartes."),
            ]),
            $this->section('Mettre en page', [
                $this->module('Grid', 'text', "Grid gère la mise en page sur deux dimensions, lignes et colonnes.\n\n  grid-template-columns: repeat(3, 1fr);\n\nIdéal pour les galeries, tableaux de bord et layouts complets."),
                $this->module('Responsive design', 'text', "Utilisez des unités fluides (rem, %, fr), des media queries et des images flexibles.\n\n  @media (max-width: 640px) { .grid { grid-template-columns: 1fr; } }\n\nTestez sur plusieurs tailles d'écran."),
            ]),
        ]);

        $this->course($sophie, $data, 'MySQL pour débutants', 'published', [
            $this->section('Modéliser', [
                $this->module('Tables et clés', 'text', "Une table représente une entité ; la clé primaire identifie chaque ligne, la clé étrangère relie les tables.\n\nModélisez (MCD) avant de créer les tables afin d'éviter les redondances."),
                $this->module('Types de données', 'text', "INT, DECIMAL, VARCHAR, TEXT, DATE, DATETIME, BOOLEAN…\n\nChoisissez le type adapté : VARCHAR pour du texte court, TEXT pour de longs contenus, ENUM pour une liste restreinte."),
                $this->module('Les jointures', 'text', "Les jointures combinent des tables :\n\n  SELECT u.name, c.title FROM users u JOIN enrollments e ON e.user_id = u.id JOIN courses c ON c.id = e.course_id;\n\nINNER JOIN pour les liens garantis, LEFT JOIN pour conserver tous les enregistrements de la table de gauche."),
            ]),
            $this->section('Interroger', [
                $this->module('Requêtes SQL', 'text', "SELECT, INSERT, UPDATE et DELETE couvrent la majorité des besoins.\n\nFiltrez avec WHERE, ordonnez avec ORDER BY, limitez avec LIMIT pour des requêtes prévisibles."),
                $this->module('Agrégations', 'text', "COUNT, SUM, AVG, MIN et MAX synthétisent les données ; GROUP BY regroupe par valeur.\n\n  SELECT status, COUNT(*) FROM enrollments GROUP BY status;\n\nHAVING filtre les groupes, là où WHERE filtre les lignes."),
                $this->module('Index et performance', 'text', "Les index accélèrent les recherches mais ralentissent les écritures.\n\nIndexez les colonnes filtrées ou jointes (clés étrangères) et utilisez EXPLAIN pour comprendre le plan d'exécution."),
            ]),
        ]);

        $this->course($karim, $web, 'React.js — De zéro à héros', 'published', [
            $this->section('Composants', [
                $this->module('Créer un composant', 'text', "Un composant est une fonction qui retourne du JSX ; React compose l'interface en petites briques réutilisables.\n\n  function Card({ title }) { return <article><h2>{title}</h2></article>; }\n\nNommez vos composants en PascalCase."),
                $this->module('Props et JSX', 'text', "Les props transmettent des données du parent à l'enfant ; elles sont en lecture seule.\n\nLe JSX mélange HTML et expressions : des accolades injectent des valeurs JavaScript."),
                $this->module('État et effets', 'video', 'https://www.youtube.com/embed/O6P86uwfdR0'),
            ]),
            $this->section('Hooks et pratique', [
                $this->module('Gestion des formulaires', 'text', "Les formulaires « contrôlés » stockent la saisie dans l'état.\n\n  const [titre, setTitre] = useState('');\n\nSynchronisez value et onChange pour garder une source de vérité unique."),
                $this->module('Hooks personnalisés', 'text', "Un hook personnalisé encapsule une logique réutilisable :\n\n  const { data, loading } = useFetch('/api/courses');\n\nComposez les hooks natifs (useState, useEffect) pour construire vos propres abstractions."),
                $this->module('Mini projet : liste de tâches', 'text', "Projet pratique : une liste de tâches avec ajout, validation et suppression.\n\nTraduisez en React : état local, rendu conditionnel et propagation des événements. Idéal pour assimiler les concepts du cours."),
            ]),
        ]);

        $this->course($sophie, $web, 'Tailwind CSS — Design rapide', 'published', [
            $this->section('Les bases', [
                $this->module('Classes utilitaires', 'text', "Tailwind stylise directement dans le markup avec des classes utilitaires : p-4, text-lg, bg-slate-100, rounded-xl…\n\nDès qu'une utilité suffit, pas de CSS custom : lisibilité et cohérence garanties."),
                $this->module('Responsive et thème', 'text', "Les variantes responsive (sm:, lg:) et dark: adaptent la mise en page sans media query séparée.\n\n  class=\"grid gap-4 sm:grid-cols-2 lg:grid-cols-3\"\n\nConfigurez la palette dans tailwind.config.js."),
            ]),
            $this->section('Aller plus loin', [
                $this->module('Composants réutilisables', 'text', "Extrayez le markup répété dans des composants réutilisables qui centralisent les classes Tailwind.\n\nDans EduPath, les composants du design system vivent dans resources/views/components."),
                $this->module('Dark mode', 'text', "Pour activer le dark mode, définissez darkMode: 'class' dans la config puis basculez la classe dark sur l'élément racine.\n\nVérifiez le contraste dans les deux thèmes : l'accessibilité ne se négocie pas."),
                $this->module('Intégration à un projet', 'text', "Tailwind s'intègre au pipeline Vite : directives @tailwind base/components/utilities dans resources/css/app.css puis build avec npm run build.\n\nLa configuration v3 (tailwind.config.js + postcss.config.js) est celle utilisée par EduPath."),
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
                'Introduction à PHP 8' => 'https://images.unsplash.com/photo-1587620962725-abab7fe55159?w=600&h=340&fit=crop&auto=format',
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
            'modules/laravel-guide-pratique.pdf' => "Guide pratique Laravel 12" . PHP_EOL . PHP_EOL .
                "Introduction aux routes, controllers, Blade et Eloquent." . PHP_EOL .
                "Ce document est fourni à titre de démonstration pour EduPath." . PHP_EOL,
            'modules/guides-tests-utilisateurs.pdf' => "Guide des tests utilisateurs" . PHP_EOL . PHP_EOL .
                "Cadrage, scénarios, recueil des retours et itérations." . PHP_EOL .
                "Document de démonstration EduPath." . PHP_EOL,
        ];

        if ($disk->exists('modules/guide-pratique-laravel.pdf')) {
            $disk->delete('modules/guide-pratique-laravel.pdf');
        }

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
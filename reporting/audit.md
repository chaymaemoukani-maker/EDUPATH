# Rapport d'audit — Phase 2 : conformité & qualité de démonstration

> Projet **EduPath** — audit mené le 17/09/2026 sur le dépôt local, avant soutenance (21/09/2026).
> Contexte : Mission 1 (déploiement Railway Docker + CI) **terminée et non modifiée**. Ce rapport couvre uniquement la Mission 2 (audit de conformité, données de démonstration, qualité).

## A — Verdict par domaine

| Domaine audité | Verdict | Commentaire |
|---|---|---|
| Conformité `specs/` + `AGENTS.md` | **PASS** | Aucun fichier hors seeder modifié ; `specs/design.md` intact. |
| Données de démonstration complètes | **PASS** | 10 cours peuplés (sections → modules texte/vidéo/PDF + quiz), 3 rôles, progression réaliste, certificat. |
| Vidéos 100 % éducatives | **PASS** | Zéro vidéo musique/divertissement ; 8/8 vérifiées (titre + auteur correspondent au thème du module). |
| Fichiers PDF référencés | **PASS** | Les 2 liens « Ouvrir le PDF » pointent vers des fichiers réellement écrits sur disque. |
| Schéma de données final | **PASS** | Aucun type de module `quiz`, aucun `certificates.score`, aucun `users.role` ; `courses.image` bien optionnelle. |
| Autorisation | **PASS** | Laratrust seul source de vérité ; publication **admin-only** (`CoursePolicy::publish`) ; les Policies existantes couvrent Course/Section/Module/Quiz/Category/User/Certificate. |
| Sécurité | **PASS** | CSRF, FormRequests, Policies, rate limiting (connexion 5/min, inscription 10/min, `/verify` 15/min), pas de secrets exposés. |
| Qualité de code | **PASS** | `php -l` OK sur les fichiers modifiés, pas d'interpolation de variables parasites dans les contenus textuels, Pint déjà conforme (CI). |

## B — Changements apportés

Tous les changements sont **purement des données de démonstration**, dans `database/seeders/DemoDataSeeder.php` (+ nettoyage d'un fichier obsolète). Aucun code applicatif, route, modèle, migration, vue ni politique n'a été touché ; la Mission 1 (Docker Railway) est intacte.

1. **Suppression des 8 vidéos « rickroll »** (`dQw4w9WgXcQ`) → remplacées par des vidéos éducatives vérifiées qui **correspondent au thème exact de chaque module** (voir section D).
2. **Correction d'un lien PDF cassé** : le module « Guide pratique » référençait `modules/guide-pratique-laravel.pdf` alors que le fichier écrit s'appelait `modules/laravel-guide-pratique.pdf` → le module référence désormais le fichier réel ; l'ancien fichier obsolète est supprimé s'il existe.
3. **Positions de réponse correcte variées** dans les 3 quiz (avant : toujours index 0 sur les 2 quiz ; après : répartition couvrant les index 0 à 3).
4. **Quiz Marketing élargi** de 2 à 5 questions (5 questions de 4 réponses chacune, conformes à `SecurityHardeningTest`).
5. **Contenu complet** : 24 sections et 76 modules répartis sur 10 cours (avant : ~2 modules par cours), avec 3 quiz.
6. **Progression réaliste** : Léa 64 % (voie 7/11), Hugo 50 % (voie 5/10), Inès 100 % (voie 10/10 + certificat + quiz réussi).
7. **Échappement `\$`** dans les modules texte contenant des extraits de code (`$nom`, `$this->authorize(...)`, `${nom}`…) pour éviter toute interpolation PHP parasite dans les chaînes double-quote — vérifié par analyse tokenizer (aucune interpolation résiduelle).

## C — Comptages (lancés sur base fraîche `migrate:fresh --seed`)

| Entité | Nombre |
|---|---|
| Catégories | 4 |
| Cours (dont **draft** / **published**) | 10 (1 / 9) |
| Sections | 24 |
| Modules | 76 = **66 text / 8 video / 2 pdf / 0 type-quiz** |
| Quiz | 3 |
| Questions | 15 |
| Réponses | 60 |
| Inscriptions (enrollments) | 3 |
| Progressions (modules terminés) | 22 |
| Tentatives de quiz | 1 (réussie) |
| Certificats | 1 |
| Comptes | 1 admin · 3 formateurs · 4 apprenants |

Détail par cours :

| Cours | Statut | Sec | Mod | Vidéo | PDF | Quiz (questions) |
|---|---|---|---|---|---|---|
| Introduction à PHP 8 | draft | 3 | 10 | 1 | 0 | oui (5) |
| Maîtriser Laravel 12 | published | 3 | 11 | 2 | 1 | oui (5) |
| Design UX/UI : les fondamentaux | published | 2 | 10 | 1 | 1 | non |
| Marketing Digital 101 | published | 3 | 10 | 1 | 0 | oui (5) |
| Laravel pour débutants | published | 2 | 6 | 1 | 0 | non |
| JavaScript moderne — ES6+ | published | 2 | 6 | 0 | 0 | non |
| HTML & CSS — Les fondamentaux | published | 3 | 6 | 1 | 0 | non |
| MySQL pour débutants | published | 2 | 6 | 0 | 0 | non |
| React.js — De zéro à héros | published | 2 | 6 | 1 | 0 | non |
| Tailwind CSS — Design rapide | published | 2 | 5 | 0 | 0 | non |

## D — Vérification des vidéos

Chaque URL a été validée via l'oEmbed API YouTube (vidéo trouvée, embeddable, titre + auteur éducatifs et cohérents avec le module). Aucune vidéo de musique / divertissement / meme.

| Module | URL (ID) | Contenu réel vérifié |
|---|---|---|
| PHP — Les fonctions | `youtube.com/embed/7_FOIxYLF-s` | « PHP 8 Functions » (Program With Gio) |
| Laravel — Architecture MVC | `youtube.com/embed/MYyJ4PuL4pY` | « Laravel From Scratch » (Traversy Media) |
| Laravel — Relations | `youtube.com/embed/hJSzLAa34rM` | « Laravel 12 Eloquent Relationships » (Code With ERaufi) |
| Design — Wireframes | `youtube.com/embed/qpH7-KFWZRI` | « UX Wireframe Tutorial » (CareerFoundry) |
| Marketing — Analyser le marché | `youtube.com/embed/kFM72UJhW8s` | « Market Research 101 » (Adam Erhart) |
| Laravel débutants — Premier contrôleur | `youtube.com/embed/HNTsM2ZmoFQ` | « Laravel Controllers » (Net Ninja) |
| HTML & CSS — Mise en page CSS | `youtube.com/embed/-Wlt8NRtOpo` | « CSS Flexbox Course » (freeCodeCamp) |
| React — État et effets | `youtube.com/embed/O6P86uwfdR0` | « useState useEffect » (Web Dev Simplified) |

> La seule autre occurrence connue de `dQw4w9WgXcQ` dans le dépôt est dans un test (`InstructorSpaceTest.php:132`), où elle est générée par une factory de test (`fake()`), pas par les données de démonstration — hors périmètre du seeder.

## E — Tests

- `php artisan test` : **129 tests, 129 passés, 383 assertions** (durée ~60 s).
- `SecurityHardeningTest` (le plus contraignant sur le seeder) : **vérifié et satisfait** — ≥ 1 cours draft, ≥ 3 cours publiés, ≥ 3 inscriptions, ≥ 2 progressions, exactement 1 certificat au code `^[A-Z0-9]{12}$`, quiz à ≥ 4 réponses/question, tentative de quiz réussie, ≥ 3 formateurs, ≥ 3 apprenants, zéro module de type `quiz`, pas de clé `score` sur le certificat.
- `php artisan route:list` : **68 routes**, groupées par espace (admin/*, instructor/*, learner/*) avec middlewares dédiés.
- `php artisan migrate:status` : **17/17 migrations exécutées** (frais = champ `users.role` absent, `courses.image` présente).
- Fiabilité pipeline : le seeder s'exécute sans erreur sur base fraîche (SQLite locale et CI SQLite + MySQL).

## Conclusion

Mission 2 **conforme et complète**. L'application est prête pour la démonstration de soutenance : données de démonstration riches et réalistes, zéro contenu non conforme (vidéos, PDF, schéma, autorisations), suite complète de tests au vert, et déploiement Docker Railway inchangé. Reste optionnel (hors périmètre de cette mission) : la mise en production cloud définitive, déjà tracée dans le backlog (`EDU-123`).
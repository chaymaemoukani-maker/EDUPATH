# EduPath

Plateforme e-learning : un formateur crée des cours structurés en **sections → modules** (texte / vidéo / PDF), agrémentés d'un **quiz** optionnel par module. Un apprenant s'inscrit gratuitement, progresse module par module, passe les quiz et obtient un **certificat PDF vérifiable publiquement** à 100 % de progression. Un administrateur attribue les rôles, gère les catégories et **publie/dépublie** les cours.

## Stack technique

| Composant | Version |
|---|---|
| Backend | Laravel 13, PHP ^8.3 |
| Frontend | Blade + Tailwind CSS v3 + Alpine.js (pas de framework JS) |
| Base de données | MySQL 8.0 |
| Rôles / permissions | Laratrust ^8.5 (source de vérité unique : `admin` / `instructor` / `learner`) |
| PDF | barryvdh/laravel-dompdf |
| Tests | Pest ^4 |
| Conteneurisation | Docker Compose (app + nginx + mysql) |

## Prérequis

- PHP ^8.3 avec les extensions `pdo_mysql`, `mbstring`, `exif`, `bcmath`, `gd`, `zip`, `intl`
- Composer 2
- Node.js 20.19+ / 22+ et npm
- Docker Desktop (recommandé) **ou** un serveur MySQL 8.0 local

## Installation avec Docker (recommandé)

```bash
composer install
npm install
npm run build

cp .env.example .env
php artisan key:generate

docker compose up -d --build

# Les commandes artisan qui touchent la base s'exécutent DANS le conteneur `app`
# (le réseau Docker ne connaît le hostname `db` que depuis l'intérieur du réseau).
docker compose exec app php artisan storage:link
docker compose exec app php artisan migrate:fresh --seed
```

L'application est alors disponible sur http://localhost:8000. Les conteneurs `web` (nginx) et `app` (php-fpm) montent le dossier du projet (`./:/var/www/html`) ; la base MySQL est exposée sur le port hôte 3306 avec les identifiants de `.env` (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, root par défaut `root_secret`).

> Testée en SQLite en mémoire (`php artisan test`) et exécutée avec MySQL via Docker (`.env.example` : `DB_HOST=db`). Les commandes artisan `php artisan migrate:...` / `db:seed` s'exécutent donc **dans** le conteneur (`docker compose exec app php artisan ...`). Pour un développement sans Docker, mettez `DB_HOST=127.0.0.1` dans `.env` et utilisez un serveur MySQL local.

## Installation locale (sans Docker)

```bash
composer install
cp .env.example .env
php artisan key:generate
# configurez .env : DB_HOST=127.0.0.1, DB_DATABASE, DB_USERNAME, DB_PASSWORD (MySQL 8)
npm install
npm run build
php artisan migrate --seed
php artisan serve
```

## Comptes de démonstration

`php artisan db:seed --class=DemoDataSeeder` (inclus dans `migrate:fresh --seed`) crée des données de démonstration. Tous les comptes utilisent le mot de passe **`password`** (⚠️ à changer en production) :

| Rôle | Email |
|---|---|
| Admin | `admin@example.com` |
| Formateur | `instructor@example.com`, `sophie.martin@example.com`, `karim.benali@example.com` |
| Apprenant | `learner@example.com`, `lea.dubois@example.com`, `hugo.lefevre@example.com`, `ines.moreau@example.com` |

Les données seedées couvrent tous les états métier utiles à la démo : cours en brouillon, cours publiés, inscriptions, progression à 50 %, progression à 100 % avec certificat, et un quiz complet avec une tentative réussie.

## Commandes utiles

```bash
# Suite de tests (Pest, SQLite en mémoire)
php artisan test

# Lint PHP (Pint)
vendor/bin/pint --test

# Build des assets frontend
npm run build       # production
npm run dev         # développement (Vite)

# Contrôles
php artisan route:list
php artisan view:cache
php artisan db:show
```

## CI / GitHub Actions

Un pipeline `.github/workflows/tests.yml` s'exécute sur chaque push / pull request vers `main`/`master` (déclenchement manuel possible via `workflow_dispatch`) :

1. **Tests** — Pest (PHP 8.3, Composer, Node 22, `npm ci` + `npm run build`) en matrice **SQLite + MySQL** (service MySQL 8.0 jetable), plus le gate de formatage **Pint** (`vendor/bin/pint --test`).
2. **Docker** — build des images `app` (Laravel autonome, sans bind mounts) puis `web` (nginx).
3. **Publication** — sur chaque push vers `main`, les images sont taguées `<sha>` + `latest` et poussées sur **GHCR** : `ghcr.io/<compte>/edupath` et `ghcr.io/<compte>/edupath-web`.

La config de style (exclusions des fichiers métier hérités) est centralisée dans `pint.json`.

## Sécurité

- Roles et permissions via Laratrust : aucun sélecteur de rôle en self-service ; le rôle est toujours résolu côté serveur.
- Publication d'un cours **réservée à l'administrateur** (Policies Laravel).
- Protection CSRF sur tous les formulaires, validation via FormRequest, uploads PDF validés (`mimes:pdf`, taille limitée).
- Rate limiting : inscription et vérification de certificat throttlées, connexion limitée.

## Documentation du projet

Toutes les décisions, le cahier des charges, la structure de la base de données, le design system et le backlog sont documentés dans `specs/` et `AGENTS.md` :

- `specs/cahier-des-charges.md` — exigences fonctionnelles
- `specs/database.md` — schéma et migrations
- `specs/design.md` — design system et pages
- `specs/fonctionnalites.md` — fonctionnalités par rôle
- `specs/backlog.md` — backlog EPIC → Feature → Task

## Licence

Projet pédagogique — Établissement de formation, 2026.
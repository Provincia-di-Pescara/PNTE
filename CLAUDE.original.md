# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**GTE-Abruzzo** (Gestionale Trasporti Eccezionali) is a multi-tenant SaaS platform developed by Provincia di Pescara to digitize the full lifecycle of exceptional vehicle transport authorizations in the Abruzzo region, intended for reuse across Italian public administration via Developers Italia.

The application must comply with: Art. 10 D.Lgs 285/1992 (Codice della Strada), D.P.R. 495/1992 wear formulas, AgID guidelines, and MIT bridge-safety directives.

## Development Commands

```bash
# Start all services concurrently (PHP server, queue, log viewer, Vite HMR)
composer run dev

# Or individually:
php artisan serve                    # Laravel dev server
npm run dev                          # Vite frontend with HMR
npm run build                        # Production frontend build
php artisan queue:listen --tries=1   # Process async jobs
php artisan pail                     # Real-time log viewer

# Database
php artisan migrate
php artisan migrate --seed

# Code style — run before finalizing any change
./vendor/bin/pint --dirty

# Testing
php artisan test --compact                              # All tests
php artisan test --compact tests/Feature/SomeTest.php  # Single file
php artisan test --compact --filter=test_name          # Single test
php artisan test --compact --testsuite=Unit
php artisan test --compact --testsuite=Feature
```

Every code change must be covered by a test (new or updated). Run only the minimum tests needed to verify the change.

## Production Deployment (Docker)

```bash
docker-compose up -d --build
docker-compose exec app composer install
docker-compose exec app php artisan migrate --seed
```

The production stack runs: `app` (Laravel/PHP-FPM + Nginx + Chromium), `db` (MariaDB 11.4 LTS with spatial extensions), `redis:7`, `osrm` (self-hosted routing engine, `--profile gis`).

## Architecture

### Tech Stack
- **Backend**: Laravel 13, PHP 8.4, Eloquent ORM
- **Frontend**: Blade templates + Tailwind CSS v4 (zero-runtime) + Alpine.js, bundled via Vite 6
- **Database**: MariaDB 11.4 LTS with native GIS/spatial index support (required for route geometry)
- **Queue/Cache**: Redis (async jobs for PEC email, PDF generation, payment webhooks)
- **GIS Routing**: Self-hosted OSRM for snap-to-road route calculation
- **PDF Generation**: Browsershot (Headless Chrome)
- **Auth**: SPID/CIE via `laravel/socialite` + `socialiteproviders/manager`
- **Payments**: PagoPA integration (planned)

### Core Domain Model

The central entity is the **application** (transport authorization request), which moves through a rigid state machine:

```
draft → submitted → waiting_clearances → waiting_payment → approved
```

Key Eloquent models (planned, not yet implemented):
- `users` — natural persons with fiscal identity (SPID/CIE data)
- `companies` — companies/agencies with delegations via `company_user` pivot
- `vehicles` — tractor units and trailers with axle/weight configurations (`vehicle_axles`)
- `entities` — municipalities, provinces, ANAS, motorways with GIS polygons and PEC addresses
- `applications` — the transport authorization request and its state
- `routes` — LineString geometry of the authorized route with per-entity km breakdown
- `clearances` — third-party approvals (Nulla Osta) per entity per application
- `tariffs` — historically-versioned wear coefficients used by `WearCalculationService`
- `roadworks` — construction sites reported by the owning entity: geometry (LINESTRING/POLYGON), `valid_from`/`valid_to`, severity (advisory/restricted/closed), status (planned/active/closed)

### RBAC Roles
- `super-admin` — Provincia di Pescara operators (full access)
- `operator` — other province operators
- `third-party` — municipalities, ANAS (limited to their clearance dashboard and roadworks management)
- `citizen` — transport companies/agencies submitting requests
- `law-enforcement` — Forze dell'Ordine (read-only access: approved transports, active roadworks, QR code verification)

### Planned Services
- **`WearCalculationService`** — computes road wear indemnity using per-axle weight × km × tariff coefficients (formula from D.P.R. 495/1992)
- **GIS spatial queries** — `ST_Intersection` + `ST_Length` against entity polygon table to extract which entities a route traverses and how many km per entity
- **AINOP integration** — via PDND API to verify bridge/infrastructure capacity along a route (field `codice_univoco_ainop` on infrastructure records)
- **PagoPA clearing** — single IUV generated from `WearCalculationService` output; RT webhook unlocks the application; proceeds split among entities

### Geographic/GIS Layer
MariaDB spatial fields (`POLYGON`, `MULTIPOLYGON`, `LINESTRING`) store entity boundaries and route geometries. Spatial indices are required. The OSRM container must be pre-loaded with the regional road graph. The frontend uses Leaflet for the interactive map.

### Architecture Docs
The `.ai/` directory (to be created) contains deep-dive documentation on complex subsystems: `STATE_MACHINE.md`, `GIS_ROUTING.md`, `WEAR_CALCULATION.md`, `PAGOPA.md`. Read these before working on the relevant domain.

## PHP & Laravel Conventions

### Strict Typing
Every PHP file must begin with `declare(strict_types=1);`. Always declare explicit return types on all methods and functions. Use PHP 8 type hints for all parameters.

```php
declare(strict_types=1);

// Correct
public function calculate(Vehicle $vehicle, int $km): Money { ... }

// Correct — constructor property promotion
public function __construct(
    private readonly TariffRepository $tariffs,
    private readonly RouteRepository $routes,
) {}
```

### Service Classes
Services in `App\Services\` must be `final`. Use constructor property promotion with `private readonly` for injected dependencies.

### Enums
All enums go in `App\Enums\`. Enum case names must be TitleCase (e.g., `WaitingClearances`, `Approved`). Use backed enums (`string` or `int`) for values stored in the database.

### Eloquent
- Always use eager loading to prevent N+1 queries.
- Prefer `Model::query()->...` over `DB::` raw queries.
- Define casts in the `casts()` method, not the `$casts` property.
- Always use Eloquent relationship methods with return type hints.

### Controllers & Validation
- Use `php artisan make:` commands with `--no-interaction` to generate all new files (models, migrations, controllers, jobs, etc.).
- Always create dedicated `App\Http\Requests\` Form Request classes for validation — never validate inline in controllers.
- Use named routes and the `route()` helper for all URL generation.

### Configuration
Never call `env()` outside of `config/` files. Always use `config('key')` in application code.

### .env — bootstrap and network topology only
The `.env` file must contain only the variables needed to bootstrap the application and define the network topology: `APP_KEY`, `APP_ENV`, `APP_URL`, DB credentials, Redis credentials, `OSRM_BASE_URL`, `CHROMIUM_PATH`. Nothing else belongs there.

Everything else must go to the **database** and be managed via the **admin UI**:
- Application behaviour: debug mode, timezone, locale/i18n, maintenance mode
- Mail/SMTP server and credentials
- Integration credentials: SPID SP metadata, PagoPA station IDs, PDND client keys, Firma Remota endpoints, etc.

`APP_VERSION*` are Docker build ARGs injected by CI/CD — they are not runtime env vars and must not appear in `.env`.

### Database — always MariaDB
MariaDB 11.4 LTS is the only supported database, including local development. There is no SQLite fallback. Local development must use `docker compose up` with the `db` service.

### Production deployment — Portainer, no shell access
Production runs on Portainer (Docker stack). There is no shell access to running containers. This has the following consequences:
- Migrations must run automatically on container startup via `entrypoint.sh` (`php artisan migrate --force`).
- Seeders for required bootstrap data (roles, permissions, default settings) must run via a dedicated idempotent seeder called by the entrypoint, not manually.
- One-off operations must be implemented as Artisan commands triggerable via a Portainer exec or a dedicated admin UI action — never assume shell availability.

### Middleware (Laravel 13)
Middleware is configured in `bootstrap/app.php` via `Application::configure()->withMiddleware()`, not in a `Kernel.php`. Laravel 13 retains the same streamlined structure introduced in Laravel 11.

### Comments & PHPDoc
Prefer PHPDoc blocks over inline comments. Add inline comments only when the logic would surprise a reader — never to describe what the code does. Use array shape annotations in PHPDoc when the structure is non-obvious.

### Code Style
- Always use curly braces for control structures, even for single-line bodies.
- Run `./vendor/bin/pint --dirty` before finalizing any set of changes.

## Project Structure Notes
- PSR-4: application code in `App\`, tests in `Tests\`
- Jobs (async): `App\Jobs\` — PEC notifications, PDF generation, payment webhooks
- `.env.example` covers only bootstrap/network vars; production uses MariaDB + Redis
- EUPL-1.2 license; `publiccode.yml` must be kept up to date with any new dependencies or deployment requirements

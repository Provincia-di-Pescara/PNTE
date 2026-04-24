# CLAUDE.md

Guidance for Claude Code when working this repo.

## Project Overview

**GTE-Abruzzo** (Gestionale Trasporti Eccezionali) — multi-tenant SaaS by Provincia di Pescara. Digitize full lifecycle of exceptional vehicle transport authorizations in Abruzzo. Intended for reuse across Italian public admin via Developers Italia.

Must comply: Art. 10 D.Lgs 285/1992 (Codice della Strada), D.P.R. 495/1992 wear formulas, AgID guidelines, MIT bridge-safety directives.

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

Every change need test (new or updated). Run minimum tests to verify change.

## Production Deployment (Docker)

```bash
docker-compose up -d --build
docker-compose exec app composer install
docker-compose exec app php artisan migrate --seed
```

Prod stack: `app` (Laravel/PHP-FPM + Nginx), `db` (MariaDB 10.11 with spatial extensions), `redis`, `osrm` (self-hosted routing engine), `chrome` (headless PDF rendering).

## Architecture

### Tech Stack
- **Backend**: Laravel 12, PHP 8.4, Eloquent ORM
- **Frontend**: Blade + Tailwind CSS v4 (zero-runtime) + Alpine.js, Vite 6
- **Database**: MariaDB 11.4 LTS, native GIS/spatial index (required for route geometry)
- **Queue/Cache**: Redis (async: PEC email, PDF generation, payment webhooks)
- **GIS Routing**: Self-hosted OSRM, snap-to-road
- **PDF Generation**: Browsershot (Headless Chrome)
- **Auth**: SPID/CIE via `laravel/socialite` + `socialiteproviders/manager`
- **Payments**: PagoPA (planned)

### Core Domain Model

Central entity: **application** (transport authorization request). Rigid state machine:

```
draft → submitted → waiting_clearances → waiting_payment → approved
```

Key Eloquent models (planned, not yet implemented):
- `users` — natural persons with fiscal identity (SPID/CIE data)
- `companies` — companies/agencies with delegations via `company_user` pivot
- `vehicles` — tractor units and trailers with axle/weight configs (`vehicle_axles`)
- `entities` — municipalities, provinces, ANAS, motorways with GIS polygons and PEC addresses
- `applications` — transport authorization request and its state
- `routes` — LineString geometry of authorized route with per-entity km breakdown
- `clearances` — third-party approvals (Nulla Osta) per entity per application
- `tariffs` — historically-versioned wear coefficients used by `WearCalculationService`

### RBAC Roles
- `super-admin` — Provincia di Pescara operators (full access)
- `operator` — other province operators
- `third-party` — municipalities, ANAS (limited to their clearance dashboard)
- `citizen` — transport companies/agencies submitting requests

### Planned Services
- **`WearCalculationService`** — road wear indemnity via per-axle weight × km × tariff coefficients (D.P.R. 495/1992)
- **GIS spatial queries** — `ST_Intersection` + `ST_Length` against entity polygon table; extract entities a route traverses + km per entity
- **AINOP integration** — via PDND API, verify bridge/infrastructure capacity along route (`codice_univoco_ainop` on infrastructure records)
- **PagoPA clearing** — single IUV from `WearCalculationService` output; RT webhook unlocks application; proceeds split among entities

### Geographic/GIS Layer
MariaDB spatial fields (`POLYGON`, `MULTIPOLYGON`, `LINESTRING`) store entity boundaries + route geometries. Spatial indices required. OSRM container must pre-load regional road graph. Frontend: Leaflet for interactive map.

### Architecture Docs
`.ai/` dir (to be created): deep-dive docs on complex subsystems — `STATE_MACHINE.md`, `GIS_ROUTING.md`, `WEAR_CALCULATION.md`, `PAGOPA.md`. Read before working on relevant domain.

## PHP & Laravel Conventions

### Strict Typing
Every PHP file begin with `declare(strict_types=1);`. Explicit return types on all methods/functions. PHP 8 type hints on all params.

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
Services in `App\Services\` must be `final`. Constructor property promotion with `private readonly` for injected deps.

### Enums
All enums in `App\Enums\`. Case names TitleCase (e.g., `WaitingClearances`, `Approved`). Backed enums (`string` or `int`) for DB-stored values.

### Eloquent
- Eager load always — prevent N+1.
- Prefer `Model::query()->...` over `DB::` raw queries.
- Define casts in `casts()` method, not `$casts` property.
- Eloquent relationship methods must have return type hints.

### Controllers & Validation
- Use `php artisan make:` with `--no-interaction` for all new files.
- Dedicated `App\Http\Requests\` Form Request classes for validation — never inline in controllers.
- Named routes + `route()` helper for all URL generation.

### Configuration
Never call `env()` outside `config/` files. Use `config('key')` in app code.

### Middleware (Laravel 12)
Middleware configured in `bootstrap/app.php` via `Application::configure()->withMiddleware()`, not `Kernel.php`. Laravel 12 keeps streamlined structure from Laravel 11.

### Comments & PHPDoc
Prefer PHPDoc over inline comments. Inline only when logic surprises reader — never describe what code does. Array shape annotations in PHPDoc when structure non-obvious.

### Code Style
- Curly braces on all control structures, even single-line.
- Run `./vendor/bin/pint --dirty` before finalizing changes.

## Project Structure Notes
- PSR-4: app code in `App\`, tests in `Tests\`
- Async jobs: `App\Jobs\` — PEC notifications, PDF generation, payment webhooks
- `.env.example`: SQLite + database-driver queue/cache for local dev; prod uses MariaDB + Redis
- EUPL-1.2 license; keep `publiccode.yml` current with new deps or deployment changes
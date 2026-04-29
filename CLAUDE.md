# CLAUDE.md

Guidance for Claude Code when working in this repo.

## Project Overview

**GTE-Abruzzo** (Gestionale Trasporti Eccezionali) — multi-tenant SaaS by Provincia di Pescara to digitize exceptional vehicle transport authorizations in Abruzzo. Intended for reuse across Italian public admin via Developers Italia.

Must comply: Art. 10 D.Lgs 285/1992 (Codice della Strada), D.P.R. 495/1992 wear formulas, AgID guidelines, MIT bridge-safety directives.

## Development Commands

```bash
# Local dev — write code locally, run tests inside the container
docker compose -f docker-compose.yml -f docker-compose.dev.yml exec app php artisan test --compact

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

Every code change need test (new or updated). Run minimum tests to verify change.

## Production Deployment (Docker)

```bash
docker-compose up -d --build
docker-compose exec app composer install
docker-compose exec app php artisan migrate --seed
```

Prod stack: `app` (Laravel/PHP-FPM + Nginx + Chromium), `db` (MariaDB 11.4 LTS with spatial extensions), `redis:7`, `osrm` (self-hosted routing engine, `--profile gis`).

## Milestone Status

| Branch | Status | Scope |
|--------|--------|-------|
| `v0.2.x` | ✅ Done | Identity & RBAC — SPID/CIE OIDC, users, companies, entities, setup wizard |
| `v0.3.x` | ✅ Done | Garage Virtuale — vehicles, axles, WearCalculationService, admin tariffario |
| `v0.4.x` | ✅ Done | WebGIS & Routing — Leaflet, OSRM, routes, roadworks, RouteIntersectionService |
| `v0.5.x` | 🔜 Next | State Machine — application wizard, clearances (Nulla Osta), PEC notifications |
| `v0.6.x` | Planned | PagoPA & PDF — payments, PAdES signature, protocollo |
| `v1.0.0` | Planned | AINOP/PDND — national infrastructure integration |

## Missing Features (to plan)

- [ ] Menu sistema e impostazioni per il branding e la configurazione (admin UI, to be planned in v0.5.x or dedicated milestone)

## Architecture

### Tech Stack
- **Backend**: Laravel 13, PHP 8.4, Eloquent ORM
- **Frontend**: Blade templates + Tailwind CSS v4 (zero-runtime) + Alpine.js + Leaflet, bundled via Vite 6
- **Database**: MariaDB 11.4 LTS with native GIS/spatial index support (required for route geometry)
- **Queue/Cache**: Redis (async jobs for PEC email, PDF generation, payment webhooks)
- **GIS Routing**: Self-hosted OSRM for snap-to-road route calculation
- **PDF Generation**: Browsershot (Headless Chrome)
- **Auth**: SPID/CIE via `laravel/socialite` + `socialiteproviders/manager`
- **Payments**: PagoPA integration (planned)

### Core Domain Model

Central entity: **application** (transport authorization request), moves through rigid state machine:

```
draft → submitted → waiting_clearances → waiting_payment → approved
```

Eloquent models — implemented (✅) or planned (🔜):
- ✅ `users` — natural persons with fiscal identity (SPID/CIE data), `entity_id` FK for third-party role
- ✅ `companies` — companies/agencies with delegations via `company_user` pivot
- ✅ `vehicles` — tractor units and trailers with axle/weight configurations (`vehicle_axles`)
- ✅ `entities` — municipalities, provinces, ANAS, motorways with GIS polygons (`geom`), PEC, AINOP stub
- ✅ `tariffs` — historically-versioned wear coefficients used by `WearCalculationService`
- ✅ `routes` — LineString geometry of authorized route with per-entity km breakdown (`entity_breakdown`)
- ✅ `roadworks` — construction sites: geometry (LINESTRING/POLYGON), `valid_from`/`valid_to`, severity, status
- 🔜 `applications` — transport authorization request and its state
- 🔜 `clearances` — third-party approvals (Nulla Osta) per entity per application

### RBAC Roles
- `super-admin` — Provincia di Pescara operators (full access)
- `operator` — other province operators
- `third-party` — municipalities, ANAS (clearance dashboard + roadworks management; scoped to own `entity_id`)
- `citizen` — transport companies/agencies submitting requests
- `law-enforcement` — Forze dell'Ordine (read-only: approved transports, active roadworks, QR code verification)

### Implemented Services
- **`WearCalculationService`** — road wear indemnity: per-axle weight × km × tariff coefficients (D.P.R. 495/1992)
- **`OsrmService`** — HTTP client for self-hosted OSRM: `snapToRoad()`, `alternatives()`; WKT via `ST_AsText(ST_GeomFromGeoJSON(?))`
- **`RouteIntersectionService`** — `ST_Intersects` + `ST_Length * 111.32` → entity_id → km breakdown
- **`RoadworkConflictService`** — `ST_Intersects` + date overlap + status filter → active conflicts on a route

### Planned Services
- **AINOP integration** — via PDND API, verify bridge/infra capacity on route (`codice_univoco_ainop` on entities)
- **PagoPA clearing** — IUV from `WearCalculationService` output; RT webhook unlock application; split proceeds among entities

### Geographic/GIS Layer
MariaDB spatial fields (`POLYGON`, `MULTIPOLYGON`, `LINESTRING`) store entity boundaries + route geometries. Spatial indices required. OSRM pre-loaded with regional road graph. Frontend uses Leaflet.

- Geometries stored with SRID 4326 via `ST_GeomFromText(?, 4326)`
- `ST_Length` on SRID 4326 returns degrees; converted to km with `× 111.32` (< 2% error at 41–42°N)
- WKT extracted for service queries via `ST_AsText(geometry)`
- Spatial columns added via `DB::statement('ALTER TABLE ... ADD COLUMN geometry LINESTRING NOT NULL')` and `CREATE SPATIAL INDEX`

### GIS Import
`php artisan gte:import-geo {file}` — imports GeoJSON FeatureCollection into `entities.geom`. Matches by `codice_istat` property. Source shapefiles converted via `ogr2ogr -f GeoJSON output.geojson input.shp`.

### Architecture Docs
`.ai/` dir (to be created) — deep-dive docs on complex subsystems: `STATE_MACHINE.md`, `GIS_ROUTING.md`, `WEAR_CALCULATION.md`, `PAGOPA.md`. Read before working on relevant domain.

## PHP & Laravel Conventions

### Strict Typing
Every PHP file begin with `declare(strict_types=1);`. Explicit return types on all methods/functions. PHP 8 type hints for all params.

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
Services in `App\Services\` must be `final`. Constructor property promotion with `private readonly` for deps.

### Enums
Enums in `App\Enums\`. Case names TitleCase (e.g., `WaitingClearances`, `Approved`). Backed enums (`string` or `int`) for DB values.

### Eloquent
- Always eager load (prevent N+1).
- Prefer `Model::query()->...` over `DB::` raw queries.
- Define casts in `casts()` method, not `$casts` property.
- Always use Eloquent relationship methods with return type hints.

### Controllers & Validation
- Use `php artisan make:` commands with `--no-interaction` to generate all new files (models, migrations, controllers, jobs, etc.).
- Dedicated `App\Http\Requests\` Form Request classes for validation — never inline in controllers.
- Use named routes and `route()` helper for all URL generation.

### Configuration
Never call `env()` outside `config/` files. Use `config('key')` in app code.

### .env — bootstrap and network topology only
`.env` contains only bootstrap + network topology vars: `APP_KEY`, `APP_ENV`, `APP_URL`, DB credentials, Redis credentials, `OSRM_BASE_URL`, `CHROMIUM_PATH`. Nothing else.

All else → **database**, managed via **admin UI**:
- App behaviour: debug mode, timezone, locale/i18n, maintenance mode
- Mail/SMTP server and credentials
- Integration credentials: SPID SP metadata, PagoPA station IDs, PDND client keys, Firma Remota endpoints, etc.

`APP_VERSION*` are Docker build ARGs from CI/CD — not runtime env vars, must not appear in `.env`.

### Database — always MariaDB
MariaDB 11.4 LTS only. No SQLite fallback. Local dev must use `docker compose up` with `db` service.

### Production deployment — Portainer, no shell access
Prod runs on Portainer (Docker stack). No shell access to containers. Consequences:
- Migrations auto-run on container startup via `entrypoint.sh` (`php artisan migrate --force`).
- Bootstrap seeders (roles, permissions, default settings) via dedicated idempotent seeder called by entrypoint — not manually.
- One-off ops: Artisan commands via Portainer exec or admin UI action — never assume shell.

### Middleware (Laravel 13)
Middleware in `bootstrap/app.php` via `Application::configure()->withMiddleware()`, not `Kernel.php`. Laravel 13 streamlined structure from Laravel 11.

### Comments & PHPDoc
Prefer PHPDoc over inline comments. Inline only when logic would surprise — never to describe what code does. Array shape annotations in PHPDoc when structure non-obvious.

### Naming: App\Models\Route vs Facade
`App\Models\Route` conflicts with `Illuminate\Support\Facades\Route`. In files that import both, alias the facade:
```php
use Illuminate\Support\Facades\Route as RouteFacade;
use App\Models\Route;
```
The model has `protected $table = 'routes'` explicitly set.

### Code Style
- Always curly braces for control structures, even single-line.
- Run `./vendor/bin/pint --dirty` before finalizing any set of changes.

## Project Structure Notes
- PSR-4: application code in `App\`, tests in `Tests\`
- Jobs (async): `App\Jobs\` — PEC notifications, PDF generation, payment webhooks
- `.env.example` covers only bootstrap/network vars; production uses MariaDB + Redis
- EUPL-1.2 license; `publiccode.yml` keep current with new deps or deployment requirements
- `docker-compose.dev.yml` (gitignored) — local dev override with volume mount; use alongside `docker-compose.yml`

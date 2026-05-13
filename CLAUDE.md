# PNTE.md

Guidance for PNTE Code when working in this repo.

## Project Overview

**PNTE** (Piattaforma Nazionale Trasporti Eccezionali) — multi-tenant SaaS by Provincia di Pescara to digitize exceptional vehicle transport authorizations in Italy. Intended for reuse across Italian public admin via Developers Italia.

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

Prod stack: `app` (Laravel/PHP-FPM + Nginx + Chromium), `db` (PostgreSQL 16 + PostGIS 3.4), `redis:7`, `osrm` (self-hosted routing engine, `--profile gis`).

## Milestone Status

| Branch | Status | Scope |
|--------|--------|-------|
| `v0.2.x` | ✅ Done | Identity & RBAC — SPID/CIE OIDC, users, companies, entities, setup wizard |
| `v0.3.x` | ✅ Done | Garage Virtuale — vehicles, axles, WearCalculationService, admin tariffario |
| `v0.4.x` | 🔜 Next | WebGIS & ARS — Leaflet, OSRM, routes, roadworks, ARS (Archivio Regionale Strade), veicoli agricoli, WearCalculationService esteso |
| `v0.5.x` | Planned | State Machine — application wizard, tipo_istanza, clearances ARS fast-track, check-in viaggio, Radar law-enforcement |
| `v0.6.x` | Planned | PagoPA & PDF — payments, PAdES, protocollo, RipartoService, allerta meteo Protezione Civile |
| `v0.7.x` | Planned | Open Data Portal — mappa pubblica cantieri, statistiche aggregate, export GeoJSON/KML |
| `v1.0.0` | Planned | AINOP/PDND — national infrastructure integration |

## Missing Features (to plan)

- [x] Menu sistema e impostazioni per il branding e la configurazione (admin UI) — implementato in v0.4.x

## Architecture

### Tech Stack
- **Backend**: Laravel 13, PHP 8.4, Eloquent ORM
- **Frontend**: Blade templates + Tailwind CSS v4 (zero-runtime) + Alpine.js + Leaflet, bundled via Vite 6
- **Database**: PostgreSQL 16 + PostGIS 3.4 (required for route geometry and advanced spatial analysis)
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
- ✅ `users` — natural persons with fiscal identity (SPID/CIE data); powers derive either from runtime public-registry validation or from explicit internal delegations
- ✅ `companies` — principals and agencies (`is_agency`) with official registry data and partner relationships
- 🔜 `agency_mandates` — explicit Company -> Agency partner mandate with legal duration, signed document, scope rules, suspension/revocation, renewal workflow and audit metadata
- 🔜 `delegations` — polymorphic authorization binding for individual users, subordinate to an active `agency_mandate` when operating on behalf of an agency
- 🔜 `vehicle_documents` — registration booklets, load diagrams, homologation files attached to vehicles
- ✅ `vehicles` — tractor units and trailers with axle/weight configurations (`vehicle_axles`)
- ✅ `entities` — municipalities, provinces, ANAS, motorways with GIS polygons (`geom`), PEC, AINOP stub, `is_tenant` (bool), `has_financial_delegation` (bool), `is_capofila` (bool)
- ✅ `tariffs` — historically-versioned wear coefficients used by `WearCalculationService`
- ✅ `routes` — LineString geometry of authorized route with per-entity km breakdown (`entity_breakdown`)
- ✅ `roadworks` — construction sites: geometry (LINESTRING/POLYGON), `valid_from`/`valid_to`, severity, status
- 🔜 `applications` — transport authorization request and its state
- ✅ `standard_routes` — ARS pre-approved routes (LINESTRING, sagoma/massa limits) for Fast-Track clearances
- 🔜 `clearances` — third-party approvals (Nulla Osta) per entity per application; states: `pre_cleared`, `pending_review`, `approved`, `rejected`

### RBAC Roles
- `system-admin` — platform/infrastructure operators; no entity/company binding; only `/system`; 403 on all business routes. Owns all integration credentials (SMTP, PEC, OIDC SPID/CIE, PDND, PagoPA, AINOP), GIS sources, app behaviour (debug/timezone/locale/maintenance), and global branding (logo + nome piattaforma).
- `admin-ente` — entity-bound manager; handles operators, payments, clearances, reports. **Province sono tutte uguali post-certificazione**: ogni provincia gestisce in autonomia propri comuni/operatori/pratiche.
- `admin-capofila` — equivalente a `admin-ente`. Il flag `is_capofila` resta nel DB per identificare l'ente fallback degli enti non-federati (gestione errori di routing/onboarding); **non è una superpower governance**.
- `operator` — entity-bound staff for own tenant/entity workflows
- `admin-azienda` — company-bound manager for transport company or agency
- `agency` — agency operators with multiple active `agency_mandates` and session-level partner context switcher
- `third-party` — municipalities, ANAS (clearance dashboard + roadworks management; scoped via `delegations`)
- `citizen` — transport companies/agencies submitting requests
- `law-enforcement` — Forze dell'Ordine (read-only: approved transports, active roadworks, QR code verification)

### Implemented Services
- **`WearCalculationService`** — road wear indemnity: per-axle weight × km × tariff coefficients (D.P.R. 495/1992)
- **`OsrmService`** — HTTP client for self-hosted OSRM: `snapToRoad()`, `alternatives()`; WKT via `ST_AsText(ST_GeomFromGeoJSON(?))`
- **`RouteIntersectionService`** — `ST_Intersects` + `ST_Length * 111.32` → entity_id → km breakdown
- **`RoadworkConflictService`** — `ST_Intersects` + date overlap + status filter → active conflicts on a route
- **`GeoJsonExportService`** — RFC 7946 GeoJSON export for routes, entities, roadworks; SRID 4326 compliance; geometry simplification for frontend; metadata enrichment (entity breakdown, authority names, km totals)
- **`SpatialQueryService`** — unified interface for `ST_Intersects()`, `ST_Length()`, `ST_Buffer()` operations; caches results in Redis (TTL 30 min); pre-computes `entity_breakdown` materialization; manages spatial index optimization

### Diagnostics Layer (system-admin only)

`App\Services\Diagnostics\*` — 13 diagnostic services + `HealthCheckService` orchestrator. Ognuno implementa `DiagnosticInterface::run(): DiagnosticResult` e logga in `system_audit_logs` (action `diagnostic.run.{key}`).

Servizi: `db`, `postgis`, `redis`, `queue`, `storage`, `osrm`, `smtp`, `imap`, `oidc`, `pdnd`, `pagopa`, `ainop`, `routing` (E2E pipeline).

Tre superfici di accesso, **tutte autenticate (session web) e protette da middleware `system-admin`**:

| Superficie | Endpoint | Uso |
|---|---|---|
| **UI web** | `/system/diagnostics` (master), `/system/diagnostics/api-tester`, `/system/integrations/{service}/test` | Test interattivi via Alpine.js + fetch |
| **JSON API** | `GET /api/v1/system/health`, `GET /api/v1/system/health/{service}`, `POST /api/v1/system/test/{mail,routing,geojson}` | CI/healthcheck/monitoring (session cookie required) |
| **Artisan CLI** | `php artisan pnte:diag` (all), `php artisan pnte:diag:run {service}` (single) | Docker `HEALTHCHECK`, Portainer exec, debug. Flag `--json`, `--no-audit`. Exit 0 ok / 1 fail / 2 error |

Nessun servizio diagnostic deve mai lanciare eccezioni: i fallimenti sono catturati e wrappati in `DiagnosticResult::fail()`.

### Planned Services
- **`AgencyDetectionService`** — queries PDND Infocamiere API by P.IVA; extracts `ateco_principale` and `descrizione_attivita`; filters by ATECO 82.99.11 + Legge 264/1991 compliance keywords; returns `['is_agency' => bool, 'ateco_code' => string, 'ateco_description' => string, 'compliance_verified' => bool]`; used in onboarding flow and monthly re-sync job
- **AINOP integration** — via PDND API, verify bridge/infra capacity on route (`codice_univoco_ainop` on entities)
- **PagoPA clearing** — IUV from `WearCalculationService` output; RT webhook unlock application; split proceeds among entities
- **`PdfTemplateService`** — generates official PDFs (tenant-admin nomination, special mandate, authorization layouts) from Blade templates and public-registry data
- **`P7mVerificationService`** — validates CAdES/PAdES files, checks integrity/revocation, extracts signer CF from certificate, matches against IPA/PDND registries
- **`PartnerMandateLifecycleService`** — enforces `agency_mandates` expiry, T-30/T-7 reminders, simplified renewals, suspension/revocation and partner-scope checks
- **`ResourceLockService`** — prevents duplicate applications for the same vehicle/date window and preserves the originating partner context for audit

### Geographic/GIS Layer
PostGIS spatial fields (`POLYGON`, `MULTIPOLYGON`, `LINESTRING`) store entity boundaries + route geometries. GiST indices required. OSRM pre-loaded with regional road graph. Frontend uses Leaflet.

- Geometries stored with SRID 4326 via `ST_GeomFromText(?, 4326)`
- `ST_Length` on SRID 4326 returns degrees; converted to km with `× 111.32` (< 2% error at 41–42°N)
- WKT extracted for service queries via `ST_AsText(geometry)`
- Spatial columns added via `DB::statement('ALTER TABLE ... ADD COLUMN geometry geometry(LINESTRING, 4326) NOT NULL')` and `CREATE INDEX ... USING GIST (...)`

### GIS Import
`php artisan pnte:import-geo {file}` — imports GeoJSON FeatureCollection into `entities.geom`. Matches by `codice_istat` property. Source shapefiles converted via `ogr2ogr -f GeoJSON output.geojson input.shp`.

### Architecture Docs
`.ai/` dir (to be created) — deep-dive docs on complex subsystems: `STATE_MACHINE.md`, `GIS_ROUTING.md`, `WEAR_CALCULATION.md`, `PAGOPA.md`. Read before working on relevant domain.

## AgID Compliance — Principi Vincolanti per lo Sviluppo

Ogni contributo al codice deve rispettare i seguenti principi. Non sono linee guida opzionali: violazioni bloccano il merge.

| Principio | Regola operativa |
|---|---|
| **Once-Only** | Se un dato è disponibile da SPID, IPA, InfoCamere o altro endpoint PDND: **non chiederlo all'utente**. Campo compilabile da API = campo read-only. Aggiungere un campo manuale dove esiste un'API pubblica è un bug. |
| **Digital-by-Default** | Nessun processo con fallback cartaceo o "scarica il modulo". Ogni transizione di stato deve essere completabile interamente in piattaforma. |
| **Privacy-by-Design** | Dato minimo necessario (art. 25 GDPR). Zero PII nei log applicativi (no `Log::info($user->codice_fiscale)`). `codice_fiscale` cifrato at-rest. Soft-delete con anonimizzazione pianificata. |
| **Accessibilità (WCAG 2.1 AA)** | Seguire i pattern del Design System PA. Attributi `aria-*` su ogni componente interattivo Alpine.js. Contrasti minimi conformi. Non fare merge di UI senza test con screen reader. |
| **Interoperabilità (EIF/ModI)** | Endpoint pubblici documentati con OpenAPI 3.0. Versionamento API in URL (`/api/v1/`). Output geografici in formato GeoJSON. Header `Content-Type: application/json` su tutti gli endpoint API. |
| **Open-Source by Default** | Licenza EUPL-1.2. Prima di aggiungere un vendor privato, verificare su Developers Italia se esiste un'alternativa a riuso. `publiccode.yml` aggiornato a ogni milestone. |
| **Cloud-Native / Stateless** | Nessun dato persistente nel container `app` (no file locali fuori da `storage/`). Storage via Laravel Filesystem driver configurabile. Le migration girano automaticamente all'avvio via `entrypoint.sh`. |
| **Zero-Trust** | Policy Laravel su ogni Model: nessuna logica di autorizzazione inline nei controller. Rate-limiting su tutti gli endpoint pubblici e API. Validazione input esclusivamente via Form Request. |
| **Tenant Guard** | Operazioni finanziarie (generazione IUV, Clearing House) possono essere avviate solo se la Provincia di partenza ha `is_tenant = true`. Verificare esplicitamente nei Service, non assumere dal contesto. |
| **Riuso-prima-di-costruire** | Prima di implementare un'integrazione PA, verificare client ufficiali su Developers Italia o pacchetti AgID. |

### Entity Flags: `is_tenant`, `has_financial_delegation`, `is_capofila`

Tre boolean su `entities`, indipendenti e ortogonali:

- **`is_tenant`** (default `false`): se `true`, l'ente ha una dashboard attiva; il sistema invia PEC di avviso e attende approvazione in piattaforma. Se `false`, PEC automatica e operatore Provincia sblocca manualmente.
- **`has_financial_delegation`** (default `false`): se `true`, la quota usura dell'ente è inclusa nell'IUV PagoPA della Provincia e redistribuita via SEPA mensile. Se `false`, quota scorporata dall'IUV con avviso all'utente. Il flag può essere attivato/disattivato dall'ente dalla propria dashboard con grace period fino a mezzanotte.
- **`is_capofila`** (default `false`): se `true`, l'ente è il **fallback handler per enti non-federati** (riceve pratiche orfane, gestione errori onboarding/routing). NON conferisce poteri governance speciali in dashboard quotidiana — le province sono tutte uguali post-certificazione. La logica fallback è server-side, mai esposta come UI privilegiata.

### System/Admin Isolation

- `system-admin` users MUST NOT have active rows in `delegations` or `agency_mandates`.
- Business routes (`/pratiche`, `/pagamenti`, `/nulla-osta`, PDF download, SEPA exports) MUST require an active delegation binding to `Entity` or `Company`; when the actor is an agency operator, the route MUST also resolve to an active `agency_mandate` for the selected principal company.
- The first `admin-ente` onboarding for a public entity MUST use system-generated PDF + signed upload (`.p7m`/PAdES) validated by `P7mVerificationService`.
- The same validation engine is reused for Agency -> Company special mandates, to keep approval binary and non-discretionary.
- `agency_mandates.valid_until` MUST NEVER exceed the expiry date written into the signed special-mandate document chosen during PDF generation.
- A principal company MUST be able to suspend or revoke one agency partner instantly without affecting other partner mandates.
- Audit trails MUST persist both the acting user and the originating partner context (`agency_mandate_id` or equivalent) on sensitive business operations.

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

All else → **database**, managed via **system-admin UI under `/system/*`**:
- App behaviour: debug mode, timezone, locale/i18n, maintenance mode (`/system/settings/app-behaviour`)
- Mail/SMTP server and credentials (`/system/integrations/smtp`)
- PEC/IMAP listener credentials (`/system/integrations/pec`)
- Integration credentials: SPID/CIE OIDC (`/system/integrations/oidc`), PDND voucher (`/system/integrations/pdnd`), PagoPA (`/system/integrations/pagopa`), AINOP X.509 (`/system/integrations/ainop`)
- Branding **minimale globale** (logo + nome piattaforma) — niente colori/CSS per-tenant, niente personalizzazione per provincia (`/system/settings/branding`)
- GIS sources (URL ISTAT comuni/province) e import via `/system/geo`

`APP_VERSION*` are Docker build ARGs from CI/CD — not runtime env vars, must not appear in `.env`.

### Database — PostgreSQL + PostGIS
PostgreSQL 16 with PostGIS 3.4 only. No SQLite fallback. Local dev must use `docker compose up` with `db` service.

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
- `.env.example` covers only bootstrap/network vars; production uses PostgreSQL/PostGIS + Redis
- EUPL-1.2 license; `publiccode.yml` keep current with new deps or deployment requirements
- `docker-compose.dev.yml` (gitignored) — local dev override with volume mount; use alongside `docker-compose.yml`

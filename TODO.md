# PNTE — Todo & Versioning

## Schema versioni

| Versione | Milestone | Contenuto |
|----------|-----------|-----------|
| **v0.1.x** | Stack | Aggiornamento dipendenze, Docker, tooling |
| **v0.2.x** | M1 | Identity (SPID/CIE), RBAC, Anagrafiche |
| **v0.3.x** | M2 | Garage Virtuale + WearCalculationService |
| **v0.4.x** | M3 | WebGIS + OSRM + ARS (Archivio Regionale Strade) + Intersezione Spaziale |
| **v0.5.x** | M4 | State Machine + tipo_istanza + Check-in Viaggio + Radar Forze dell'Ordine |
| **v0.6.x** | M5 | PagoPA + PDF + Firma PAdES + Allerta Meteo + RipartoService |
| **v0.7.x** | M6 | Open Data Portal — mappa pubblica cantieri + statistiche + GeoJSON/KML |
| **v1.0.0** | GA | AINOP/PDND, security audit, AgID compliance |

---

## BUGFIXING 


---

## Missing Feature

- [x] Menu sistema e impostazioni per il branding e la configurazione — implementato in v0.4.x (impersonazione + pannello impostazioni + design system)

---

## v0.1.x — Stack Alignment ✅

- [x] Laravel `^11` → `^13.0` (v13.6.0)
- [x] PHP constraint `^8.2` → `^8.4`
- [x] Tailwind v3 → v4 via `@tailwindcss/vite` (rimossi postcss/autoprefixer)
- [x] Alpine.js v3 aggiunto
- [x] `spatie/laravel-permission ^6` aggiunto
- [x] `spatie/browsershot ^5` aggiunto
- [x] Node `20-slim` → `25-trixie-slim` in Dockerfile
- [x] `docker-compose.yml` creato (app, db, redis, osrm)
- [x] `.env.example` aggiornato
- [x] `publiccode.yml` aggiornato a v0.1.0
- [x] Tag `v0.1.0` rilasciato

---

## v0.2.x — M1: Identity, RBAC, Anagrafiche

- [x] **[1.1]** Migration `users` — identità fiscale SPID/CIE (codice fiscale, dati IDP)
- [x] **[1.1]** Integrazione SPID/CIE via Socialite + proxy OIDC esterno
- [ ] **[1.1]** Accesso fallback solo in ambiente `local/dev` con credenziali base per `super-admin`/utenti seedati, da disabilitare automaticamente negli ambienti con SPID/CIE attivo
- [x] **[1.2]** Setup Spatie Permission — seed ruoli: `super-admin`, `operator`, `third-party`, `citizen`, `law-enforcement`
- [ ] **[1.2]** Seeder bootstrap idempotente per parametri iniziali di installazione: super-admin iniziale, ente capofila, impostazioni applicative minime, configurazione mail/SMTP, canali notifiche, metadata SPID/PagoPA/PDND/Firma Remota come placeholder amministrabili da UI
- [ ] **[1.2]** Procedura di first-run del `super-admin`: credenziali bootstrap o token/password temporanea con obbligo di rotazione al primo accesso
- [ ] **[1.2]** Procedura di recupero accesso amministrativo: comando/azione controllata per rigenerare accesso `super-admin` in caso di perdita credenziali, senza interventi manuali sul database
- [x] **[1.3]** Migration `companies` + pivot `company_user` (deleghe/procure)
- [x] **[1.3]** UI: richiesta delega aziendale (citizen) + approvazione (operator)
- [x] **[1.4]** Migration `entities` — Comuni, Province, ANAS, Autostrade (GIS `MULTIPOLYGON`, PEC, ISTAT)
- [x] **[1.4]** CRUD entità amministrative (solo `super-admin`)
- [x] **[1.5]** Pagina impostazioni admin — sezione Mail: modifica SMTP + tasto "Invia email di test"
- [x] **[1.6]** Sincronizzazione Enti via API IPA: Sviluppo Service Open Data IPA e automazione notturna per aggiornamento PEC (Comuni, Province, Forze dell'Ordine)
- [x] **[1.7]** Validazione Aziende via InfoCamere / INI-PEC (PDND): Integrazione API per autocompilazione da P.IVA e campi sensibili in read-only
- [x] **[1.8]** Setup step 3: tasto "Invia email di test"
- [ ] **[1.9]** Fast-Track delega aziendale: verifica CF utente == Legale Rappresentante da Registro Imprese (PDND) → approvazione istantanea senza operatore
- [ ] **[1.10]** Delega dipendenti enti pubblici via documento formale: generazione PDF da sistema, firma digitale `.p7m` del soggetto titolato, upload e validazione automatica (integrità, certificato, CF firmatario, coerenza IPA/PDND) prima dell'attivazione delega `third-party`
- [ ] **[1.11]** Delega per ditte/aziende via documento formale: generazione PDF da sistema, firma digitale `.p7m` del legale rappresentante, upload e validazione automatica (integrità, certificato, CF firmatario, matching Registro Imprese) prima dell'attivazione delega `admin-azienda`
- [ ] **[1.12]** Routing approvazioni manuali: se non gestite automaticamente → admin Provincia Tenant di appartenenza → se ente non-tenant → Capofila
- [ ] **[1.13]** RBAC separato: rinomina `super-admin` in `system-admin`, introduce `admin-ente`, `agency` e middleware `EnsureDelegationBound` per bloccare i dati business agli utenti non delegati
- [ ] **[1.14]** `agency_mandates`: `principal_company_id`, `agency_company_id`, `status`, `valid_from`, `valid_until`, `scope_rules`, `mandate_document_path`, `validated_at`, `validated_signer_cf`, `approved_at`, `revoked_at`, `suspended_at`, `auto_renew`
- [ ] **[1.15]** `delegations` polimorfica: `user_id`, `delegable_type`, `delegable_id`, `role`, `status`, `agency_mandate_id` nullable, audit di approvazione e revoca; per utenti agenzia valida solo con mandato partner attivo
- [ ] **[1.16]** `entities`: aggiungere `is_capofila` boolean default false; seeder Capofila (Pescara `is_tenant=true`, `is_capofila=true`)
- [ ] **[1.17]** Pannello `/system`: Vault connettori (PDND/OIDC/PagoPA), SMTP/IMAP madre, scheduler, telemetria aggregata e anonimizzata
- [ ] **[1.18]** Dashboard ditta **Gestione Partner** + dashboard agenzia: lista partner, context switcher "Per quale cliente sto lavorando?", stato, scadenza, ultimo accesso, azioni per singola Agenzia
- [ ] **[1.19]** `PdfTemplateService`: generazione PDF da Blade per Procura Speciale Agenzia-Ditta e nomina primo `admin-ente`, includendo la data di validità scelta dall'Agenzia
- [ ] **[1.20]** `P7mVerificationService`: verifica integrità firma, revoca/scadenza certificato, estrazione CF firmatario e matching binario con IPA/Registro Imprese
- [ ] **[1.21]** Mandato Agenzia -> Ditta Scenario A: richiesta in piattaforma e approvazione con click della Ditta già censita → creazione `agency_mandates`
- [ ] **[1.22]** Mandato Agenzia -> Ditta Scenario B: PDF Procura Speciale autogenerato + upload `.p7m` + attivazione automatica del `agency_mandate` se il firmatario coincide con il legale rappresentante
- [ ] **[1.23]** Primo censimento Ente: PDF di nomina autogenerato + upload firmato + verifica automatica di sostanza e forma; auto-approvazione se `is_capofila=true`
- [ ] **[1.24]** Lifecycle `agency_mandates`: scadenza naturale obbligatoria, notifiche T-30/T-7, rinnovo semplificato se condizioni immutate, nuova firma P7M se cambiano poteri/soggetti/durata legale
- [ ] **[1.25]** Sospensione e revoca istantanea partner: kill-switch per singola Agenzia senza impattare gli altri mandati della stessa Ditta
- [ ] **[1.26]** Audit partner: salvare contesto `agency_mandate_id` e `created_by_agency_id` sulle operazioni sensibili per attribuzione responsabilità e KPI per Agenzia
- [ ] **[1.27]** ATECO-based agency auto-classification via PDND Infocamiere: target codice 82.99.11 (Fornitura assistenza registrazione autoveicoli) + Legge 264/1991 compliance keywords in descrizione attività; `companies` migration: add `ateco_code` (string, nullable), `ateco_last_synced_at` (timestamp, nullable); service `AgencyDetectionService::detectAgencyStatus(piva)` ritorna `['is_agency' => bool, 'ateco_code' => string, 'ateco_description' => string, 'compliance_verified' => bool]`; onboarding integration auto-flag `is_agency = true` + UI confirmation dialog; Artisan `pnte:re-sync-agency-ateco` (monthly scheduler) revoca tutti gli `agency_mandates` attivi con `status = 'revoked'` se ATECO cambia o compliance fallisce; PDF Procura Speciale aggiunge dichiarazione Legge 264/1991 per audit
- [ ] **[1.28]** Immediate DB cutover: sostituzione stack `mariadb` con `postgresql + postgis` in docker compose, env defaults e `config/database.php` (`DB_CONNECTION=pgsql`, porta 5432)
- [ ] **[1.29]** Refactor migrations SQL MySQL-specifiche (`AFTER`, `MODIFY COLUMN`, `DROP INDEX ... ON`) verso sintassi PostgreSQL + attivazione `CREATE EXTENSION IF NOT EXISTS postgis`
- [ ] **[1.30]** Spatial index migration: sostituire `CREATE SPATIAL INDEX` con indici `GiST` (`CREATE INDEX ... USING GIST (...)`) su `entities.geom`, `routes.geometry`, `roadworks.geometry`, `standard_routes.geometry`
- [ ] **[1.31]** Validazione servizi GIS su PostGIS: query `ST_Intersects`, `ST_Length`, `ST_Buffer`, `ST_AsGeoJSON`, `ST_GeomFromText`, `ST_GeomFromGeoJSON`; smoke test end-to-end routing -> clearances -> waiting_payment

---

## v0.3.x — M2: Garage Virtuale + Tariffario

- [x] **[2.1]** Migration `vehicles` — trattori, rimorchi, mezzi d'opera (targa, telaio, massa, dimensioni)
- [ ] **[2.2]** `vehicle_documents`: libretti di circolazione, schemi di carico, omologazioni e allegati tecnici di flotta
- [x] **[2.3]** Migration `vehicle_axles` — interassi e carico per asse
- [x] **[2.3]** UI: configuratore assi (Alpine.js dinamico)
- [ ] **[2.4]** Vehicle Assembly UI: selezione motrice + rimorchio dal Garage con aggregazione automatica di masse, assi e profilo usura
- [x] **[2.5]** Migration `tariffs` — coefficienti d'usura storicizzati (valid_from / valid_to)
- [x] **[2.5]** `App\Services\WearCalculationService` — formula D.P.R. 495/1992 (peso × km × coeff. per asse)
- [x] **[2.5]** Admin tariffario: CRUD coefficienti con storico versioni

---

## v0.4.x — M3: WebGIS + OSRM + ARS + Intersezione Spaziale

- [x] **[3.1]** Import shapefile/GeoJSON confini comunali e provinciali Abruzzo → `entities.geom`
- [x] **[3.2]** Frontend Leaflet — mappa interattiva per tracciamento percorso
- [x] **[3.2]** Integrazione API OSRM snap-to-road → salvataggio `LineString` in `routes.geometry`
- [x] **[3.3]** Query `ST_Intersection` + `ST_Length` → km per ente estratti automaticamente
- [x] **[3.4]** Stub AINOP/PDND: campo `codice_univoco_ainop` su tabella infrastrutture
- [ ] **[3.4]** Evidenziazione WebGIS corridoi nazionali MIT idonei — rinviato a v1.0.0 (AINOP/PDND)
- [x] **[3.5]** Migration `roadworks` + controllo cantieri al submit: `ST_Intersects(route, roadwork)` + overlap date range → blocco con indicazione tratto
- [x] **[3.6]** Migrazioni ARS: `standard_routes` (LINESTRING + limiti sagoma/massa) + `tipo_applicazione` su `tariffs` + `tipo_asse` nullable
- [x] **[3.7]** `StandardRoute` model + `StandardRoutePolicy` + CRUD `ThirdParty\StandardRouteController` + Blade views
- [x] **[3.8]** `StandardRouteOverlayService`: `analyze()` + `segmentCoverage()` con `ST_Buffer` (≈11m) sul percorso
- [x] **[3.9]** `ArsOverlayController` `POST /api/routing/ars-overlay` + `EntityGeoJsonController` `GET /api/entities/geojson`
- [x] **[3.10]** `route-builder.js`: layer verde/rosso ARS (singola/multipla) + modalità selezione poligoni ISTAT (periodica)
- [x] **[3.11]** `pnte:import-standard-routes {file} {entity_id}` — import GeoJSON strade standard
- [x] **[3.12]** `VehicleType` enum: +4 tipi agricoli + `isAgricultural()` helper
- [x] **[3.13]** `TipoApplicazioneTariff` enum + `Tariff::scopeByTipoApplicazione()` + `TariffFactory` aggiornata — `WearCalculationService::calculateForApplication(WearContext)` rinviato a v0.5.x (dipende da `TipoIstanza`)
- [ ] **[3.14]** Import confini ISTAT nazionale (non solo Abruzzo) in `entities.geom` — cartografia completa per percorsi interregionali; filtro `is_tenant` per attivazione operativa
- [ ] **[3.15]** Route split auto-PEC enti non-tenant: rilevazione enti senza `is_tenant = true` lungo il percorso → PEC automatica con invito esplicito a censirsi in piattaforma nel body

---

## v0.5.x — M4: State Machine + tipo_istanza + Check-in + Radar

- [x] **[4.1]** Wizard compilazione domanda multi-step: Azienda → Convoglio → Percorso → Riepilogo
- [x] **[4.2]** Migration `applications` — stato `draft`, `tipo_istanza`, `numero_viaggi`, `valida_da`, `valida_fino`, `selected_entity_ids`, `viaggi_effettuati`, `sospesa_fino`
- [x] **[4.3]** State machine: transizioni `draft → submitted → waiting_clearances → waiting_payment → approved`
- [x] **[4.4]** Scrivania Enti Terzi — dashboard ruolo `third-party` (tratta di competenza + Approva/Rifiuta)
- [x] **[4.5]** Migration `clearances` — Nulla Osta per ente per pratica (`ClearanceStatus` incl. `pre_cleared`)
- [x] **[4.6]** `App\Jobs\SendClearanceNotification` — PEC asincrono via Redis queue + listener ricezione esiti
- [ ] **[4.7]** Percorsi alternativi OSRM (`alternatives=true`) quando cantiere attivo — cablare nel wizard di submission
- [x] **[4.8]** `TipoIstanza` enum + `WearContext` value object
- [x] **[4.9]** `ClearanceStatus` enum con `PreCleared` + `ClearanceDispatchService` (ARS fast-track + PEC dispatch + skip `waiting_clearances`)
- [x] **[4.10]** `StoreApplicationRequest`: validazione condizionale per `tipo_istanza` (numero_viaggi, valida_da/fino, selected_entity_ids)
- [x] **[4.11]** `TripStatus` enum + migration `trips` (application_id, driver_user_id, status, started_at, ended_at, geometry_snapshot)
- [x] **[4.12]** `Trip` model + `TripPolicy` + `CitizenTripController` (start/end/cancel) + middleware anti-sospensione
- [ ] **[4.13]** PWA mobile-friendly autista: dashboard autorizzazioni attive + pulsante "INIZIA/CONCLUDI VIAGGIO" + banner sospensione allerta meteo
- [ ] **[4.13-bis]** `trips`: aggiungere `trip_vehicle_id` e `trip_trailer_id` per i veicoli effettivi usati nel Preavviso di Viaggio, distinti dai veicoli nominali dell'autorizzazione
- [ ] **[4.13-ter]** Validazione Preavviso di Viaggio: i mezzi selezionati al check-in devono appartenere alla flotta della stessa Ditta/cliente e rispettare i limiti dell'autorizzazione
- [x] **[4.14]** `RadarController` (`law-enforcement`) + `GET /api/law-enforcement/active-trips`
- [ ] **[4.15]** Leaflet Radar: mappa real-time convogli in transito, polling 30s, popup targa/tipo/ora
- [ ] **[4.16]** `ImapListenerJob` schedulato: lettura PEC in ingresso via IMAP, match ID pratica nell'oggetto, allegato PDF → associato a fascicolo, stato clearance → `pending_review`
- [ ] **[4.17]** UI "Da Valutare" (semaforo giallo): notifica operatore Provincia, vista allegato PDF, pulsanti Approva/Rifiuta con campo motivazione obbligatoria
- [ ] **[4.18]** Resource locking pratiche: blocco backend su stesso mezzo e stessa data/finestra operativa già impegnata da un'altra pratica in bozza o lavorazione
- [ ] **[4.19]** Messaggio conflitto multi-agenzia: indicare quale Agenzia e quale `agency_mandate` hanno già aperto la pratica concorrente; fornire audit e warning UI coerenti con il blocco backend

---

## v0.6.x — M5: Pagamenti + Rilascio Legale + Allerta Meteo

- [ ] **[5.1]** Migration `entities`: +`is_tenant` boolean default false, +`has_financial_delegation` boolean default false
- [ ] **[5.2-bis]** Onboarding `has_financial_delegation` per enti `third-party`: first-visit modal con spiegazione vantaggi, default OFF; grace period: flag resta attivo fino a mezzanotte dopo disattivazione
- [ ] **[5.1b]** PagoPA — IUV mono-beneficiario Provincia: importo = Σ quote enti con `has_financial_delegation=true` + quota Provincia; guard: IUV solo se Provincia di partenza è Tenant; scorporo quote `has_financial_delegation=false` con avviso utente
- [ ] **[5.1c]** Webhook RT PagoPA → transizione `waiting_payment → approved` + avvio `GenerateAuthorizationPdf`
- [ ] **[5.old-1]** `WearCalculationService::calculateForApplication()` — integrazione `WearContext` + `TipoIstanza` (analitico / forfettario periodico)
- [ ] **[5.2]** Webhook RT (Ricevuta Telematica) → transizione `waiting_payment → approved`
- [ ] **[5.3]** Vista Blade layout ufficiale autorizzazione (mini-mappa + tabelle tecniche)
- [ ] **[5.4]** `App\Jobs\GenerateAuthorizationPdf` — Browsershot PDF con QR Code
- [ ] **[5.5]** Client API Protocollo Informatico della Provincia
- [ ] **[5.6]** Firma PAdES remota (Aruba/InfoCert API) apposta dal dirigente
- [ ] **[5.7]** `RipartoService` — riparto forfettario periodici proporzionale per area ISTAT (`ST_Area` proxy)
- [ ] **[5.8]** Dashboard Ragioneria Clearing House — riparto centesimale per ente da `entity_breakdown` (`RouteIntersectionService`); solo enti con `has_financial_delegation=true`
- [ ] **[5.8b]** Export mensile file **XML SEPA** per bonifici cumulativi automatici verso enti deleganti (un record per ente, importo aggregato mensile)
- [ ] **[5.9]** `roadworks`: +`is_public` boolean + checkbox "Visibile mappa pubblica" nel form CRUD
- [ ] **[5.10]** `alert_zones` — import geometrie zone allerta Abruzzo A/B/C per intersect spaziale
- [ ] **[5.11]** `CheckWeatherAlertsJob` schedulato ogni ora — fetch Open Data DPC, sospensione automatica pratiche + `SendWeatherSuspensionNotification`

---

## v0.7.x — M6: Open Data Portal

- [ ] **[6.1]** `GET /mappa-cantieri` pubblica (no auth) — Leaflet CartoDB Positron, filtro `is_public=true`, legenda cromatica per severity
- [ ] **[6.2]** `GET /api/public/roadworks` GeoJSON FeatureCollection (no auth, throttle 60/min)
- [ ] **[6.3]** `GET /api/public/roadworks.geojson` + `.kml` — download file (header `Content-Disposition: attachment`)
- [ ] **[6.4]** `GET /statistiche` pubblica — trasporti per Comune per anno, heatmap strade più usate, tempi medi nulla osta (Chart.js + Leaflet.heat)

---

## v1.0.0 — GA Production

- [ ] Integrazione AINOP/PDND completa (quando API disponibili via PDND)
- [ ] Security audit + penetration test
- [ ] Checklist conformità AgID (accessibilità, design PA)
- [ ] Load testing
- [ ] `publiccode.yml` finale

# GTE-Abruzzo — Todo & Versioning

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

## UI
Fetch this design file, read its readme, and implement the relevant aspects of the design. https://api.anthropic.com/v1/design/h/tD08D-qLpTIhU8271cIKBw?open_file=GTE-Abruzzo.html
Implement: GTE-Abruzzo.html

---

## v0.1.x — Stack Alignment ✅

- [x] Laravel `^11` → `^13.0` (v13.6.0)
- [x] PHP constraint `^8.2` → `^8.4`
- [x] Tailwind v3 → v4 via `@tailwindcss/vite` (rimossi postcss/autoprefixer)
- [x] Alpine.js v3 aggiunto
- [x] `spatie/laravel-permission ^6` aggiunto
- [x] `spatie/browsershot ^5` aggiunto
- [x] Node `20-slim` → `22-slim` in Dockerfile
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
- [ ] **[1.6]** Sincronizzazione Enti via API IPA: Sviluppo Service Open Data IPA e automazione notturna per aggiornamento PEC (Comuni, Province, Forze dell'Ordine)
- [ ] **[1.7]** Validazione Aziende via InfoCamere / INI-PEC (PDND): Integrazione API per autocompilazione da P.IVA e campi sensibili in read-only
- [x] **[1.8]** Setup step 3: tasto "Invia email di test"
---

## v0.3.x — M2: Garage Virtuale + Tariffario

- [x] **[2.1]** Migration `vehicles` — trattori, rimorchi, mezzi d'opera (targa, telaio, massa, dimensioni)
- [x] **[2.2]** Migration `vehicle_axles` — interassi e carico per asse
- [x] **[2.2]** UI: configuratore assi (Alpine.js dinamico)
- [x] **[2.3]** Migration `tariffs` — coefficienti d'usura storicizzati (valid_from / valid_to)
- [x] **[2.3]** `App\Services\WearCalculationService` — formula D.P.R. 495/1992 (peso × km × coeff. per asse)
- [x] **[2.3]** Admin tariffario: CRUD coefficienti con storico versioni

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
- [x] **[3.11]** `gte:import-standard-routes {file} {entity_id}` — import GeoJSON strade standard
- [x] **[3.12]** `VehicleType` enum: +4 tipi agricoli + `isAgricultural()` helper
- [x] **[3.13]** `TipoApplicazioneTariff` enum + `Tariff::scopeByTipoApplicazione()` + `TariffFactory` aggiornata — `WearCalculationService::calculateForApplication(WearContext)` rinviato a v0.5.x (dipende da `TipoIstanza`)

---

## v0.5.x — M4: State Machine + tipo_istanza + Check-in + Radar

- [ ] **[4.1]** Wizard compilazione domanda multi-step: Azienda → Convoglio → Percorso → Riepilogo
- [ ] **[4.2]** Migration `applications` — stato `draft`, `tipo_istanza`, `numero_viaggi`, `valida_da`, `valida_fino`, `selected_entity_ids`, `viaggi_effettuati`, `sospesa_fino`
- [ ] **[4.3]** State machine: transizioni `draft → submitted → waiting_clearances → waiting_payment → approved`
- [ ] **[4.4]** Scrivania Enti Terzi — dashboard ruolo `third-party` (tratta di competenza + Approva/Rifiuta)
- [ ] **[4.5]** Migration `clearances` — Nulla Osta per ente per pratica (`ClearanceStatus` incl. `pre_cleared`)
- [ ] **[4.6]** `App\Jobs\SendClearanceNotification` — PEC asincrono via Redis queue + listener ricezione esiti
- [ ] **[4.7]** Percorsi alternativi OSRM (`alternatives=true`) quando cantiere attivo — cablare nel wizard di submission
- [ ] **[4.8]** `TipoIstanza` enum + `WearContext` value object
- [ ] **[4.9]** `ClearanceStatus` enum con `PreCleared` + `ClearanceDispatchService` (ARS fast-track + PEC dispatch + skip `waiting_clearances`)
- [ ] **[4.10]** `StoreApplicationRequest`: validazione condizionale per `tipo_istanza` (numero_viaggi, valida_da/fino, selected_entity_ids)
- [ ] **[4.11]** `TripStatus` enum + migration `trips` (application_id, driver_user_id, status, started_at, ended_at, geometry_snapshot)
- [ ] **[4.12]** `Trip` model + `TripPolicy` + `CitizenTripController` (start/end/cancel) + middleware anti-sospensione
- [ ] **[4.13]** UI mobile-friendly autista: dashboard autorizzazioni attive + pulsante "INIZIA/CONCLUDI VIAGGIO" + banner sospensione allerta meteo
- [ ] **[4.14]** `RadarController` (`law-enforcement`) + `GET /api/law-enforcement/active-trips`
- [ ] **[4.15]** Leaflet Radar: mappa real-time convogli in transito, polling 30s, popup targa/tipo/ora

---

## v0.6.x — M5: Pagamenti + Rilascio Legale + Allerta Meteo

- [ ] **[5.1]** PagoPA — generazione IUV da `WearCalculationService::calculateForApplication()` (analitico / forfettario)
- [ ] **[5.2]** Webhook RT (Ricevuta Telematica) → transizione `waiting_payment → approved`
- [ ] **[5.3]** Vista Blade layout ufficiale autorizzazione (mini-mappa + tabelle tecniche)
- [ ] **[5.4]** `App\Jobs\GenerateAuthorizationPdf` — Browsershot PDF con QR Code
- [ ] **[5.5]** Client API Protocollo Informatico della Provincia
- [ ] **[5.6]** Firma PAdES remota (Aruba/InfoCert API) apposta dal dirigente
- [ ] **[5.7]** `RipartoService` — riparto forfettario periodici proporzionale per area ISTAT (`ST_Area` proxy)
- [ ] **[5.8]** Dashboard Ragioneria — export CSV/Excel riparto fondi per ente (usa `RipartoService`)
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

# GTE-Abruzzo — Todo & Versioning

## Schema versioni

| Versione | Milestone | Contenuto |
|----------|-----------|-----------|
| **v0.1.x** | Stack | Aggiornamento dipendenze, Docker, tooling |
| **v0.2.x** | M1 | Identity (SPID/CIE), RBAC, Anagrafiche |
| **v0.3.x** | M2 | Garage Virtuale + WearCalculationService |
| **v0.4.x** | M3 | WebGIS + OSRM + Intersezione Spaziale |
| **v0.5.x** | M4 | State Machine + Scrivania Enti Terzi + PEC async |
| **v0.6.x** | M5 | PagoPA + PDF + Firma PAdES + Protocollo |
| **v1.0.0** | GA | AINOP/PDND, security audit, AgID compliance |

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
- [x] `.env.example` aggiornato (SPID, PagoPA, OSRM, AINOP/PDND)
- [x] `publiccode.yml` aggiornato a v0.1.0
- [x] Tag `v0.1.0` rilasciato

---

## v0.2.x — M1: Identity, RBAC, Anagrafiche

- [ ] **[1.1]** Migration `users` — identità fiscale SPID/CIE (codice fiscale, dati IDP)
- [ ] **[1.1]** Integrazione SPID/CIE via Socialite + `socialiteproviders/spid`
- [ ] **[1.2]** Setup Spatie Permission — seed ruoli: `super-admin`, `operator`, `third-party`, `citizen`, `law-enforcement`
- [ ] **[1.3]** Migration `companies` + pivot `company_user` (deleghe/procure)
- [ ] **[1.3]** UI: richiesta e approvazione deleghe aziendali
- [ ] **[1.4]** Migration `entities` — Comuni, Province, ANAS, Autostrade (GIS `MULTIPOLYGON`, PEC, ISTAT)
- [ ] **[1.4]** CRUD entità amministrative (solo `super-admin`)

---

## v0.3.x — M2: Garage Virtuale + Tariffario

- [ ] **[2.1]** Migration `vehicles` — trattori, rimorchi, mezzi d'opera (targa, telaio, massa, dimensioni)
- [ ] **[2.2]** Migration `vehicle_axles` — interassi e carico per asse
- [ ] **[2.2]** UI: configuratore assi (Alpine.js dinamico)
- [ ] **[2.3]** Migration `tariffs` — coefficienti d'usura storicizzati (valid_from / valid_to)
- [ ] **[2.3]** `App\Services\WearCalculationService` — formula D.P.R. 495/1992 (peso × km × coeff. per asse)
- [ ] **[2.3]** Admin tariffario: CRUD coefficienti con storico versioni

---

## v0.4.x — M3: WebGIS + OSRM + Intersezione Spaziale

- [ ] **[3.1]** Import shapefile/GeoJSON confini comunali e provinciali Abruzzo → `entities.geom`
- [ ] **[3.2]** Frontend Leaflet — mappa interattiva per tracciamento percorso
- [ ] **[3.2]** Integrazione API OSRM snap-to-road → salvataggio `LineString` in `routes.geometry`
- [ ] **[3.3]** Query `ST_Intersection` + `ST_Length` → km per ente estratti automaticamente
- [ ] **[3.4]** Stub AINOP/PDND: campo `codice_univoco_ainop` su tabella infrastrutture
- [ ] **[3.4]** Evidenziazione WebGIS corridoi nazionali MIT idonei
- [ ] **[3.5]** Migration `roadworks` — `entity_id`, `geometry` (LINESTRING/POLYGON), `valid_from`, `valid_to`, `severity` (advisory/restricted/closed), `status` (planned/active/closed)
- [ ] **[3.5]** Controllo cantieri al submit: `ST_Intersects(route, roadwork)` + overlap date range → blocco con indicazione tratto
- [ ] **[3.5]** Percorsi alternativi OSRM (`alternatives=true`) quando il percorso confligge con un cantiere attivo

---

## v0.5.x — M4: State Machine + Workflow

- [ ] **[4.1]** Wizard compilazione domanda multi-step: Azienda → Convoglio → Percorso → Riepilogo
- [ ] **[4.1]** Migration `applications` + `routes` — salvataggio pratica in stato `draft`
- [ ] **[4.2]** State machine: transizioni `draft → submitted → waiting_clearances`
- [ ] **[4.3]** Scrivania Enti Terzi — dashboard ruolo `third-party` (tratta di competenza + Approva/Rifiuta)
- [ ] **[4.3]** Migration `clearances` — Nulla Osta per ente per pratica
- [ ] **[4.3]** Gestione cantieri nella Scrivania Enti Terzi — CRUD `roadworks` per l'ente di competenza
- [ ] **[4.4]** `App\Jobs\SendClearanceNotification` — PEC asincrono via Redis queue
- [ ] **[4.4]** Listener ricezione esiti PEC

---

## v0.6.x — M5: Pagamenti + Rilascio Legale

- [ ] **[5.1]** PagoPA — generazione IUV da output `WearCalculationService`
- [ ] **[5.1]** Webhook RT (Ricevuta Telematica) → transizione `waiting_payment → approved`
- [ ] **[5.2]** Vista Blade layout ufficiale autorizzazione (mini-mappa + tabelle tecniche)
- [ ] **[5.2]** `App\Jobs\GenerateAuthorizationPdf` — Browsershot PDF con QR Code
- [ ] **[5.3]** Client API Protocollo Informatico della Provincia
- [ ] **[5.3]** Firma PAdES remota (Aruba/InfoCert API) apposta dal dirigente
- [ ] **[5.4]** Dashboard Ragioneria — export CSV/Excel riparto fondi per ente
- [ ] **[5.5]** Dashboard Forze dell'Ordine (`law-enforcement`) — verifica per targa, scansione QR, mappa cantieri attivi, trasporti in transito oggi
- [ ] **[5.5]** Vista mobile-first per utilizzo su strada

---

## v1.0.0 — GA Production

- [ ] Integrazione AINOP/PDND completa (quando API disponibili via PDND)
- [ ] Security audit + penetration test
- [ ] Checklist conformità AgID (accessibilità, design PA)
- [ ] Load testing
- [ ] `publiccode.yml` finale

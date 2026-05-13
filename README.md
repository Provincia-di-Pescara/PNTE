# PNTE

![PNTE Logo](./design/logo-icon.png)

PNTE adotta un'architettura "Walled Garden Ibrido" che risolve il problema della scalabilità nazionale garantendo al contempo il controllo della Provincia Capofila.

## Progetto di Trasformazione Digitale per la Provincia di Pescara e il riuso nazionale

> Software a riuso sviluppato dalla **Provincia di Pescara** ai sensi dell'art. 69 del D.Lgs. 82/2005 (CAD) e pubblicato sul catalogo [Developers Italia](https://developers.italia.it/). Licenza **EUPL-1.2**.

---

### 1. Premessa e Visione

Il progetto **PNTE** (Piattaforma Nazionale Trasporti Eccezionali) nasce per digitalizzare l'intero ciclo di vita delle autorizzazioni per il transito di veicoli e trasporti eccezionali. Il software è sviluppato dalla **Provincia di Pescara** in qualità di ente capofila, con l'obiettivo di essere messo a disposizione di tutti gli enti della Regione Abruzzo e del territorio nazionale tramite il catalogo del **Riuso di Developers Italia**.

L'architettura è concepita come un **SaaS (Software as a Service) Multi-tenant**, dove ogni ente (Province, Comuni, ANAS) può gestire le proprie competenze in un ambiente isolato ma integrato in un unico flusso geografico e amministrativo.

---

### 2. Architettura Multi-Tenant: Il Walled Garden Ibrido

PNTE adotta un'architettura "Walled Garden Ibrido" che risolve il problema della scalabilità nazionale garantendo al contempo il controllo della Provincia Capofila.

**Cartografia Nazionale, Azione Locale:** Il database contiene i confini ISTAT e la rete stradale dell'intera Italia, ma il sistema opera attivamente soltanto per le **Province Tenant** — quelle che hanno aderito formalmente alla piattaforma. Questo permette sin dal primo giorno di tracciare percorsi interregionali senza riconfigurare la cartografia a ogni nuova adesione.

**Calcolo Universale, Incasso Selettivo:** Il motore di calcolo dell'usura stradale elabora le ripartizioni economiche per l'intero percorso, includendo tutti gli enti attraversati, indipendentemente dal loro stato di adesione. Il flusso finanziario (IUV PagoPA, Clearing House) viene attivato soltanto per la quota di competenza degli enti con `has_financial_delegation = true` all'interno del perimetro Tenant.

**Gestione degli Enti Esterni (non-tenant):** Quando il percorso attraversa un ente non censito, la piattaforma — agendo da **Ente Unico Rilasciante** — invia automaticamente la PEC di richiesta Nulla Osta. Il testo della PEC include un paragrafo che illustra i benefici dell'adesione alla piattaforma e le istruzioni per censirsi. La regolarizzazione finanziaria verso questi enti resta a carico dell'utente richiedente, con importo e riferimenti mostrati a schermo.

| Tratta del percorso | Parere (Nulla Osta) | Flusso finanziario |
| :--- | :--- | :--- |
| Ente con `is_tenant = true` | Dashboard in piattaforma + PEC di avviso | Incluso nell'IUV se `has_financial_delegation = true` |
| Ente con `is_tenant = false` | PEC automatica → operatore Provincia sblocca manualmente | Incluso nell'IUV se `has_financial_delegation = true`; altrimenti scorporato con avviso all'utente |
| Ente non registrato in piattaforma | PEC automatica con invito a censirsi | Scorporato; utente avvisato di regolarizzare direttamente |

---

### 3. Gerarchia RBAC e Isolamento dei Dati

La vetta della piramide dei permessi è sdoppiata in due binari paralleli e non comunicanti, progettati per separare in modo netto infrastruttura e procedimenti amministrativi.

**Il `system-admin` (Gestore della Piattaforma):**
È il team IT che installa, configura e mantiene il sistema. Non ha binding con record in `entities`, `companies`, `agency_mandates` o `delegations`. Accede soltanto al pannello `/system` e non può entrare nel merito delle pratiche. Le rotte business (`/pratiche`, `/pagamenti`, `/nulla-osta`) richiedono obbligatoriamente un titolo attivo verso l'oggetto di business: per gli Enti basta una `delegation`; per i rapporti Agenzia -> Ditta serve un `agency_mandate` attivo e una `delegation` utente coerente. Poiché il `system-admin` è un utente sganciato, non può accedere a quelle sezioni.

**L'`admin-ente` (Gestore del Business):**
È il Dirigente o il Responsabile dell'Ente. Gestisce operatori, deleghe, pratiche, pagamenti e pareri nel proprio perimetro. Se l'ente ha `is_capofila = true`, l'`admin-ente` eredita poteri estesi: approvazione dei fallback, report aggregati, coordinamento dell'ecosistema tenant.

**La gerarchia completa:**

| Ruolo | Binding | Accesso dati pratiche |
| :--- | :--- | :--- |
| `system-admin` | Nessuno | Nessun accesso ai dati di business; solo `/system` |
| `admin-ente` | `delegations` -> `Entity` | Completo sul proprio ente |
| `operator` | `delegations` -> `Entity` | Operativita sul proprio ente |
| `admin-azienda` | `delegations` -> `Company` | Completo sulla propria ditta |
| `agency` | `agency_mandates` -> `delegations` -> `Company` | Opera per clienti multipli con contesto partner esplicito |
| `citizen` | `delegations` -> `Company` | Inserimento pratiche per la propria ditta |
| `third-party` | `delegations` -> `Entity` | Nulla Osta e cantieri di competenza |
| `law-enforcement` | Nessuno (read-only) | Solo pratiche `approved` |
| Utente nudo | Nessuno | Solo wizard richiesta delega |

---

### 4. Identity Zero-Trust e Onboarding (Delega a Cascata)

Al primo accesso tramite SPID o CIE, l'utente è "nudo": autenticato ma senza alcun potere operativo. I poteri amministrativi nascono da due fonti distinte: verifica runtime sui registri pubblici per i titolari effettivi, e catena interna `agency_mandates + delegations` per utenti delegati e agenzie.

**1. Titolare Effettivo e Fast-Track runtime:**
Si attiva se l'utente accede con **SPID Professionale** oppure se il suo Codice Fiscale coincide con quello del **Legale Rappresentante** estratto in tempo reale dal Registro Imprese tramite **PDND/InfoCamere**. In entrambi i casi il potere come `admin-azienda` viene riconosciuto istantaneamente, ma la source of truth resta esterna: al login successivo il controllo viene ripetuto e il titolo si adegua automaticamente se il legale rappresentante è cambiato.

**2. Auto-Classificazione Agenzia via ATECO (Legge 264/1991):**
La piattaforma rileva automaticamente se un'azienda è un'agenzia di pratiche auto tramite il **Codice ATECO** estratto dal Registro Imprese via PDND. Il setaccio è a due livelli:
- **Filtro 1 - Codice ATECO:** Il codice target è **82.99.11** (Fornitura di assistenza per la registrazione di autoveicoli).
- **Filtro 2 - Compliance Legge 264/1991:** La descrizione dell'attività (`descrizione_attivita`) deve contenere almeno una keyword: "Consulenza", "Legge 264", "Agenzia di pratiche", "Studi di consulenza".
- **Attivazione:** Al primo login tramite SPID/CIE, se ATECO 82.99.11 + keyword compliance matchano, il sistema auto-flag `companies.is_agency = true` senza richiedere conferma operatore.
- **Monitoraggio Continuo:** Un job Artisan `pnte:re-sync-agency-ateco` eseguito monthly interroga PDND per tutte le aziende flaggate come agenzie. Se ATECO cambia o P.IVA diventa inattiva, tutti gli `agency_mandates` attivi verso quella agenzia vengono revocati automaticamente (`status = 'revoked'`, `revocation_reason = 'ATECO compliance check: agency status revoked'`). L'agenzia riceve una PEC di notifica.
- **Audit:** Campi `ateco_code` (string) e `ateco_last_synced_at` (timestamp) aggiunti a `companies` per tracciabilità compliance.

**3. Agenzie di Consulenza: doppio livello `agency_mandates + delegations`:**
L'agenzia è trattata come una ditta specializzata (`companies.is_agency = true`). Il titolare entra con SPID Professionale o check PDND e viene auto-approvato come `admin-azienda` della propria Agenzia. Da quel momento il collegamento Agenzia -> Cliente non vive piu dentro una semplice delega utente, ma in un aggregate dedicato: `agency_mandates`.
- **Scenario A - Ditta già digitale:** l'Agenzia richiede il mandato via piattaforma; il titolare della Ditta riceve notifica e approva con un click; il sistema crea un `agency_mandate` attivo tra Ditta e Agenzia.
- **Scenario B - Ditta analogica:** l'Agenzia sceglie la data di validità del mandato; il sistema genera una **Procura Speciale** in PDF con dati camerali ufficiali e con quella scadenza già scritta nel documento; il legale rappresentante firma offline il `.p7m`; il motore di validazione verifica integrità, certificato e corrispondenza del CF firmatario con il Registro Imprese, attivando automaticamente l'`agency_mandate` se il match è positivo.

**3. Regola legale della durata:**
Per agenzie e procure speciali, `agency_mandates.valid_until` non può mai superare la scadenza del documento firmato caricato. La data scelta dall'Agenzia nel flusso di generazione PDF diventa quindi la source of truth temporale del rapporto Ditta -> Agenzia. Le `delegations` operative degli utenti dell'Agenzia non possono eccedere il mandato partner padre.

**4. Gestione Partner, rinnovo e kill-switch:**
La Ditta vede un cruscotto centrale **Gestione Partner** con tutte le Agenzie abilitate: stato, scadenza, ultimo accesso, volume pratiche, ambito operativo e azioni rapide. Il sistema invia notifiche proattive a **T-30** e **T-7** sia alla Ditta mandante sia all'Agenzia. Se le condizioni non cambiano, il rinnovo è semplificato con conferma in portale; se cambiano poteri, soggetti o durata legale, serve una nuova firma P7M. La Ditta può sospendere o revocare istantaneamente una sola Agenzia senza toccare le altre: il kill-switch agisce sull'`agency_mandate` e blocca ogni nuova pratica dal secondo successivo.

**5. Flusso P7M per il primo `admin-ente`:**
Per il primo amministratore di qualsiasi Ente (Comune, Provincia, Capofila) il sistema genera un PDF formale di nomina precompilato. Il documento viene firmato digitalmente dal Dirigente o Legale Rappresentante e ricaricato sul portale come `.p7m` o PDF PAdES. Lo stesso motore di validazione controlla integrità del documento, validità del certificato e corrispondenza del firmatario con il soggetto titolato censito nelle banche dati pubbliche. Per il primo onboarding della Capofila, il sistema auto-approva il documento se l'ente ha `is_capofila = true`.

**6. Delega a Cascata per il personale ordinario:**
Una volta validato il primo amministratore, gli accessi ordinari dei dipendenti tornano snelli: richiesta in piattaforma, notifica al relativo `admin-ente` o `admin-azienda`, approvazione con un click. Per il personale di una Agenzia, la delega utente è sempre subordinata a un `agency_mandate` attivo verso la Ditta cliente. L'OTP via PEC IPA resta disponibile per gli operatori degli Enti pubblici come canale out-of-band rapido, ma non sostituisce il P7M per la prima nomina dell'amministratore.

**7. Fallback Manuale:**
Le richieste che non possono essere validate automaticamente vengono instradate all'`admin-ente` della Provincia Tenant di appartenenza; se l'ente non è tenant, ricadono sulla Capofila.

---

### 5. Il Pannello `/system`

Il `system-admin` non atterra sulla dashboard classica ma su un pannello separato, protetto da middleware dedicato. Il pannello espone soltanto funzioni infrastrutturali:

**Gestione Connettori (Vault):** certificati X.509 per PDND, client OIDC SPID/CIE, API key PagoPA.

**Master Settings:** SMTP/IMAP della PEC madre, task schedulati, sincronizzazioni manuali, abilitazione nuovi tenant.

**Cruscotto Telemetrico:** metriche aggregate e anonimizzate, senza nomi, targhe, P.IVA o PDF pratiche.

**Zero-Touch Provisioning:** il `system-admin` installa, collega le API, esegue il seeder nazionale, abilita la Capofila (`is_tenant = true`, `is_capofila = true`) e termina il proprio intervento. L'onboarding dei tenant e dei loro amministratori avviene poi interamente via SPID, PDND, IPA e flusso P7M.

---

### 6. Conformità AgID e Principi EIF

Il software è progettato per rispettare le **Linee Guida AgID** e il **European Interoperability Framework (EIF)**. I seguenti principi guidano ogni scelta architetturale e implementativa:

| Principio | Applicazione in PNTE |
| :--- | :--- |
| **Once-Only** | I dati disponibili da banche dati nazionali (attributi SPID, IPA, Registro Imprese PDND) non vengono mai richiesti manualmente all'utente. I campi pre-compilati da API sono in sola lettura. |
| **Digital-by-Default** | Ogni processo burocratico è integralmente eseguibile online. Non esistono fallback cartacei né procedure "scarica il PDF e mandalo via email". |
| **Privacy-by-Design** | Raccolta del dato minimo necessario (art. 25 GDPR). Nessun dato personale in log applicativi. `codice_fiscale` cifrato at-rest. Anonimizzazione pianificata per le pratiche archiviate. |
| **Accessibilità (WCAG 2.1 AA)** | UI costruita sul Design System PA. Attributi `aria-*` su tutti i componenti interattivi. Contrasti conformi alle linee guida. Test con screen reader prima di ogni rilascio. |
| **Interoperabilità (EIF/ModI)** | Tutti gli endpoint pubblici documentati con **OpenAPI 3.0**. Versionamento API in URL (`/api/v1/`). Output geografici in formato **GeoJSON** (RFC 7946). |
| **Open-Source by Default** | Licenza **EUPL-1.2**. Ogni nuova dipendenza verificata su Developers Italia prima di aggiungere un vendor privato. `publiccode.yml` aggiornato a ogni milestone. |
| **Cloud-Native** | Container stateless: nessun dato persistente nel container `app`. Storage su driver filesystem configurabile (S3-compatible). Le migrazioni sono eseguite automaticamente all'avvio via `entrypoint.sh`. |
| **Zero-Trust** | Policy Laravel su ogni Model. Rate-limiting su tutti gli endpoint pubblici e API. Validazione input via Form Request dedicate. Le rotte business richiedono sempre una `delegation` attiva verso l'oggetto di business. |
| **Tenant Guard** | Le operazioni finanziarie e di governance estesa possono partire solo se l'ente di partenza è tenant e, dove richiesto, capofila. |
| **Riuso-prima-di-costruire** | Prima di implementare un'integrazione PA, si verifica l'esistenza di client ufficiali su Developers Italia o AgID. |

---

### 7. Riferimenti Normativi

Il software è costruito per far rispettare i rigidi vincoli del Codice della Strada:

- **Art. 10 del Codice della Strada (D.Lgs 285/1992):** Requisiti tecnici e amministrativi per i trasporti eccezionali.
- **Art. 9-20 del Regolamento di Attuazione (D.P.R. 495/1992):** Formule di calcolo per l'indennizzo d'usura stradale.
- **CAD (D.Lgs 82/2005):** Art. 69 — obbligo di riuso del software sviluppato dalla PA; art. 50-ter interoperabilità.
- **Linee Guida AgID:** Standard per il riuso, l'accessibilità e l'interoperabilità del software nella PA (vedi §4).
- **GDPR (Reg. UE 2016/679):** Privacy-by-Design, dato minimo, diritto all'oblio.
- **Direttive MIT:** Gestione della sicurezza dei ponti e dei manufatti stradali (vedi §17).

---

### 8. Stack Tecnologico

La scelta delle tecnologie mira alla massima modernità e stabilità (Long Term Support):

| Layer | Tecnologia | Versione | Ruolo |
| :--- | :--- | :--- | :--- |
| **Framework** | Laravel | 13.x | Logica di business, Eloquent ORM, API Gateway |
| **Linguaggio** | PHP (PHP-FPM) | 8.4 | Performance elevate e tipizzazione forte |
| **Web Server** | Nginx | stable | Gestione richieste e security hardening |
| **Database** | PostgreSQL + PostGIS | 16 / 3.4 | RDBMS geospaziale con GiST index e funzioni GIS avanzate |
| **Cache/Queue** | Redis | 7.x | Gestione code asincrone (PEC, PDF, Pagamenti) |
| **Frontend** | Tailwind CSS v4 + Alpine.js | v4 / v3 | UI zero-runtime, reattiva e conforme alle linee guida design PA |
| **Build Tool** | Vite | 6.x | HMR in dev, bundle ottimizzato in prod |
| **RBAC** | Spatie Laravel Permission | 6.x | Ruoli e permessi granulari (`system-admin`, `admin-ente`, `agency`, `third-party`, `citizen`) |
| **Routing GIS** | OSRM | latest | Motore di routing self-hosted per il calcolo dei percorsi |
| **PDF Engine** | Browsershot (spatie/browsershot) | 5.x | Generazione PDF via Chromium per layout complessi |
| **Container** | Docker + Docker Compose | latest | Pacchettizzazione completa per il rilascio a riuso |
| **Node.js** | Node.js LTS | 22.x | Runtime per build assets frontend |

---

### 9. Architettura dei Servizi (Docker Compose)

Il sistema è distribuito tramite una flotta di container cooperanti:

- `app`: Il cuore del sistema in Laravel (PHP-FPM + Nginx nello stesso container). Include Chromium per la generazione PDF via Browsershot.
- `db`: PostgreSQL 16 con estensione PostGIS 3.4 per storage e analisi spaziale nazionale.
- `redis`: Gestore delle code per l'invio delle notifiche, l'elaborazione dei pareri e il Listener IMAP.
- `osrm`: Motore di routing caricato con il grafo stradale regionale abruzzese (attivato con `--profile gis`).

---

### 10. Motore WebGIS e Calcolo Indennizzi

Il cuore innovativo del sistema è il motore geografico: non è semplicemente una mappa visiva, ma il **motore di calcolo legale ed economico** della pratica.

**Routing Snap-to-Road:** Grazie a **OSRM**, l'utente inserisce i punti di partenza/arrivo e la linea del percorso viene automaticamente ancorata alle strade reali, calcolando i chilometri precisi su ogni tratta.

**Intersezione Spaziale:** Il backend incrocia la `LineString` del percorso con i poligoni dei confini comunali e provinciali (ISTAT, intera Italia) tramite `ST_Intersects` + `ST_LENGTH`. Il risultato è un `entity_breakdown`: per ogni ente attraversato, i chilometri esatti di competenza, calcolati in millisecondi.

**Motore di Calcolo (D.P.R. 495/1992):** Un rule-engine basato su tabelle di coefficienti storicizzate calcola l'indennizzo d'usura totale e la quota spettante a ogni ente (formula: peso × km × coefficiente-asse), in conformità alle formule ministeriali.

**ARS — Tratti Verdi e Tratti Rossi:** Il percorso viene confrontato con l'**Archivio Regionale Strade (ARS)**, il catalogo delle strade pre-approvate per il transito eccezionale (tipicamente percorsi agricoli ricorrenti). Il `StandardRouteOverlayService` analizza la sovrapposizione con buffer geometrico (~11 m):
- **Tratti Verdi** (strade ARS): la clearance è `pre_cleared`; l'ente competente non riceve richiesta di Nulla Osta — il transito è autorizzato in Fast-Track.
- **Tratti Rossi** (strade fuori ARS): si avvia l'iter ordinario con richiesta di Nulla Osta all'ente di competenza.

**Trasporti Periodici:** Per le autorizzazioni con `tipo_istanza = periodico`, l'utente seleziona un'**area geografica** (poligono ISTAT) anziché una linea. Il conteggio chilometrico viene sospeso e il sistema applica le **tariffe forfettarie ministeriali**, come previsto per i trasporti agricoli ricorrenti.

PNTE adotta la logica di business corretta e scarta il vincolo dell'inserimento manuale di nodi chilometrici: l'utente disegna sulla mappa come in un navigatore consumer e il backend calcola automaticamente intersezioni, km e competenze.

---

### 10.5 Fondamenta Geografiche: Dati Spaziali, GeoJSON e Interoperabilità

La piattaforma non gestisce geometrie come feature opzionale, ma come **fondamento architetturale** di ogni operazione. Il data layer geografico è il sistema nervoso del software: ogni decisione (calcolo economico, approvazione, validazione di percorsi) passa per operazioni spaziali.

#### Coordinate e Sistemi di Riferimento (SRID 4326 / WGS84)

Tutte le geometrie sono archiviate in **SRID 4326 (WGS84)**: coordinate in `[longitude, latitude]` (precisione a metri). PostgreSQL con PostGIS gestisce nativamente `spatial_ref_sys` e le operazioni geografiche avanzate su SRID 4326.

**Conversione distanze:** `ST_LENGTH()` su SRID 4326 ritorna distanze in gradi decimali. Per le coordinate di Abruzzo (41–42°N), il fattore di conversione è **111.32 km/°**, con errore relativo < 2%. Tutte le distanze chilometriche nel sistema usano questo fattore; le rotte con lunghezza > 300 km in direzione Nord-Sud sono suddivise per minimizzare l'errore.

**Spatial Index (Performance):** Tutte le colonne geometriche hanno indice `GiST` su PostGIS. Le query `ST_INTERSECTS` su interi dataset di entità (300+ poligoni) eseguono in millisecondi.

#### Colonne Geometriche (Database Schema)

| Tabella | Colonna | Tipo | Indice | Uso |
| :--- | :--- | :--- | :--- | :--- |
| `entities` | `geom` | POLYGON / MULTIPOLYGON | SPATIAL | Confini Comuni, Province, ANAS; aggiornati via import GeoJSON annuale |
| `routes` | `geometry` | LINESTRING | SPATIAL | Percorso tracciato dall'utente dopo snap-to-road OSRM |
| `roadworks` | `geometry` | LINESTRING / POLYGON | SPATIAL | Cantieri segnalati dagli enti gestori |
| `standard_routes` | `geometry` | LINESTRING | SPATIAL | ARS — strade pre-approvate per Fast-Track |

#### GeoJSON come Standard di Interoperabilità (RFC 7946)

**Principio First-Class:** GeoJSON non è un formato di export, ma l'interfaccia primaria per l'interscambio di dati geografici. Tutti gli endpoint pubblici che ritornano dati geografici usano RFC 7946 FeatureCollections.

**Endpoint API Geografici:**
- `GET /api/v1/routes/{id}/geojson` — Ritorna la route come Feature con LineString geometry + `entity_breakdown` metadata e calcolo indennizzi.
- `GET /api/v1/entities/{id}/boundary` — Confine dell'ente come FeatureCollection (POLYGON).
- `GET /api/v1/routes/{id}/conflicts` — Cantieri in conflitto con il percorso come FeatureCollection.
- `GET /api/v1/routes/{id}/export?format=geojson|kml` — Export completo per third-party systems.

**Documentazione OpenAPI 3.0:** Tutti gli endpoint geografici sono documentati con schema GeoJSON esplicito (RFC 7946 FeatureCollection o Feature).

#### Operazioni Spaziali Core (Backend)

Il backend utilizza natively le funzioni spaziali di PostGIS per ogni operazione critica:

- **`ST_INTERSECTS(route_geom, entity_geom)`** — Identifica tutti gli enti attraversati dal percorso in una singola query. Utilizzato per il calcolo dell'`entity_breakdown`.
- **`ST_LENGTH(route_geom)`** — Calcola la lunghezza della rotta in gradi; moltiplicato per 111.32 per ottenere km. Utilizzato per il calcolo dell'usura e delle tariffe.
- **`ST_BUFFER(point, radius_degrees)`** — Buffer di sicurezza intorno a punti di interesse (es., ~11 m = 0.0001° per il matching ARS). Usato per identificare "Tratti Verdi" con tolleranza geometrica.
- **`ST_AS_TEXT(geometry)`** — Esporta geometria in WKT per le query OSRM.
- **`ST_GEOMFROMGEOJSON(geojson_str, 4326)`** — Importa GeoJSON dal frontend Leaflet draw.
- **`ST_SIMPLIFY(geometry, tolerance)`** — Riduce vertici per il rendering frontend.

**Caching in Redis:** Query spaziali frequenti cachate con TTL 30 minuti, chiave = hash(route_geometry). Invalidazione: aggiornamento `entities.geom` o `roadworks.geometry`.

#### Import / Export Geografico

**Import da GeoJSON:**
```bash
php artisan pnte:import-geo /data/regione-abruzzo.geojson --upsert-by=codice_istat
```

**Import da Shapefile:**
```bash
ogr2ogr -f GeoJSON output.geojson comuni.shp
php artisan pnte:import-geo output.geojson
```

**Export Route / Entities:**
```bash
GET /api/v1/routes/{id}/export?format=geojson
GET /api/v1/open-data/entities/geojson  # Milestone 7 Open Data
```

#### Conformità Privacy e Security

- **Geometrie Pubbliche vs Private:** Confini ISTAT pubblici. Route geometrie scoped: utenti autorizzati + law-enforcement (read-only). Route archiviate hanno geometry = NULL.
- **GIS Privacy:** Nessun PII in properties (nomi, targhe, P.IVA). Metadata in tabelle separate con RBAC.

---

### 11. Flusso Burocratico e Automazione PEC

Il sistema automatizza il ciclo di vita dei Nulla Osta garantendo la supervisione umana nelle fasi critiche.

**PEC in uscita (asincrona):** Al cambio di stato `submitted → waiting_clearances`, un job Redis invia in background le richieste di Nulla Osta via PEC a tutti gli enti di competenza. Per gli enti non censiti in piattaforma, il testo include l'invito a registrarsi.

**IMAP Listener (PEC in ingresso):** Uno scheduler che gira periodicamente legge la casella PEC istituzionale della Provincia tramite IMAP. Quando riconosce l'ID pratica nell'oggetto della mail:
1. Scarica il PDF allegato e lo associa al fascicolo digitale.
2. **Non approva automaticamente** il Nulla Osta: imposta lo stato della clearance su `pending_review` ("Da Valutare" — semaforo giallo).
3. L'operatore della Provincia legge il documento e clicca manualmente "Approva" o "Rifiuta" con motivazione.

Questo meccanismo azzera il rischio di **falsi positivi** (approvazione automatica di un parere negativo o condizionato non riconosciuto dalla macchina).

**Enti con `is_tenant = true`:** Ricevono comunque la PEC di avviso, ma l'operatore dell'ente gestisce il parere direttamente dalla propria Scrivania in piattaforma, senza che la Provincia debba intervenire.

---

### 12. Modello Tenant per gli Enti: `is_tenant`, `has_financial_delegation`, `is_capofila`

La tabella `entities` espone tre flag booleani, indipendenti e ortogonali, che la Provincia Tenant configura ente per ente. Il flag `has_financial_delegation` può essere gestito in autonomia dall'ente stesso dalla propria dashboard.

#### `is_tenant` — L'ente è censito in piattaforma?

| Valore | Comportamento |
| :--- | :--- |
| `true` | L'operatore dell'ente accede con SPID/CIE e gestisce i pareri dalla propria Scrivania. Il sistema invia comunque una PEC di avviso che è richiesta l'approvazione in piattaforma. |
| `false` *(default)* | Il sistema invia la PEC automatica. L'operatore della Provincia sblocca manualmente la clearance alla ricezione della risposta (tramite IMAP Listener o comunicazione diretta). |

#### `has_financial_delegation` — L'ente ha autorizzato la Provincia a incassare per lui?

| Valore | Comportamento |
| :--- | :--- |
| `true` | La quota di usura stradale dell'ente viene **sommata all'IUV PagoPA** emesso dalla Provincia. A fine mese, il modulo Clearing House calcola il riparto e genera il file XML SEPA per il bonifico automatico all'ente. |
| `false` *(default)* | La quota dell'ente viene **scorporata** dall'IUV. Il sistema genera il PagoPA solo per la quota Provincia e avvisa l'utente: *"Devi regolarizzare separatamente la quota di € X,XX con il Comune di Y tramite i loro canali."* |

#### `is_capofila` — L'ente è la Capofila del sistema?

| Valore | Comportamento |
| :--- | :--- |
| `true` | Sblocca governance estesa: onboarding iniziale del tenant, approvazione fallback, report aggregati, coordinamento ecosistema. |
| `false` *(default)* | L'ente opera nel proprio perimetro senza poteri di governance trasversale. |

**Onboarding delegazione finanziaria:** Al primo accesso dell'ente (ruolo `third-party`), viene mostrato un modal che illustra i vantaggi dell'attivazione (il trasportatore paga in un'unica soluzione; l'ente non deve gestire incassi). Il valore di default è **OFF**, ma l'attivazione è incoraggiata con spiegazione dei benefici.

**Grace period:** Una volta attivata, anche in caso di successiva disattivazione, il flag resta attivo fino alla **mezzanotte del giorno corrente**. Questo garantisce che le pratiche già in stato `waiting_payment` nella giornata vengano correttamente incluse nell'IUV consolidato, evitando inconsistenze contabili.

---

### 13. Pagamenti e Clearing House (PagoPA)

Il limite di 5 co-beneficiari del nodo PagoPA viene aggirato per non bloccare le pratiche con molti enti attraversati.

**Incasso Centralizzato:** Il sistema genera un unico **IUV PagoPA mono-beneficiario** intestato alla Provincia Capofila. L'importo è la somma delle quote di tutti gli enti con `has_financial_delegation = true` più la quota Provincia. Il trasportatore effettua un unico pagamento e sblocca la pratica.

**Sblocco Automatico:** Alla ricezione della **RT (Ricevuta Telematica)** via webhook PagoPA, il sistema transita automaticamente `waiting_payment → approved` e avvia la generazione del PDF dell'autorizzazione.

**IUV condizionato al Tenant:** Il pagamento PagoPA viene generato **solo se la Provincia di partenza del percorso è un Tenant attivo** sulla piattaforma. In caso contrario, il sistema non può fungere da stazione appaltante e ne informa l'utente con le istruzioni per procedere sui canali dell'ente competente.

**Modulo Clearing House:** Una dashboard dedicata alla Ragioneria mostra, per ogni mese, il riparto finanziario dovuto a ciascun ente delegante. Il calcolo è alimentato direttamente dall'`entity_breakdown` prodotto dal `RouteIntersectionService` (dati WebGIS), garantendo una ripartizione al centesimo. A fine mese, il software genera un **file XML SEPA** per predisporre i bonifici cumulativi automatici tramite l'home banking dell'Ente, con un'unica disposizione per tutti gli enti deleganti.

---

### 14. Integrazione AINOP (Archivio Informatico Nazionale delle Opere Pubbliche)

Il sistema è architetturalmente predisposto per la piena interoperabilità con **AINOP** tramite la **Piattaforma Digitale Nazionale Dati (PDND)**.

Quando le API AINOP saranno disponibili in produzione, PNTE potrà:

- **Verificare in tempo reale** la portata e le limitazioni di ponti e viadotti presenti lungo il percorso autorizzativo, interrogando il dataset AINOP tramite le API PDND.
- **Collegare automaticamente** le opere d'arte censite a sistema (tramite coordinate geografiche o identificativi univoci `codice_univoco_ainop`) alle corrispondenti schede AINOP.
- **Esporre nel WebGIS** lo stato di idoneità infrastrutturale dei tratti percorsi, con evidenza visiva delle limitazioni di carico o delle criticità segnalate.
- **Alimentare il censimento nazionale**, trasformando ogni ente aderente in un contribuente attivo alla base dati infrastrutturale richiesta dal Piano Nazionale MIT.

Questo posiziona PNTE come strumento di **censimento infrastrutturale** oltre che gestionale autorizzativo, in anticipo sulle scadenze normative del 2027.

---

### 15. Gestione Cantieri e Percorsi Alternativi

Gli enti gestori di competenza (Comuni, ANAS, Autostrade) segnalano i cantieri stradali direttamente dalla propria Scrivania. Ogni cantiere è definito da una geometria GIS (tratto stradale), un periodo di validità e un livello di severità (informativo / limitato / chiuso).

Al momento del tracciamento del percorso, prima della sottomissione, il sistema verifica automaticamente la sovrapposizione spaziale e temporale (`ST_INTERSECTS` + date range) tra il percorso richiesto e i cantieri attivi. In caso di conflitto:

- La sottomissione viene bloccata con indicazione del tratto incompatibile.
- Vengono proposti sulla mappa **percorsi alternativi** calcolati da OSRM escludendo i tratti interessati.

Le **Forze dell'Ordine** (`law-enforcement`) accedono in sola lettura a:

- Verifica sul campo tramite targa o scansione QR Code → dettaglio completo della pratica approvata (convoglio, percorso, ente emittente, periodo di validità).
- Mappa dei cantieri attivi nel giorno corrente con filtro per tratta.
- Lista dei trasporti eccezionali in transito nella giornata corrente con visualizzazione del percorso.
- Vista ottimizzata per dispositivi mobili, per utilizzo diretto su strada.

---

### 16. Conformità 2027 (MIT Compliance)

Le linee guida MIT per la sicurezza dei ponti e la gestione dei trasporti eccezionali prevedono scadenze di adeguamento entro il **2027**. L'architettura di PNTE è stata progettata per anticipare tali requisiti:

- Il modello dati include i campi necessari per il collegamento alle schede AINOP (`codice_univoco_ainop`) e per tracciare le limitazioni strutturali per tratta.
- Il modulo WebGIS è predisposto per importare e visualizzare i **Corridoi Nazionali** definiti dal Piano MIT, privilegiando i tratti classificati come idonei in fase di tracciamento del percorso.
- La finestra temporale fino al 2027 è un vantaggio competitivo per gli enti che adottano PNTE oggi: arriveranno alla scadenza già allineati agli standard AINOP e al Piano Nazionale, senza interventi straordinari di migrazione.

---

### 17. Interoperabilità e Servizi PA

- **Autenticazione Zero-Trust:** Integrazione con **SPID/CIE** tramite il layer Socialite. Delega a cascata con Fast-Track PDND/InfoCamere, agenzie multi-cliente, P7M per le prime nomine e OTP-PEC per gli accessi ordinari (vedi §3 e §4).
- **Principio "Once Only":** Nessun dato inserito manualmente se disponibile da banche dati nazionali. Sincronizzazione Enti (PEC incluse) via **API IPA (Indice PA)**; validazione Aziende via **Registro Imprese / INI-PEC tramite PDND**.
- **Pareri (Nulla Osta):** PEC asincrona in uscita + **IMAP Listener** in ingresso per ricezione automatica con stato "Da Valutare" e supervisione dell'operatore (vedi §9).
- **Pagamenti:** Integrazione nativa con **PagoPA** — IUV mono-beneficiario + Clearing House con bonifici SEPA mensili per gli enti con delega finanziaria (vedi §11).
- **Documentale:** Integrazione via API con il **Protocollo Informatico** dell'ente rilasciante.
- **Legalità e Mandati:** Firma PAdES/CAdES per autorizzazioni e procure speciali. Il motore di validazione confronta il CF del firmatario con le anagrafiche ufficiali, eliminando discrezionalità nei mandati Agenzia-Ditta e nel primo censimento Ente.
- **Governance Multi-Agenzia:** Una Ditta può mantenere più partner attivi in parallelo (Nord, Centro, emergenze, backup) tramite il pannello **Gestione Partner**, senza mescolare i rapporti legali aziendali con le deleghe operative dei singoli utenti.

---

### 18. Modello Dati (Entità Principali)

| Entità | Descrizione |
| :--- | :--- |
| `users` | Identità digitale SPID/CIE della persona fisica; nessun potere diretto senza `delegations` |
| `companies` | Ditte e Agenzie (`is_agency`, `ateco_code`, `ateco_last_synced_at` per compliance) con dati camerali ufficiali e anagrafica clienti |
| `agency_mandates` | Rapporto partner Ditta -> Agenzia: durata legale, documento firmato, stato, sospensione/revoca, rinnovo e scope operativo |
| `delegations` | Tabella polimorfica utente -> Entity|Company|Agency con ruolo, stato, audit e riferimento al contesto partner quando l'utente opera per conto di una Agenzia |
| `vehicle_documents` | Libretti, schemi di carico, omologazioni e allegati della flotta |
| `vehicles` | Anagrafica tecnica (assi, pesi, configurazioni) per il Garage Virtuale e il Preavviso di Viaggio |
| `entities` | Comuni, Province, gestori stradali (poligoni GIS, PEC, `is_tenant`, `has_financial_delegation`, `is_capofila`) |
| **Geometries (Spatial)** | LINESTRING/POLYGON archiviate in PostgreSQL/PostGIS con SRID 4326 (WGS84); indice GiST; exportate in RFC 7946 GeoJSON; operazioni `ST_INTERSECTS`, `ST_LENGTH`, `ST_BUFFER` native |
| `applications` | La pratica e la sua macchina a stati (`draft → submitted → waiting_clearances → waiting_payment → approved`), con contesto di audit del partner che l'ha creata |
| `routes` | Geometrie del percorso e `entity_breakdown` (km per ente) |
| `clearances` | Workflow Nulla Osta: stati `pre_cleared` (ARS), `pending_review` (IMAP Listener), `approved`, `rejected` |
| `trips` | Preavvisi di Viaggio: veicoli effettivi usati nel singolo viaggio, stato, orari, contatore viaggi |
| `tariffs` | Coefficienti d'usura storicizzati per il motore di calcolo |
| `roadworks` | Cantieri segnalati dagli enti gestori: geometria GIS, periodo di validità, severità, stato |
| `standard_routes` | ARS — strade pre-approvate per Fast-Track (LINESTRING, limiti sagoma/massa) |

---

### 19. Roadmap di Sviluppo

| Versione | Milestone | Obiettivo | Stato |
| :--- | :--- | :--- | :--- |
| **v0.1.x** | Stack | Laravel 13 / PHP 8.4 / Tailwind v4 / Alpine.js / Docker Compose | ✅ Completato |
| **v0.2.x** | M1 — Foundation | Auth SPID/CIE, RBAC separato, `agency_mandates + delegations`, agenzie, Fast-Track PDND, P7M validation engine, pannello `/system`, Gestione Partner | ✅ Completato (con patch documentali pianificate) |
| **v0.3.x** | M2 — Garage & Calcolo | Anagrafica mezzi, assi, `WearCalculationService` (D.P.R. 495/1992), ATECO-based agency detection | ✅ Completato (con patch ATECO in v0.2.x) |
| **v0.4.x** | M3 — WebGIS | WebGIS, OSRM, ARS (Tratti Verdi/Rossi), Intersezione Spaziale, Cantieri | 🔜 In sviluppo |
| **v0.5.x** | M4 — Workflow | State Machine, Scrivania Enti, IMAP Listener, Preavviso di Viaggio PWA, Tipo Istanza, Radar Forze dell'Ordine | ⏳ Pianificato |
| **v0.6.x** | M5 — Pagamenti | PagoPA Clearing House, `is_tenant`/`has_financial_delegation`/`is_capofila`, PDF, Firma PAdES, SEPA XML | ⏳ Pianificato |
| **v0.7.x** | M6 — Open Data | Open Data Portal, Mappa Pubblica Cantieri, Statistiche, GeoJSON/KML | ⏳ Pianificato |
| **v1.0.0** | GA | AINOP/PDND integration, security audit, AgID compliance | ⏳ Pianificato |

---

### 20. Installazione Rapida

**Prerequisiti:** Docker Engine 24+, Docker Compose v2.

```bash
# 1. Clona il repository
git clone https://github.com/provincia-di-pescara/PNTE.git
cd PNTE

# 2. Configura l'environment
cp .env.example .env
# Compila .env con i valori richiesti (DB, Redis, SPID, PagoPA, ecc.)

# 3. Build e avvio dei servizi
docker compose up -d --build

# 4. Dipendenze e database
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
```

> **Nota:** Il progetto richiede PHP 8.4+, Node 25 e PostgreSQL 16 + PostGIS 3.4. Per lo sviluppo locale senza Docker è possibile usare `composer run dev` per avviare tutti i servizi concorrenti (server, queue, log, Vite). Il container `osrm` richiede il grafo stradale pre-processato — avviarlo con `docker compose --profile gis up`.

---

### 21. Contribuire

Il progetto è sviluppato in aperto e accetta contributi nel rispetto delle [linee guida per i contributori di Developers Italia](https://developers.italia.it/it/come-contribuire). Per segnalare bug o proporre funzionalità aprire una issue sul repository GitHub.

**Licenza:** [EUPL-1.2](LICENSE)

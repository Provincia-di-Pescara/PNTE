# GTE-Abruzzo: Gestionale Trasporti Eccezionali

## Progetto di Trasformazione Digitale per la Provincia di Pescara e il riuso regionale

> Software a riuso sviluppato dalla **Provincia di Pescara** ai sensi dell'art. 69 del D.Lgs. 82/2005 (CAD) e pubblicato sul catalogo [Developers Italia](https://developers.italia.it/). Licenza **EUPL-1.2**.

---

### 1. Premessa e Visione

Il progetto **GTE-Abruzzo** (Gestionale Trasporti Eccezionali) nasce per digitalizzare l'intero ciclo di vita delle autorizzazioni per il transito di veicoli e trasporti eccezionali. Il software è sviluppato dalla **Provincia di Pescara** in qualità di ente capofila, con l'obiettivo di essere messo a disposizione di tutti gli enti della Regione Abruzzo e del territorio nazionale tramite il catalogo del **Riuso di Developers Italia**.

L'architettura è concepita come un **SaaS (Software as a Service) Multi-tenant**, dove ogni ente (Province, Comuni, ANAS) può gestire le proprie competenze in un ambiente isolato ma integrato in un unico flusso geografico e amministrativo.

---

### 2. Riferimenti Normativi

Il software è costruito per far rispettare i rigidi vincoli del Codice della Strada:

- **Art. 10 del Codice della Strada (D.Lgs 285/1992):** Requisiti tecnici e amministrativi per i trasporti eccezionali.
- **Art. 9-20 del Regolamento di Attuazione (D.P.R. 495/1992):** Formule di calcolo per l'indennizzo d'usura stradale.
- **Linee Guida AgID:** Standard per il riuso del software nella PA e accessibilità.
- **Direttive MIT:** Gestione della sicurezza dei ponti e dei manufatti stradali.

---

### 3. Stack Tecnologico

La scelta delle tecnologie mira alla massima modernità e stabilità (Long Term Support):

| Layer | Tecnologia | Versione | Ruolo |
| :--- | :--- | :--- | :--- |
| **Framework** | Laravel | 12.x | Logica di business, Eloquent ORM, API Gateway |
| **Linguaggio** | PHP (PHP-FPM) | 8.4 | Performance elevate e tipizzazione forte |
| **Web Server** | Nginx | stable | Gestione richieste e security hardening |
| **Database** | MariaDB LTS | 11.4 | RDBMS con supporto nativo GIS (Spatial Index) |
| **Cache/Queue** | Redis | latest | Gestione code asincrone (PEC, PDF, Pagamenti) |
| **Frontend** | Tailwind CSS v4 + Alpine.js | v4 / v3 | UI zero-runtime, reattiva e conforme alle linee guida design PA |
| **Routing GIS** | OSRM | latest | Motore di routing self-hosted per il calcolo dei percorsi |
| **PDF Engine** | Headless Chrome (Browsershot) | latest | Generazione PDF via Chromium per layout complessi |
| **Container** | Docker + Docker Compose | latest | Pacchettizzazione completa per il rilascio a riuso |

---

### 4. Architettura dei Servizi (Docker Compose)

Il sistema è distribuito tramite una flotta di container cooperanti:

- `app`: Il cuore del sistema in Laravel (PHP-FPM + Nginx nello stesso container).
- `db`: MariaDB 11.4 LTS con storage spaziale per i confini territoriali.
- `osrm`: Motore di routing caricato con il grafo stradale regionale abruzzese.
- `chrome`: Servizio dedicato al rendering dei PDF (Headless Chrome).
- `redis`: Gestore delle code per l'invio delle notifiche e l'elaborazione dei pareri.

---

### 5. Integrazione WebGIS e Calcolo Indennizzi

Il cuore innovativo è il motore geografico:

- **Routing Snap-to-Road:** Grazie a **OSRM**, l'utente traccia un percorso che viene automaticamente ancorato alle strade reali.
- **Intersezione Spaziale:** Il backend analizza la `LineString` del percorso incrociandola con i poligoni dei **confini comunali** caricati a database, estraendo automaticamente i km percorsi per ogni ente.
- **Motore di Calcolo:** Un rule-engine basato su tabelle di coefficienti (storicamente tracciate) calcola l'indennizzo d'usura totale e la quota spettante a ogni ente terzo, in conformità al D.P.R. 495/1992.

---

### 6. Integrazione AINOP (Archivio Informatico Nazionale delle Opere Pubbliche)

Il sistema è architetturalmente predisposto per la piena interoperabilità con **AINOP** tramite la **Piattaforma Digitale Nazionale Dati (PDND)**.

Quando le API AINOP saranno disponibili in produzione, GTE-Abruzzo potrà:

- **Verificare in tempo reale** la portata e le limitazioni di ponti e viadotti presenti lungo il percorso autorizzativo, interrogando il dataset AINOP tramite le API PDND.
- **Collegare automaticamente** le opere d'arte censite a sistema (tramite coordinate geografiche o identificativi univoci `codice_univoco_ainop`) alle corrispondenti schede AINOP.
- **Esporre nel WebGIS** lo stato di idoneità infrastrutturale dei tratti percorsi, con evidenza visiva delle limitazioni di carico o delle criticità segnalate.
- **Alimentare il censimento nazionale**, trasformando ogni ente aderente in un contribuente attivo alla base dati infrastrutturale richiesta dal Piano Nazionale MIT.

Questo posiziona GTE-Abruzzo come strumento di **censimento infrastrutturale** oltre che gestionale autorizzativo, in anticipo sulle scadenze normative del 2027.

---

### 7. Conformità 2027 (MIT Compliance)

Le linee guida MIT per la sicurezza dei ponti e la gestione dei trasporti eccezionali prevedono scadenze di adeguamento entro il **2027**. L'architettura di GTE-Abruzzo è stata progettata per anticipare tali requisiti:

- Il modello dati include i campi necessari per il collegamento alle schede AINOP (`codice_univoco_ainop`) e per tracciare le limitazioni strutturali per tratta.
- Il modulo WebGIS è predisposto per importare e visualizzare i **Corridoi Nazionali** definiti dal Piano MIT, privilegiando i tratti classificati come idonei in fase di tracciamento del percorso.
- La finestra temporale fino al 2027 è un vantaggio competitivo per gli enti che adottano GTE-Abruzzo oggi: arriveranno alla scadenza già allineati agli standard AINOP e al Piano Nazionale, senza interventi straordinari di migrazione.

---

### 8. Interoperabilità e Servizi PA

- **Autenticazione:** Integrazione con **SPID/CIE** tramite il layer Socialite. Gestione deleghe per agenzie di pratiche auto.
- **Pagamenti:** Integrazione nativa con **PagoPA**. Gestione del flusso "Incasso Unico + Clearing" per la ripartizione fondi tra enti.
- **Documentale:** Integrazione via API con il **Protocollo Informatico** dell'ente rilasciante.
- **Legalità:** Firma PAdES automatica tramite **Firma Remota** (Aruba/InfoCert API) e verifica pubblica tramite **QR-Code**.

---

### 9. Modello Dati (Entità Principali)

| Entità | Descrizione |
| :--- | :--- |
| `users` / `companies` | Identità digitale (SPID/CIE) e poteri di delega |
| `vehicles` | Anagrafica tecnica (assi, pesi, configurazioni) per il Garage Virtuale |
| `entities` | Comuni, Province, gestori stradali (con poligoni GIS e PEC) |
| `applications` | La pratica e la sua macchina a stati (`draft → submitted → waiting_clearances → waiting_payment → approved`) |
| `routes` | Geometrie del percorso e metadati chilometrici per ente |
| `clearances` | Workflow dei Nulla Osta richiesti agli enti terzi |
| `tariffs` | Coefficienti d'usura storicizzati per il motore di calcolo |

---

### 10. Roadmap di Sviluppo

| Fase | Obiettivo |
| :--- | :--- |
| **Phase 1: Foundation** | Setup Docker, Auth SPID/CIE, RBAC e gestione deleghe |
| **Phase 2: Territorio** | Importazione confini amministrativi e setup OSRM |
| **Phase 3: Garage & Calcolo** | Anagrafica mezzi e motore di calcolo indennizzi (`WearCalculationService`) |
| **Phase 4: Workflow** | Scrivania Enti Terzi, state machine, job asincroni PEC |
| **Phase 5: Compliance** | Integrazione PagoPA, Protocollo Informatico e Firma Remota PAdES |

---

### 11. Installazione Rapida

**Prerequisiti:** Docker Engine 24+, Docker Compose v2.

```bash
# 1. Clona il repository
git clone https://github.com/provincia-pescara/gte-abruzzo.git
cd gte-abruzzo

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

> **Nota:** Il progetto richiede PHP 8.4 e MariaDB 11.4 LTS. Per lo sviluppo locale senza Docker è possibile usare SQLite (`DB_CONNECTION=sqlite`) e il comando `composer run dev` per avviare tutti i servizi concorrenti (server, queue, log, Vite).

---

### 12. Contribuire

Il progetto è sviluppato in aperto e accetta contributi nel rispetto delle [linee guida per i contributori di Developers Italia](https://developers.italia.it/it/come-contribuire). Per segnalare bug o proporre funzionalità aprire una issue sul repository GitHub.

**Licenza:** [EUPL-1.2](LICENSE)

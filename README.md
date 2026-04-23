# GTE-Abruzzo: Gestionale Trasporti Eccezionali

## Progetto di Trasformazione Digitale per la Provincia di Pescara e il riuso regionale

### 📌 1. Premessa e Visione
Il progetto **GTE-Abruzzo** (Gestionale Trasporti Eccezionali) nasce per digitalizzare l'intero ciclo di vita delle autorizzazioni per il transito di veicoli e trasporti eccezionali. Il software è sviluppato dalla **Provincia di Pescara** in qualità di ente capofila, con l'obiettivo di essere messo a disposizione di tutti gli enti della Regione Abruzzo e del territorio nazionale tramite il catalogo del **Riuso di Developers Italia**.

L'architettura è concepita come un **SaaS (Software as a Service) Multi-tenant**, dove ogni ente (Province, Comuni, ANAS) può gestire le proprie competenze in un ambiente isolato ma integrato in un unico flusso geografico e amministrativo.

---

### ⚖️ 2. Riferimenti Normativi
Il software è costruito per far rispettare i rigidi vincoli del Codice della Strada:
- **Art. 10 del Codice della Strada (D.Lgs 285/1992):** Requisiti tecnici e amministrativi per i trasporti eccezionali.
- **Art. 9-20 del Regolamento di Attuazione (D.P.R. 495/1992):** Formule di calcolo per l'indennizzo d'usura stradale.
- **Linee Guida AgID:** Standard per il riuso del software nella PA e accessibilità.
- **Direttive MIT:** Gestione della sicurezza dei ponti e dei manufatti stradali.

---

### 🛠️ 3. Stack Tecnologico
La scelta delle tecnologie mira alla massima modernità e stabilità (Long Term Support):

| Layer | Tecnologia | Ruolo |
| :--- | :--- | :--- |
| **Framework** | Laravel 11 | Logica di business, Eloquent ORM, API Gateway. |
| **Linguaggio** | PHP 8.3 (PHP-FPM) | Performance elevate e tipizzazione forte. |
| **Web Server** | Nginx | Gestione richieste e security hardening. |
| **Database** | MariaDB 10.11 | RDBMS con supporto nativo GIS (Spatial Index). |
| **Cache/Queue** | Redis | Gestione code asincrone (PEC, PDF, Pagamenti). |
| **Frontend** | Blade + Tailwind + Alpine | UI leggera, reattiva e conforme alle linee guida di design PA. |
| **Routing GIS** | OSRM | Motore di routing self-hosted per il calcolo dei percorsi. |
| **PDF Engine** | Headless Chrome | Generazione PDF via Browsershot per layout complessi. |
| **Container** | Docker | Pacchettizzazione completa per il rilascio a riuso. |

---

### 🏗️ 4. Architettura dei Servizi (Docker Compose)
Il sistema è distribuito tramite una flotta di container cooperanti:
- `app`: Il cuore del sistema in Laravel.
- `db`: MariaDB con storage spaziale per i confini territoriali.
- `osrm`: Motore di routing caricato con il grafo stradale regionale.
- `chrome`: Servizio dedicato al rendering dei PDF (Headless).
- `redis`: Gestore delle code per l'invio delle notifiche e l'elaborazione dei pareri.

---

### 🛰️ 5. Integrazione WebGIS e Calcolo Indennizzi
Il cuore innovativo è il motore geografico:
- **Routing Snap-to-Road:** Grazie a **OSRM**, l'utente traccia un percorso che viene automaticamente ancorato alle strade reali.
- **Intersezione Spaziale:** Il backend analizza la `LineString` del percorso incrociandola con i poligoni dei **confini comunali** caricati a database, estraendo automaticamente i km percorsi per ogni ente.
- **Motore di Calcolo:** Un rule-engine basato su tabelle di coefficienti (storicamente tracciate) calcola l'indennizzo d'usura totale e la quota spettante a ogni ente terzo.

---

### 💳 6. Interoperabilità e Servizi PA
- **Autenticazione:** Integrazione con **SPID/CIE** tramite il layer `GovPay-Interaction-Layer`. Gestione deleghe per agenzie di pratiche auto.
- **Pagamenti:** Integrazione nativa con **PagoPA**. Gestione del flusso "Incasso Unico + Clearing" per la ripartizione fondi tra enti.
- **Documentale:** Integrazione via API con il **Protocollo Informatico** dell'ente rilasciante.
- **Legalità:** Firma PAdES automatica tramite **Firma Remota** (Aruba/InfoCert API) e verifica pubblica tramite **QR-Code**.

---

### 📊 7. Modello Dati (Entità Principali)
- `users` / `companies`: Identità digitale e poteri di delega.
- `vehicles`: Anagrafica tecnica (assi, pesi, configurazioni) per il "Garage Virtuale".
- `entities`: Database dei comuni, province e gestori stradali (con poligoni GIS).
- `applications`: La pratica e la sua macchina a stati (Bozza -> Istruttoria -> Nulla Osta -> Pagamento -> Rilasciata).
- `routes`: Geometrie del percorso e metadati chilometrici.
- `clearances`: Workflow dei pareri richiesti agli enti terzi.

---

### 🚀 8. Roadmap di Sviluppo
- **Phase 1: Foundation.** Setup ambiente Docker, Auth SPID/CIE e gestione deleghe.
- **Phase 2: Territorio.** Importazione confini amministrativi e setup OSRM.
- **Phase 3: Garage & Calcolo.** Sviluppo anagrafica mezzi e motore di calcolo indennizzi a DB.
- **Phase 4: Workflow.** Scrivania Enti Terzi per i Nulla Osta e gestione PEC.
- **Phase 5: Compliance.** Integrazione PagoPA, API Protocollo e Firma Remota.

---

### 📦 9. Installazione Rapida
```bash
# Clone this repository
git clone <repository-url>
cd <repository-directory>

# Configurazione environment
touch .env
# Compilare `.env` con i valori richiesti per l'ambiente locale

# Build e Start dei servizi
docker-compose up -d --build

# Installazione dipendenze e database
docker-compose exec app composer install
docker-compose exec app php artisan migrate --seed
```

# Piano di Sviluppo: GTE-Abruzzo (Gestionale Trasporti Eccezionali)

## Il Cappello Logico: L'Iter della Domanda di Trasporto Eccezionale

Prima di implementare il codice, è fondamentale avere chiaro il ciclo di vita della pratica (la *State Machine*). L'intero software deve guidare l'utente e l'operatore attraverso questo esatto flusso in 8 passaggi sequenziali:

1. **Identificazione (SPID/CIE):** L'utente (persona fisica) accede al sistema. L'Identity Provider verifica le credenziali.
2. **Delega / Profilazione:** L'utente dichiara per quale Azienda (P.IVA) sta operando, selezionandola tra quelle per cui possiede una delega attiva a sistema.
3. **Composizione Tecnica (Il Garage):** L'utente seleziona dal proprio "Garage Virtuale" i mezzi (motrice + rimorchio) che comporranno il convoglio. Il sistema ne aggrega i pesi e calcola gli assi.
4. **Tracciamento Geografico (Routing):** Sulla mappa interattiva (WebGIS), l'utente traccia il percorso. Il sistema calcola la rotta esatta (su strada) e la interseca spazialmente con i confini comunali/provinciali.
5. **Invio e Istruttoria (Stato: `submitted`):** La pratica arriva alla Provincia di Pescara. Un operatore verifica la validità documentale e il tracciato.
6. **Acquisizione Pareri (Stato: `waiting_clearances`):** Il sistema notifica (via Scrivania dedicata o PEC automatica) i Comuni, l'ANAS e le Autostrade interessate dal percorso. Si attende la loro esplicita approvazione (Nulla Osta).
7. **Pagamento (Stato: `waiting_payment`):** Ottenuti i pareri positivi, il sistema quantifica l'usura stradale (marche da bollo + tariffa al km divisa per ente). Genera lo IUV PagoPA e si mette in ascolto della Ricevuta Telematica (RT).
8. **Rilascio (Stato: `approved`):** Il sistema genera il PDF dell'autorizzazione, invoca le API del Protocollo Informatico, appone la Firma Remota (PAdES) del dirigente e rende disponibile il documento all'utente, munito di QR Code per la validazione pubblica su strada.

---

## Adeguamento Strategico MIT: "National-Ready" (Proroga al 2027)

La proroga delle linee guida MIT non indica un ripensamento normativo, ma una criticità operativa: i dati infrastrutturali locali non sono ancora digitalizzati in modo completo (ponti, viadotti, portate).

Il progetto GTE-Abruzzo, quindi, va posizionato come **sistema di censimento infrastrutturale** oltre che gestionale autorizzativo. L'alimentazione dei dati da parte di Comuni e Province diventa un contributo diretto alla base informativa richiesta a livello nazionale.

### Implicazioni Tecniche per GTE-Abruzzo

1. **Integrazione AINOP (Archivio Informatico Nazionale delle Opere Pubbliche)**
   - Inserire una fase esplicita di interoperabilità con AINOP (tramite PDND) nella roadmap.
   - Obiettivo: verificare in tempo reale la portata/limitazioni delle opere su un percorso.
   - **Azione sul modello dati:** aggiungere il campo `codice_univoco_ainop` nelle tabelle che rappresentano ponti/opere d'arte.

2. **Piano Nazionale e Corridoi Infrastrutturali Idonei**
   - Il modulo WebGIS deve poter importare e gestire i "Corridoi Nazionali" definiti dal Piano.
   - In fase di tracciamento percorso, il sistema deve:
     - privilegiare i tratti già classificati come idonei;
     - evidenziare come "agevolato" il passaggio su infrastrutture censite idonee.

3. **Posizionamento Strategico e Riuso**
   - La finestra temporale fino al 2027 è un vantaggio: consente di arrivare alla piena conformità prima dell'obbligo stringente.
   - Messaggio istituzionale chiave: il sistema permette alla Provincia di arrivare al 2027 già allineata agli standard AINOP e al Piano Nazionale.

---

## Roadmap di Sviluppo (Milestones & Task)

Il lavoro è suddiviso in 5 blocchi operativi (Sprint/Milestone), progettati per un rilascio incrementale delle funzionalità.

### 🚩 Milestone 1: Fondamenta, Identità e Anagrafiche
**Obiettivo:** Gestire l'autenticazione, le autorizzazioni e le anagrafiche di base.

* **[Task 1.1] Integrazione SPID/CIE:**
  * Configurazione del package `GovPay-Interaction-Layer` in Laravel.
  * Creazione delle *Migrations* per la tabella `users` (dati fiscali persona fisica).
* **[Task 1.2] Sistema RBAC (Role-Based Access Control):**
  * Setup dei ruoli e permessi: `super-admin` (Provincia Pescara), `operator` (Altre Province), `third-party` (Comuni/ANAS), `citizen` (Richiedente).
* **[Task 1.3] Modulo Aziende e Deleghe:**
  * *Migrations* per `companies` (Aziende/Agenzie) e tabella pivot `company_user` per la gestione delle procure/deleghe.
  * UI (Frontend) per la richiesta e l'approvazione delle deleghe.
* **[Task 1.4] Anagrafica Enti Territoriali:**
  * *Migrations* per la tabella `entities` (Comuni, Province, Anas, Autostrade).
  * Inserimento campi operativi: indirizzi PEC, codici ISTAT e tipologia ente.

### 🚩 Milestone 2: Il Garage Virtuale e il Motore di Calcolo
**Obiettivo:** Strutturare l'inserimento dei dati tecnici dei mezzi e la logica matematica dei tariffari.

* **[Task 2.1] CRUD Veicoli:**
  * *Migrations* per la tabella `vehicles` (Trattori, Rimorchi, Mezzi d'opera).
  * Form UI (Alpine.js/Blade) per l'inserimento di targhe, telaio, massa complessiva e dimensioni.
* **[Task 2.2] Gestione Assi (Core Calcolo):**
  * Sviluppo della logica per definire interassi e carico per asse (struttura JSON nel DB o tabella relazionale dedicata `vehicle_axles`).
* **[Task 2.3] Motore Tariffario (Rule Engine):**
  * *Migrations* per `tariffs` (coefficienti storicizzati di usura validi da/a).
  * Creazione del *Service* Laravel (`WearCalculationService`) per l'elaborazione delle formule di calcolo usura in base a peso, configurazione assi e chilometri.

### 🚩 Milestone 3: Core Geografico e Routing (WebGIS)
**Obiettivo:** Tracciare la mappa, calcolare le distanze e individuare automaticamente gli enti attraversati.

* **[Task 3.1] Bootstrap Dati Spaziali:**
  * Importazione degli shapefile/GeoJSON dei confini comunali e provinciali abruzzesi in MariaDB (sfruttando campi `POLYGON` o `MULTIPOLYGON`).
* **[Task 3.2] Frontend Leaflet + OSRM:**
  * Sviluppo della vista mappa interattiva.
  * Integrazione chiamate API al container OSRM locale per ottenere la `LineString` esatta del percorso vettoriale (*snap-to-road*).
* **[Task 3.3] Intersezione Spaziale Backend:**
  * Sviluppo delle query spaziali a DB (tramite funzioni `ST_Intersection` e `ST_Length`).
  * Logica di estrazione automatica delle `entities` attraversate dal poligono della `LineString` e relativo calcolo dei chilometri di competenza.
* **[Task 3.4] Integrazione PDND-AINOP (Sincronizzazione Infrastrutturale):**
  * Sviluppo del modulo per consumare le API AINOP non appena disponibili via PDND.
  * Workflow di allineamento: quando un Comune inserisce un ponte/opera d'arte nel gestionale, il sistema tenta il collegamento automatico all'opera AINOP tramite coordinate geografiche e/o identificativi univoci.
  * Esposizione in WebGIS e nei controlli di istruttoria dello stato di corrispondenza con AINOP e delle informazioni di idoneità disponibili.

### 🚩 Milestone 4: La Macchina a Stati e il Flusso Burocratico
**Obiettivo:** Compilazione della pratica, validazione e gestione asincrona dei Nulla Osta.

* **[Task 4.1] Wizard Compilazione Domanda:**
  * Sviluppo form multi-step: Selezione Azienda -> Scelta Convoglio dal Garage -> Disegno Percorso -> Riepilogo.
  * Salvataggio dati relazionali nelle tabelle `applications`, `routes` e tabelle pivot.
* **[Task 4.2] State Machine Controller:**
  * Implementazione delle transizioni di stato rigide (`draft` -> `submitted` -> `waiting_clearances`).
* **[Task 4.3] Scrivania Enti Terzi:**
  * Creazione della dashboard protetta per gli utenti con ruolo `third-party` (es. operatori comunali).
  * Funzioni di visualizzazione tratta di competenza e bottoni di "Approvazione/Rifiuto" con motivazione.
* **[Task 4.4] Job Asincroni PEC:**
  * Setup delle code (`Redis`) per l'invio massivo e in background delle email/PEC di richiesta parere.
  * Listener per la registrazione automatica degli esiti transitori.

### 🚩 Milestone 5: Chiusura Legale, Pagamenti e Rilascio
**Obiettivo:** Riscossione oneri, protocollazione e generazione del documento legale valido.

* **[Task 5.1] Integrazione PagoPA:**
  * Generazione dinamica dell'avviso IUV basato sull'output del `WearCalculationService`.
  * Sviluppo dell'endpoint Webhook per l'ascolto passivo della RT (Ricevuta Telematica) e sblocco automatico della pratica.
* **[Task 5.2] Generazione PDF (Browsershot):**
  * Creazione della vista *Blade* per il layout ufficiale dell'Autorizzazione (inclusivo di mini-mappa tracciato e tabelle tecniche).
  * Implementazione del *Job* di generazione PDF tramite *Headless Chrome*.
* **[Task 5.3] Interoperabilità Finale (Protocollo e Firma):**
  * Sviluppo client API per la connessione al Protocollo Informatico della Provincia.
  * Integrazione API Firma Remota (es. Aruba PAdES) per la firma del Dirigente.
  * Generazione QR Code dinamico impresso sul PDF per la verifica pubblica da parte delle Forze dell'Ordine.
* **[Task 5.4] Modulo di Rendicontazione (Clearing):**
  * Dashboard Ragioneria: vista per l'esportazione mensile (Excel/CSV) del riparto fondi incassati, con evidenza della quota Provincia vs. quote Enti Terzi.

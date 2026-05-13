# Piano di Sviluppo: PNTE (Piattaforma Nazionale Trasporti Eccezionali)

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

## Architettura SaaS e Confini: Il Walled Garden Ibrido

Il sistema risolve il problema della scalabilità nazionale garantendo al contempo il controllo alla Provincia Capofila.

**Cartografia Nazionale, Azione Locale:** Il database contiene i confini ISTAT e le strade di tutta Italia, ma il sistema opera attivamente solo per le Province che lo adottano come **Tenant**. Questo consente di tracciare percorsi interregionali sin dal primo giorno senza riconfigurare la cartografia.

**Taglio delle Competenze:** Il motore di calcolo elabora l'usura stradale e le ripartizioni economiche per l'intero percorso. Il flusso finanziario (IUV, Clearing) è attivato solo per gli enti con `has_financial_delegation = true` nel perimetro Tenant. La Provincia genera un IUV PagoPA **solo se è essa stessa un Tenant attivo** sulla piattaforma.

**Gestione Enti Esterni:** Per gli enti non-tenant o non censiti, il sistema — agendo da **Ente Unico Rilasciante** — invia automaticamente la PEC di Nulla Osta, includendo un invito esplicito a censirsi in piattaforma. La regolarizzazione finanziaria verso questi enti rimane a carico dell'utente richiedente (importo calcolato e mostrato a schermo).

**Due flag su `entities` (Provincia Tenant li configura ente per ente):**

- `is_tenant` (bool, default `false`): l'ente ha una Scrivania attiva? `true` → usa la dashboard, riceve PEC di avviso. `false` → PEC automatica, Provincia sblocca manualmente.
- `has_financial_delegation` (bool, default `false`): l'ente delega la riscossione alla Provincia? `true` → quota inclusa nell'IUV + bonifico SEPA mensile. `false` → quota scorporata + avviso all'utente. L'ente può gestire questo flag in autonomia dalla propria dashboard, con grace period fino a mezzanotte del giorno corrente.

---

## Identity Zero-Trust e Onboarding (Delega a Cascata)

Il data-entry manuale è azzerato. Al primo login SPID/CIE l'utente è "nudo" — autenticato ma senza poteri operativi. Deve richiedere una delega per una Ditta o un Ente.

**1. Fast-Track Aziendale (zero latenza):**
Si attiva se: SPID Professionale, oppure il CF dell'utente coincide con quello del Legale Rappresentante estratto in tempo reale dal Registro Imprese (PDND). In entrambi i casi il potere come `admin-azienda` è riconosciuto istantaneamente, ma la source of truth resta il Registro Imprese: il controllo viene ripetuto ai login successivi e il titolo decade automaticamente se il legale rappresentante cambia.

**2. Agenzie di Consulenza (mandato di rappresentanza):**
L'Agenzia è prima di tutto una Ditta: il titolare entra con SPID Professionale o check PDND e viene auto-approvato come `admin-azienda` della propria Agenzia. Da quel momento il collegamento Agenzia -> Cliente si gestisce su due livelli distinti:
- `agency_mandates` per il rapporto legale Ditta -> Agenzia (documento firmato, durata, sospensione/revoca, rinnovo, scope operativo);
- `delegations` per i singoli utenti dell'Agenzia che operano dentro un mandato partner attivo.
- **Scenario A: Ditta già digitale** → richiesta mandato in piattaforma + approvazione con un click del titolare della Ditta.
- **Scenario B: Ditta analogica** → l'Agenzia sceglie la data di validità; il sistema genera una **Procura Speciale** PDF con quella data già scritta; il legale rappresentante firma il documento offline; l'upload `.p7m` viene verificato automaticamente (integrità, certificato, CF firmatario vs Registro Imprese) e il mandato si attiva senza intervento della Provincia.
- La data scritta nel PDF firmato diventa il limite massimo di `agency_mandates.valid_until`; le deleghe utenti dell'Agenzia non possono superare il mandato partner padre.

**3. Primo onboarding Ente via PDF firmato (`.p7m`/PAdES):**
Per il primo `admin-ente` di ogni Comune o Provincia, il sistema genera un PDF formale di nomina; il Dirigente lo firma digitalmente e lo ricarica sul portale. Un motore unico di validazione verifica sostanza e forma del documento, controlla il certificato di firma e confronta il firmatario con la banca dati pubblica. Se l'ente è `is_capofila = true`, il primo onboarding è auto-approvato.

**4. Delega a cascata per il personale ordinario:**
Una volta validato il primo amministratore, i dipendenti tornano su flussi leggeri: richiesta in piattaforma, OTP PEC IPA se serve come controllo out-of-band, approvazione da dashboard dell'`admin-ente` o dell'`admin-azienda`.

**5. Approvazione Manuale (fallback):**
Richieste non gestite automaticamente → admin Provincia Tenant di appartenenza → se ente non-tenant → Provincia Capofila.

---

## Analisi TEWEB: cosa prendiamo e cosa superiamo

Il portale TEWEB di ANAS è il riferimento nazionale di fatto per le pratiche di viabilità statale. Per PNTE va studiato come **Stele di Rosetta**: importiamo le logiche di business consolidate e scartiamo gli aspetti peggiori di UX.

**1. Preavviso di Viaggio:**
TEWEB conferma la correttezza del nostro Modulo Viaggi. Per autorizzazioni multiple e periodiche, il singolo viaggio deve dichiarare quali mezzi effettivi stanno partendo quel giorno. In PNTE questo diventa una **PWA mobile-first** integrata nel portale: l'autista seleziona l'autorizzazione, sceglie motrice e rimorchio dal Garage, preme "Inizia Viaggio" e il sistema scala il contatore.

**2. Agenzia di Consulenza:**
Il ruolo `agency` copre il caso reale delle pratiche inserite da studi di consulenza e agenzie pratiche auto. L'aggregate `agency_mandates` governa i rapporti partner Ditta -> Agenzia; le `delegations` governano invece i singoli utenti dell'Agenzia che operano nel contesto partner selezionato dalla dashboard.

**3. Parco Veicolare / Garage:**
Il Garage non deve limitarsi ai record tecnici dei mezzi. Va arricchito con libretti, schemi di carico, omologazioni e allegati, così da evitare data-entry ripetitivo e pre-caricare la documentazione corretta su ogni nuova domanda.

**4. Cosa NON copiamo da TEWEB:**
Non adottiamo il routing a nodi chilometrici o menu infiniti di codici strada. Il nostro vantaggio competitivo è un WebGIS consumer-grade: l'utente disegna sulla mappa, OSRM aggancia la strada reale, il backend calcola automaticamente intersezioni, km e proprietari dell'asfalto.

---

## Cantieri e Percorsi Alternativi

Il sistema gestisce i cantieri stradali come vincoli spazio-temporali sul grafo di routing. La logica è:

1. **Segnalazione:** Gli enti gestori di competenza (Comuni, ANAS, Autostrade) inseriscono i cantieri nella propria Scrivania con geometria (tratto stradale), periodo di validità (`valid_from` / `valid_to`) e severità.
2. **Controllo automatico:** Al momento del tracciamento percorso, prima della sottomissione, il backend verifica `ST_Intersects(route.geometry, cantiere.geometry)` in AND con la finestra temporale della domanda. Se c'è sovrapposizione, la pratica non può essere sottomessa con quel percorso/data.
3. **Percorsi alternativi:** In caso di conflitto, il sistema interroga OSRM richiedendo route alternative (parametro `alternatives=true`) con i tratti bloccati esclusi, e le propone all'utente sulla mappa prima della sottomissione.
4. **Modello dati:** tabella `roadworks` — `entity_id`, `geometry` (LINESTRING/POLYGON), `valid_from`, `valid_to`, `description`, `severity` (advisory/restricted/closed), `status` (planned/active/closed).

Questo modulo si inserisce nel WebGIS (Milestone 3) e richiede che gli enti terzi abbiano un pannello di gestione cantieri nella loro Scrivania (Milestone 4).

---

## Forze dell'Ordine

Ruolo `law-enforcement` con accesso in sola lettura a:
- **Verifica sul campo:** ricerca per targa o scansione QR Code del PDF → dettaglio completo della pratica approvata (convoglio, percorso, periodo di validità, ente emittente).
- **Mappa cantieri attivi:** layer WebGIS con i cantieri attivi nel giorno corrente, filtrabili per tratta.
- **Trasporti in transito oggi:** lista delle autorizzazioni attive nella giornata con mappa del percorso.
- **Vista ottimizzata mobile:** layout responsive per utilizzo da dispositivo mobile su strada.

Il ruolo `law-enforcement` non ha accesso alle pratiche in stato `draft`/`submitted` né ai dati fiscali degli utenti oltre quanto strettamente necessario all'identificazione del convoglio autorizzato.

---

## Adeguamento Strategico MIT: "National-Ready" (Proroga al 2027)

La proroga delle linee guida MIT non indica un ripensamento normativo, ma una criticità operativa: i dati infrastrutturali locali non sono ancora digitalizzati in modo completo (ponti, viadotti, portate).

Il progetto PNTE, quindi, va posizionato come **sistema di censimento infrastrutturale** oltre che gestionale autorizzativo. L'alimentazione dei dati da parte di Comuni e Province diventa un contributo diretto alla base informativa richiesta a livello nazionale.

### Implicazioni Tecniche per PNTE

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
  * Setup dei ruoli e permessi separando `system-admin` (infrastruttura, zero accesso alle pratiche) da `admin-ente` (business).
  * Introduzione ruolo `agency` per studi di consulenza multi-cliente.
  * Middleware che impone una `delegation` attiva per tutte le rotte di business.
* **[Task 1.3] Modulo Aziende e Deleghe:**
  * *Migrations* per `companies` (Aziende/Agenzie, con `ateco_code` e `ateco_last_synced_at` per compliance), aggregate `agency_mandates` per il rapporto Ditta -> Agenzia e tabella polimorfica `delegations` per i ruoli applicativi dei singoli utenti.
  * UI (Frontend) per richiesta mandato, approvazione, **Gestione Partner** e switching del contesto cliente per le Agenzie.
  * Motore di generazione PDF da template Blade per Procure Speciali e nomine di `admin-ente`.
  * Motore di validazione `.p7m`/PAdES: verifica integrità, certificato e matching del firmatario con i registri ufficiali.
  * Regole di lifecycle: `valid_from`, `valid_until`, T-30/T-7, sospensione, revoca istantanea e rinnovo semplificato entro i limiti del documento firmato.
  * **ATECO-based agency auto-detection:** Interrogazione PDND Infocamiere per codice ATECO; filtro target 82.99.11 (Fornitura assistenza registrazione autoveicoli) + keyword Legge 264/1991 in descrizione attività; auto-flag `is_agency = true` senza intervento operatore; monthly re-sync job con revoca automatica mandati se ATECO cambia.
* **[Task 1.4] Anagrafica Enti Territoriali:**
  * *Migrations* per la tabella `entities` (Comuni, Province, Anas, Autostrade).
  * Inserimento campi operativi: indirizzi PEC, codici ISTAT, tipologia ente, `is_tenant`, `has_financial_delegation`, `is_capofila`.
* **[Task 1.5] Impostazioni di Sistema (Mail):**
  * Pannello `/system` per `system-admin`: SMTP/IMAP, OIDC, PDND, PagoPA, scheduler e telemetria anonima.
* **[Task 1.6] Sincronizzazione Enti via API IPA (Once Only):**
  * Sviluppo di un Service per l'interrogazione dell'Open Data IPA.
  * Automazione notturna (Scheduler) per l'aggiornamento automatico delle PEC di Comuni, Province e Forze dell'Ordine.
* **[Task 1.7] Validazione Aziende via InfoCamere / INI-PEC tramite PDND (Once Only):**
  * Integrazione API per autocompilazione e validazione dei dati camerali inserendo la P.IVA.
  * Blocco campi sensibili (Ragione Sociale, PEC legale) in modalità read-only per gli utenti.
* **[Task 1.8] Setup step 3 - Invio Mail:**
  * Tasto "Invia email di test" per verifica connettività SMTP.
* **[Task 1.9] Governance Partner e Audit:**
  * Cruscotto **Gestione Partner** per il titolare della Ditta con stato, scadenza, ultimo accesso, volume pratiche e azioni per singola Agenzia.
  * Audit trail con contesto partner (`agency_mandate_id`) e identità dell'Agenzia sulle operazioni sensibili.

### 🚩 Milestone 2: Il Garage Virtuale e il Motore di Calcolo
**Obiettivo:** Strutturare l'inserimento dei dati tecnici dei mezzi e la logica matematica dei tariffari.

* **[Task 2.1] CRUD Veicoli:**
  * *Migrations* per la tabella `vehicles` (Trattori, Rimorchi, Mezzi d'opera).
  * Form UI (Alpine.js/Blade) per l'inserimento di targhe, telaio, massa complessiva e dimensioni.
  * Allegati di flotta: libretto di circolazione, schema di carico, omologazione, documenti tecnici.
* **[Task 2.2] PDF Generator Service + P7M Verification Engine:**
  * Generazione on-demand di PDF formali precompilati (Procura Speciale, nomina `admin-ente`) tramite template Blade.
  * Validazione automatica di file `.p7m`/PAdES: integrità, certificato, CF firmatario, matching con Registro Imprese o IPA.
* **[Task 2.3] Gestione Assi (Core Calcolo):**
  * Sviluppo della logica per definire interassi e carico per asse (struttura JSON nel DB o tabella relazionale dedicata `vehicle_axles`).
* **[Task 2.4] Vehicle Assembly / Convoglio:**
  * Selezione combinata di motrice + rimorchio dal Garage.
  * Aggregazione automatica di masse, assi, sagoma e profilo di usura.
* **[Task 2.5] Motore Tariffario (Rule Engine):**
  * *Migrations* per `tariffs` (coefficienti storicizzati di usura validi da/a).
  * Creazione del *Service* Laravel (`WearCalculationService`) per l'elaborazione delle formule di calcolo usura in base a peso, configurazione assi e chilometri.

### 🚩 Milestone 3: Core Geografico e Routing (WebGIS)
**Obiettivo:** Tracciare la mappa, calcolare le distanze e individuare automaticamente gli enti attraversati.

* **[Task 3.1] Bootstrap Dati Spaziali:**
  * Importazione degli shapefile/GeoJSON dei confini comunali e provinciali abruzzesi in PostgreSQL/PostGIS (campi `geometry(POLYGON|MULTIPOLYGON, 4326)` + indici GiST).
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
* **[Task 3.5] Gestione Cantieri e Intersezione Temporale:**
  * Modello `roadworks` e blocco sottomissione in caso di overlap geometrico e temporale.
* **[Task 3.6] Archivio Regionale Strade (ARS):**
  * Migrazione standard routes, limitazioni sagoma/massa e Controller/View per la gestione enti.
* **[Task 3.7] Overlay ARS su WebGIS:**
  * Web service e script mappa (`route-builder.js`) per visualizzare limitazioni (verde/rosso).

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
* **[Task 4.5] Gestione `tipo_istanza` e Validazione Condizionale:**
  * Enum e logiche di sottomissione variate per autorizzazioni singole, multiple o periodiche.
* **[Task 4.6] Modulo Viaggi (Trips) e Autista:**
  * Tracking stato viaggi (`TripStatus`), UI check-in mobile autista e middleware blocco transito.
  * Preavviso di Viaggio in PWA: selezione autorizzazione, motrice effettiva e rimorchio effettivo dal Garage, scalando il contatore dei viaggi.
  * Distinzione tra veicoli nominali della pratica e veicoli effettivamente usati nel singolo viaggio.
* **[Task 4.7] Radar Forze dell'Ordine:**
  * Mappa Leaflet real-time (polling 30s) per tracciare i convogli in transito oggi.
* **[Task 4.8] IMAP Listener (PEC in ingresso):**
  * Scheduler periodico che legge la casella PEC istituzionale della Provincia via IMAP.
  * Match ID pratica nell'oggetto → scarica PDF allegato → associa al fascicolo digitale.
  * **Non auto-approva**: imposta stato clearance `pending_review` ("Da Valutare" — semaforo giallo).
  * L'operatore Provincia legge il documento e clicca "Approva" o "Rifiuta" con motivazione.
  * Questo azzera il rischio di falsi positivi da risposte condizionate o negative non riconosciute.
* **[Task 4.9] Anti-duplicazione e Resource Locking:**
  * Blocco backend delle pratiche concorrenti sullo stesso mezzo e sulla stessa data/finestra operativa.
  * Messaggio di conflitto che identifica l'Agenzia e il mandato partner che hanno già impegnato la risorsa.

### 🚩 Milestone 5: Chiusura Legale, Pagamenti e Rilascio
**Obiettivo:** Riscossione oneri, protocollazione e generazione del documento legale valido.

* **[Task 5.1] Integrazione PagoPA — Incasso Centralizzato:**
  * Generazione IUV mono-beneficiario intestato alla Provincia Capofila: importo = somma quote enti con `has_financial_delegation = true` + quota Provincia.
  * Guard: IUV generato solo se Provincia di partenza è Tenant attivo.
  * Per quote enti con `has_financial_delegation = false`: scorporo dall'IUV + avviso all'utente con importo e riferimenti ente.
  * Endpoint Webhook RT (Ricevuta Telematica) → transizione automatica `waiting_payment → approved`.
* **[Task 5.2] Generazione PDF (Browsershot):**
  * Creazione della vista *Blade* per il layout ufficiale dell'Autorizzazione (inclusivo di mini-mappa tracciato e tabelle tecniche).
  * Implementazione del *Job* di generazione PDF tramite *Headless Chrome*.
* **[Task 5.3] Interoperabilità Finale (Protocollo e Firma):**
  * Sviluppo client API per la connessione al Protocollo Informatico della Provincia.
  * Integrazione API Firma Remota (es. Aruba PAdES) per la firma del Dirigente.
  * Generazione QR Code dinamico impresso sul PDF per la verifica pubblica da parte delle Forze dell'Ordine.
* **[Task 5.4] Modulo Clearing House e SEPA:**
  * Migration `entities`: aggiunta colonne `is_tenant` (boolean, default false) e `has_financial_delegation` (boolean, default false).
  * Dashboard Ragioneria: riparto centesimale per ente calcolato dall'`entity_breakdown` del `RouteIntersectionService`.
  * Export mensile file **XML SEPA** per bonifici cumulativi automatici verso tutti gli enti deleganti tramite home banking.
  * Onboarding `third-party` (first-visit modal): illustrazione vantaggi delegazione finanziaria, default OFF, grace period fino a mezzanotte dopo disattivazione.
* **[Task 5.5] Modulo Allerta Meteo:**
  * Scheduler per il fetch degli Open Data DPC e sospensione automatica pratiche in caso di allerta arancione/rossa.

### 🚩 Milestone 6: Open Data Portal
**Obiettivo:** Condivisione pubblica delle informazioni su infrastruttura e trasporti.

* **[Task 6.1] Mappa Cantieri Pubblica:**
  * Endpoint `GET /mappa-cantieri` (no auth) per visualizzare i cantieri pubblici.
* **[Task 6.2] API Pubbliche e Download:**
  * Esportazione dati in formato GeoJSON / KML dei lavori stradali (`GET /api/public/roadworks`).
* **[Task 6.3] Dashboard Statistiche Pubbliche:**
  * Chart JS e Heatmap per trasporti per Comune, frequenza percorsi e tempi medi di nulla osta.

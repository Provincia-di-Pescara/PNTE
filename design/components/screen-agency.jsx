/* global React, PNTE */
const { Chip: Cz, Avatar: Avz, Icon: Icz, I: IIz } = window.PNTE;

// ===========================================================================
// AGENZIA DI PRATICHE AUTO — dashboard ruolo `agency`
// ATECO 82.99.11 · Legge 264/1991 · multi-cliente con context switcher
// ===========================================================================

const AGENCY_CLIENTS = [
  { id: "c1", name: "Edilstrade Abruzzo S.r.l.", piva: "01294870681", city: "Pescara",
    mandate: { id: "AM-2025-014", since: "12 gen 2026", expires: "12 gen 2027",
      scenario: "B · P7M", status: "active", days_to_expiry: 252,
      signed_by: "Mario Esposito · LR", scope: "Pratiche TE · viaggi singoli e periodici" },
    apps_open: 4, apps_30: 11, last_login: "2 ore fa" },
  { id: "c2", name: "Trasporti Marsicani S.p.A.", piva: "00873240667", city: "Avezzano",
    mandate: { id: "AM-2025-021", since: "03 feb 2026", expires: "03 feb 2027",
      scenario: "A · click", status: "active", days_to_expiry: 274,
      signed_by: "L. Rappresentante", scope: "Pratiche TE · viaggi singoli" },
    apps_open: 2, apps_30: 6, last_login: "ieri" },
  { id: "c3", name: "Edilcave Costruzioni S.r.l.", piva: "02394810685", city: "Sulmona",
    mandate: { id: "AM-2025-022", since: "18 feb 2026", expires: "18 mag 2026",
      scenario: "B · P7M", status: "expiring", days_to_expiry: 13,
      signed_by: "Antonio Cavallo · LR", scope: "Pratiche TE · solo viaggi singoli" },
    apps_open: 3, apps_30: 8, last_login: "3 gg" },
  { id: "c4", name: "Iannucci Mezzi d'Opera S.n.c.", piva: "01984730672", city: "Chieti",
    mandate: { id: "AM-2026-002", since: "04 apr 2026", expires: "04 apr 2027",
      scenario: "A · click", status: "active", days_to_expiry: 334,
      signed_by: "L. Rappresentante", scope: "Pratiche TE + variazioni" },
    apps_open: 1, apps_30: 4, last_login: "5 gg" },
  { id: "c5", name: "ColdLogistics Adriatica S.r.l.", piva: "02110456789", city: "Pescara",
    mandate: { id: "AM-2026-007", since: "—", expires: "—",
      scenario: "B · P7M (caricato)", status: "validating", days_to_expiry: null,
      signed_by: "in verifica", scope: "Pratiche TE periodiche" },
    apps_open: 0, apps_30: 0, last_login: "—" },
];

function AgencyScreen() {
  const [tab, setTab] = React.useState("overview");
  const [activeClient, setActiveClient] = React.useState("c1");
  const client = AGENCY_CLIENTS.find(c => c.id === activeClient);

  return (
    <div style={{ display: "flex", flexDirection: "column", height: "100%", overflow: "hidden" }}>

      {/* ============= Agency identity strip + Client switcher ============= */}
      <div style={{ borderBottom: "1px solid var(--line)", background: "var(--surface)",
                    padding: "14px 24px", display: "flex", alignItems: "center", gap: 18 }}>
        <div style={{ display: "flex", alignItems: "center", gap: 12 }}>
          <div style={{ width: 38, height: 38, borderRadius: 9, background: "var(--ink)",
                        color: "var(--bg)", display: "flex", alignItems: "center",
                        justifyContent: "center", fontWeight: 700, fontSize: 11.5,
                        letterSpacing: 0.5 }}>SAA</div>
          <div>
            <div style={{ fontSize: 14, fontWeight: 600, lineHeight: 1.2 }}>
              Studio Auto Abruzzo · Agenzia
            </div>
            <div style={{ display: "flex", gap: 6, alignItems: "center", marginTop: 3 }}>
              <Cz tone="amber">ATECO 82.99.11</Cz>
              <Cz>L. 264/1991</Cz>
              <span className="mono" style={{ fontSize: 10.5, color: "var(--ink-3)" }}>
                P.IVA 01234560683
              </span>
            </div>
          </div>
        </div>

        <div style={{ height: 32, width: 1, background: "var(--line)" }} />

        {/* Context switcher: per quale cliente sto lavorando? */}
        <div style={{ display: "flex", alignItems: "center", gap: 10, flex: 1 }}>
          <div style={{ fontSize: 11, color: "var(--ink-3)", letterSpacing: 0.6,
                        textTransform: "uppercase", fontWeight: 500 }}>
            Sto operando per
          </div>
          <select value={activeClient} onChange={e => setActiveClient(e.target.value)}
            style={{ height: 36, padding: "0 12px", border: "1px solid var(--line-2)",
                     borderRadius: 8, background: "var(--surface)", color: "var(--ink)",
                     fontSize: 13, fontWeight: 600, fontFamily: "inherit", minWidth: 280,
                     outline: "none", cursor: "pointer" }}>
            {AGENCY_CLIENTS.map(c => (
              <option key={c.id} value={c.id}>{c.name} — {c.city}</option>
            ))}
          </select>
          {client && (
            <div className="chip" style={{ background: "var(--surface-2)" }}>
              <span style={{ width: 6, height: 6, borderRadius: 999,
                background: client.mandate.status === "active" ? "var(--success)"
                          : client.mandate.status === "expiring" ? "var(--accent)"
                          : "var(--ink-3)" }} />
              mandato {client.mandate.id} · {client.mandate.scenario}
              {client.mandate.status === "expiring" && (
                <span style={{ color: "var(--accent-ink)", fontWeight: 600, marginLeft: 4 }}>
                  · scade fra {client.mandate.days_to_expiry} gg
                </span>
              )}
            </div>
          )}
        </div>

        <button className="btn btn-primary">
          <Icz d={IIz.plus} size={11} /> Nuova pratica per cliente
        </button>
      </div>

      {/* ============= Tabs ============= */}
      <div style={{ borderBottom: "1px solid var(--line)", background: "var(--surface)",
                    padding: "0 24px", display: "flex", gap: 4 }}>
        {[
          ["overview","Overview"],
          ["partners","Gestione Partner"],
          ["pratiche","Pratiche cliente"],
          ["audit","Audit & responsabilità"],
        ].map(([k,l]) => (
          <button key={k} onClick={() => setTab(k)}
            style={{ padding: "12px 14px", border: "none", background: "transparent",
                     borderBottom: tab === k ? "2px solid var(--ink)" : "2px solid transparent",
                     color: tab === k ? "var(--ink)" : "var(--ink-3)",
                     fontWeight: tab === k ? 600 : 500, fontSize: 13, cursor: "pointer",
                     fontFamily: "inherit", marginBottom: -1 }}>
            {l}
          </button>
        ))}
      </div>

      {/* ============= Body ============= */}
      <div style={{ flex: 1, overflow: "auto", padding: "20px 24px" }}>
        {tab === "overview" && <AgencyOverview client={client} />}
        {tab === "partners" && <AgencyPartners />}
        {tab === "pratiche" && <AgencyApplications client={client} />}
        {tab === "audit"    && <AgencyAudit />}
      </div>
    </div>
  );
}

// ============================================================================
function AgencyOverview({ client }) {
  return (
    <>
      {/* KPI multi-cliente */}
      <div style={{ display: "grid", gridTemplateColumns: "repeat(4,1fr)", gap: 12 }}>
        {[
          { l: "Clienti attivi",       v: "5",   sub: "5 mandati attivi · 1 in verifica" },
          { l: "Pratiche aperte",      v: "10",  sub: "convogli in lavorazione" },
          { l: "Pratiche 30gg",        v: "29",  sub: "+ 18% vs mese scorso" },
          { l: "Mandati in scadenza",  v: "1",   sub: "T-30 · da rinnovare", tone: "amber" },
        ].map(s => (
          <div key={s.l} className="card" style={{ padding: 14 }}>
            <div style={{ fontSize: 11, color: "var(--ink-3)", letterSpacing: 0.8,
                          textTransform: "uppercase", fontWeight: 500 }}>{s.l}</div>
            <div className="num" style={{ fontSize: 26, fontWeight: 600, marginTop: 4,
              color: s.tone === "amber" ? "var(--accent-ink)" : "var(--ink)" }}>{s.v}</div>
            <div style={{ fontSize: 11.5, color: "var(--ink-3)", marginTop: 1 }}>{s.sub}</div>
          </div>
        ))}
      </div>

      {/* Banner cliente attivo */}
      {client && (
        <div className="card" style={{ marginTop: 16, padding: 16,
              background: "var(--surface)" }}>
          <div style={{ display: "flex", alignItems: "flex-start", gap: 14 }}>
            <Avz name={client.name} tone="info" />
            <div style={{ flex: 1 }}>
              <div style={{ display: "flex", alignItems: "center", gap: 8 }}>
                <h3 style={{ margin: 0, fontSize: 14.5, fontWeight: 600 }}>{client.name}</h3>
                <span className="chip" style={{ fontSize: 10.5 }}>
                  P.IVA {client.piva}
                </span>
                <span className="chip" style={{ fontSize: 10.5 }}>{client.city}</span>
              </div>
              <div style={{ fontSize: 12, color: "var(--ink-3)", marginTop: 6, lineHeight: 1.5 }}>
                Mandato <span className="mono" style={{ color: "var(--ink-2)" }}>{client.mandate.id}</span>
                {" "}· {client.mandate.scenario}
                {" "}· firmato da <strong style={{ color: "var(--ink-2)", fontWeight: 600 }}>{client.mandate.signed_by}</strong>
                {" "}· valido fino <span className="mono">{client.mandate.expires}</span>
              </div>
              <div style={{ fontSize: 12, color: "var(--ink-2)", marginTop: 4 }}>
                <strong style={{ fontWeight: 600 }}>Scope:</strong> {client.mandate.scope}
              </div>
            </div>
            <div style={{ display: "flex", flexDirection: "column", gap: 6, alignItems: "flex-end" }}>
              <button className="btn btn-sm btn-primary">Avvia pratica per questo cliente</button>
              <button className="btn btn-sm">Apri scheda mandato</button>
            </div>
          </div>
        </div>
      )}

      {/* Conformità ATECO + alert */}
      <div style={{ display: "grid", gridTemplateColumns: "1.4fr 1fr", gap: 14, marginTop: 16 }}>
        <div className="card" style={{ padding: 16 }}>
          <div style={{ fontSize: 11, color: "var(--ink-3)", letterSpacing: 1.2,
                        textTransform: "uppercase", fontWeight: 600, marginBottom: 10 }}>
            Compliance & classificazione
          </div>
          <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 10 }}>
            {[
              ["ATECO","82.99.11","Fornitura assistenza registrazione autoveicoli"],
              ["Legge 264/1991","verificata","keyword in descrizione attività"],
              ["Ult. sync PDND","12 mag · 04:00","Infocamere Registro Imprese"],
              ["Stato is_agency","TRUE","auto-detection passata"],
            ].map(([l,v,sub]) => (
              <div key={l} style={{ padding: "8px 12px", border: "1px solid var(--line)",
                                    borderRadius: 8, background: "var(--surface)" }}>
                <div style={{ fontSize: 10, color: "var(--ink-3)", letterSpacing: 1,
                              textTransform: "uppercase", fontWeight: 500 }}>{l}</div>
                <div className="mono" style={{ fontSize: 12.5, fontWeight: 600, marginTop: 2 }}>{v}</div>
                <div style={{ fontSize: 11, color: "var(--ink-3)", marginTop: 1 }}>{sub}</div>
              </div>
            ))}
          </div>
        </div>

        <div className="card" style={{ padding: 16 }}>
          <div style={{ fontSize: 11, color: "var(--ink-3)", letterSpacing: 1.2,
                        textTransform: "uppercase", fontWeight: 600, marginBottom: 10 }}>
            Avvisi
          </div>
          <div style={{ display: "flex", flexDirection: "column", gap: 8 }}>
            <div style={{ padding: "10px 12px", border: "1px solid color-mix(in oklch, var(--accent), white 60%)",
                          borderRadius: 8, background: "var(--accent-bg)" }}>
              <div style={{ fontSize: 12, fontWeight: 600, color: "var(--accent-ink)",
                            display: "flex", alignItems: "center", gap: 6 }}>
                <Icz d={IIz.alert} size={12} /> T-13 · Edilcave Costruzioni
              </div>
              <div style={{ fontSize: 11.5, color: "var(--accent-ink)", marginTop: 3, lineHeight: 1.4 }}>
                Mandato AM-2025-022 in scadenza. Le condizioni sono immutate ⇒ rinnovo semplificato in portale.
              </div>
              <button className="btn btn-sm" style={{ marginTop: 8, background: "var(--surface)",
                color: "var(--accent-ink)", border: "1px solid var(--accent)" }}>
                Avvia rinnovo
              </button>
            </div>
            <div style={{ padding: "10px 12px", border: "1px solid var(--line)",
                          borderRadius: 8, background: "var(--surface-2)" }}>
              <div style={{ fontSize: 12, fontWeight: 600 }}>
                Conflitto risorsa · Iannucci · PNTE-2026-002391
              </div>
              <div style={{ fontSize: 11.5, color: "var(--ink-3)", marginTop: 3, lineHeight: 1.4 }}>
                Mezzo PE-984ZK già impegnato da <em>Agenzia Adriatica</em> (mandato AM-2026-009) per il 18 mag.
              </div>
            </div>
          </div>
        </div>
      </div>
    </>
  );
}

// ============================================================================
function AgencyPartners() {
  return (
    <>
      <div style={{ display: "flex", alignItems: "flex-end", gap: 14, marginBottom: 14 }}>
        <div style={{ flex: 1 }}>
          <h2 style={{ margin: 0, fontSize: 20, fontWeight: 600, letterSpacing: "-0.01em" }}>
            Gestione Partner · clienti
          </h2>
          <div style={{ fontSize: 12.5, color: "var(--ink-3)", marginTop: 4, maxWidth: 720,
                        lineHeight: 1.5 }}>
            Aggregate <span className="mono">agency_mandates</span>: durata, scope, kill-switch e rinnovo.
            Il valid_until non può superare la data scritta nella Procura Speciale firmata.
          </div>
        </div>
        <button className="btn"><Icz d={IIz.download} size={11} /> Esporta</button>
        <button className="btn btn-primary"><Icz d={IIz.plus} size={11} /> Acquisisci nuovo cliente</button>
      </div>

      <div className="card" style={{ overflow: "hidden" }}>
        <div style={{ display: "grid",
                      gridTemplateColumns: "1.4fr 110px 130px 110px 110px 90px 80px 110px",
                      padding: "10px 14px", fontSize: 10.5, color: "var(--ink-3)",
                      letterSpacing: 0.8, textTransform: "uppercase", fontWeight: 500,
                      background: "var(--surface-2)", borderBottom: "1px solid var(--line)" }}>
          <div>Cliente</div>
          <div>Mandato</div>
          <div>Scenario</div>
          <div>Validità</div>
          <div>Scadenza</div>
          <div>Pratiche</div>
          <div>Stato</div>
          <div>Azioni</div>
        </div>
        {AGENCY_CLIENTS.map((c,i) => (
          <div key={c.id} className="row-hover" style={{ display: "grid",
                gridTemplateColumns: "1.4fr 110px 130px 110px 110px 90px 80px 110px",
                padding: "12px 14px", alignItems: "center", fontSize: 12.5,
                borderBottom: i < AGENCY_CLIENTS.length - 1 ? "1px solid var(--line)" : "none" }}>
            <div>
              <div style={{ fontWeight: 500 }}>{c.name}</div>
              <div className="mono" style={{ fontSize: 11, color: "var(--ink-3)" }}>
                P.IVA {c.piva} · {c.city}
              </div>
            </div>
            <div className="mono" style={{ color: "var(--ink-3)", fontSize: 11.5 }}>{c.mandate.id}</div>
            <div style={{ fontSize: 11.5 }}>{c.mandate.scenario}</div>
            <div className="mono" style={{ fontSize: 11.5, color: "var(--ink-3)" }}>{c.mandate.since}</div>
            <div className="mono" style={{ fontSize: 11.5,
                  color: c.mandate.status === "expiring" ? "var(--accent-ink)" : "var(--ink-3)" }}>
              {c.mandate.expires}
            </div>
            <div className="num">{c.apps_open + " / " + c.apps_30}</div>
            <div>
              <Cz tone={
                c.mandate.status === "active" ? "success"
                : c.mandate.status === "expiring" ? "amber"
                : c.mandate.status === "validating" ? "default"
                : "danger"
              }>
                {c.mandate.status === "active" ? "attivo"
                : c.mandate.status === "expiring" ? "T-" + c.mandate.days_to_expiry
                : c.mandate.status === "validating" ? "P7M…"
                : "sosp."}
              </Cz>
            </div>
            <div style={{ display: "flex", gap: 4, justifyContent: "flex-end" }}>
              <button className="btn btn-sm">Apri</button>
              <button className="btn btn-sm btn-ghost" style={{ width: 24, padding: 0 }}
                title="Sospendi (kill-switch)">
                <Icz d={IIz.more} size={12} />
              </button>
            </div>
          </div>
        ))}
      </div>

      {/* Onboarding nuovo cliente · scenari */}
      <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 12, marginTop: 18 }}>
        <div className="card" style={{ padding: 16 }}>
          <div className="chip" style={{ background: "var(--success-bg)", color: "var(--success)",
                  border: "1px solid color-mix(in oklch, var(--success), white 50%)", fontSize: 10.5 }}>
            Scenario A
          </div>
          <h3 style={{ margin: "8px 0 4px", fontSize: 14, fontWeight: 600 }}>
            Ditta già digitale · click di approvazione
          </h3>
          <div style={{ fontSize: 12, color: "var(--ink-3)", lineHeight: 1.5 }}>
            Richiedi il mandato in piattaforma. Il legale rappresentante della Ditta riceve notifica e approva con un click.
            <span className="mono" style={{ color: "var(--ink-2)" }}> agency_mandate</span> attivo immediatamente.
          </div>
          <button className="btn btn-sm btn-primary" style={{ marginTop: 12 }}>Avvia richiesta</button>
        </div>
        <div className="card" style={{ padding: 16 }}>
          <div className="chip" style={{ background: "var(--accent-bg)", color: "var(--accent-ink)",
                  border: "1px solid color-mix(in oklch, var(--accent), white 60%)", fontSize: 10.5 }}>
            Scenario B
          </div>
          <h3 style={{ margin: "8px 0 4px", fontSize: 14, fontWeight: 600 }}>
            Ditta analogica · Procura Speciale firmata .p7m
          </h3>
          <div style={{ fontSize: 12, color: "var(--ink-3)", lineHeight: 1.5 }}>
            Scegli la data di validità · genera il PDF Procura Speciale precompilato · il LR firma offline ·
            carica il <span className="mono" style={{ color: "var(--ink-2)" }}>.p7m</span>: il sistema verifica
            integrità, certificato e CF firmatario su Registro Imprese.
          </div>
          <div style={{ display: "flex", gap: 6, marginTop: 12 }}>
            <button className="btn btn-sm btn-primary">Genera PDF</button>
            <button className="btn btn-sm">Carica .p7m</button>
          </div>
        </div>
      </div>
    </>
  );
}

// ============================================================================
function AgencyApplications({ client }) {
  if (!client) return null;
  const apps = [
    { id: "PNTE-2026-002417", state: "waiting_clearances", route: "Pescara → Sulmona",
      veh: "PE-432LM + R-9821", when: "18 mag · 06:00", clearances: "3/5", semaforo: "amber" },
    { id: "PNTE-2026-002408", state: "waiting_payment",    route: "Chieti → L'Aquila",
      veh: "PE-981XR + R-7732", when: "20 mag · 04:00", clearances: "5/5", semaforo: "ok" },
    { id: "PNTE-2026-002391", state: "draft",              route: "Pescara → Roseto degli A.",
      veh: "PE-984ZK + R-1244", when: "—",              clearances: "—",   semaforo: "default" },
    { id: "PNTE-2026-002378", state: "approved",           route: "Pescara → Avezzano",
      veh: "PE-432LM + R-7732", when: "11 mag · 05:30", clearances: "5/5", semaforo: "ok" },
  ];
  const stateLabels = {
    draft: ["bozza","default"],
    waiting_clearances: ["attesa NO","amber"],
    waiting_payment: ["attesa pagamento","amber"],
    approved: ["autorizzata","success"],
  };
  return (
    <>
      <div style={{ display: "flex", alignItems: "center", gap: 12, marginBottom: 14 }}>
        <h2 style={{ margin: 0, fontSize: 20, fontWeight: 600 }}>Pratiche · {client.name}</h2>
        <Cz>scope: {client.mandate.scope}</Cz>
        <div style={{ flex: 1 }} />
        <button className="btn"><Icz d={IIz.search} size={11} /> Cerca</button>
        <button className="btn btn-primary"><Icz d={IIz.plus} size={11} /> Nuova pratica</button>
      </div>

      <div className="card" style={{ overflow: "hidden" }}>
        <div style={{ display: "grid",
                      gridTemplateColumns: "180px 130px 1.2fr 1.1fr 130px 80px 70px",
                      padding: "10px 14px", fontSize: 10.5, color: "var(--ink-3)",
                      letterSpacing: 0.8, textTransform: "uppercase", fontWeight: 500,
                      background: "var(--surface-2)", borderBottom: "1px solid var(--line)" }}>
          <div>Pratica</div><div>Stato</div><div>Percorso</div><div>Convoglio</div>
          <div>Quando</div><div>N.O.</div><div></div>
        </div>
        {apps.map((a,i) => (
          <div key={a.id} className="row-hover" style={{ display: "grid",
                gridTemplateColumns: "180px 130px 1.2fr 1.1fr 130px 80px 70px",
                padding: "12px 14px", alignItems: "center", fontSize: 12.5,
                borderBottom: i < apps.length - 1 ? "1px solid var(--line)" : "none" }}>
            <div className="mono">{a.id}</div>
            <Cz tone={stateLabels[a.state][1]}>{stateLabels[a.state][0]}</Cz>
            <div>{a.route}</div>
            <div className="mono" style={{ color: "var(--ink-3)", fontSize: 11.5 }}>{a.veh}</div>
            <div className="mono" style={{ color: "var(--ink-3)", fontSize: 11.5 }}>{a.when}</div>
            <div className="num" style={{
              color: a.semaforo === "amber" ? "var(--accent-ink)"
                   : a.semaforo === "ok" ? "var(--success)" : "var(--ink-3)",
              fontWeight: 600 }}>{a.clearances}</div>
            <div style={{ display: "flex", gap: 4, justifyContent: "flex-end" }}>
              <button className="btn btn-sm">Apri</button>
            </div>
          </div>
        ))}
      </div>

      <div style={{ marginTop: 14, padding: 12, border: "1px dashed var(--line-2)",
                    borderRadius: 9, background: "var(--surface-2)",
                    fontSize: 11.5, color: "var(--ink-3)", lineHeight: 1.5 }}>
        Ogni pratica creata da questa Agenzia salva in audit i campi
        <span className="mono" style={{ color: "var(--ink-2)" }}> agency_mandate_id</span> e
        <span className="mono" style={{ color: "var(--ink-2)" }}> created_by_agency_id</span> per
        attribuzione di responsabilità e KPI per Agenzia.
      </div>
    </>
  );
}

// ============================================================================
function AgencyAudit() {
  const events = [
    ["14 mag · 14:32","E. Marrone","Apertura pratica PNTE-2026-002417 per Edilstrade Abruzzo","AM-2025-014"],
    ["14 mag · 11:08","E. Marrone","Caricato .p7m per ColdLogistics — verifica avviata","AM-2026-007"],
    ["13 mag · 17:51","Sistema","Notifica T-30 inviata a Edilcave Costruzioni","AM-2025-022"],
    ["13 mag · 09:14","E. Marrone","Switch contesto cliente: Iannucci Mezzi d'Opera","AM-2026-002"],
    ["12 mag · 16:48","Sistema","ATECO re-sync · 82.99.11 confermato","—"],
    ["10 mag · 11:02","E. Marrone","Sospensione mandato Adriatica Trasporti (kill-switch)","AM-2025-009"],
  ];
  return (
    <>
      <div style={{ display: "flex", alignItems: "flex-end", gap: 14, marginBottom: 14 }}>
        <div style={{ flex: 1 }}>
          <h2 style={{ margin: 0, fontSize: 20, fontWeight: 600 }}>Audit & responsabilità</h2>
          <div style={{ fontSize: 12.5, color: "var(--ink-3)", marginTop: 4 }}>
            Operazioni sensibili tracciate con contesto partner per attribuzione di responsabilità.
          </div>
        </div>
        <button className="btn"><Icz d={IIz.download} size={11} /> Esporta CSV</button>
      </div>
      <div className="card" style={{ overflow: "hidden" }}>
        <div style={{ display: "grid", gridTemplateColumns: "150px 140px 1fr 130px",
                      padding: "10px 14px", fontSize: 10.5, color: "var(--ink-3)",
                      letterSpacing: 0.8, textTransform: "uppercase", fontWeight: 500,
                      background: "var(--surface-2)", borderBottom: "1px solid var(--line)" }}>
          <div>Quando</div><div>Operatore agenzia</div><div>Operazione</div><div>Mandato</div>
        </div>
        {events.map(([when,who,what,m],i) => (
          <div key={i} style={{ display: "grid", gridTemplateColumns: "150px 140px 1fr 130px",
                padding: "10px 14px", gap: 10, alignItems: "center", fontSize: 12.5,
                borderBottom: i < events.length - 1 ? "1px solid var(--line)" : "none" }}>
            <span className="mono" style={{ color: "var(--ink-3)" }}>{when}</span>
            <span style={{ fontWeight: 500 }}>{who}</span>
            <span style={{ color: "var(--ink-2)" }}>{what}</span>
            <span className="mono" style={{ color: "var(--ink-3)" }}>{m}</span>
          </div>
        ))}
      </div>
    </>
  );
}

window.PNTEScreens = { ...(window.PNTEScreens || {}), AgencyScreen };

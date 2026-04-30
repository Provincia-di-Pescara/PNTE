/* global React, GTE, GTEMap */
const { Chip: Ct, Icon: Ict, I: IIt } = window.GTE;

// ===========================================================================
// Ente Terzo — Comune / ANAS / Autostrade
// Two-up: Nulla Osta da rilasciare + Cantieri di competenza
// ===========================================================================
function ThirdPartyScreen() {
  const requests = [
    { id: "GTE-2026-002417", req: "Adriatica Logistic S.r.l.", km: "3,1 km su SS17",
      from: "Pescara", to: "Sulmona", weight: "92,1 t", deadline: "2g 14h", status: "pending" },
    { id: "GTE-2026-002418", req: "Ferraris Trasporti S.p.A.", km: "1,8 km via Cavour",
      from: "Pescara", to: "Sulmona", weight: "76,4 t", deadline: "4g 02h", status: "pending" },
    { id: "GTE-2026-002411", req: "Edilstrade Abruzzo", km: "0,9 km centro",
      from: "Chieti", to: "Avezzano", weight: "64,8 t", deadline: "scaduto",
      status: "overdue" },
  ];

  const cantieri = [
    { id: "RW-2086", title: "Riasfaltatura SS17 km 14+200", from: "10 mag", to: "20 mag",
      severity: "restricted", impact: "3 pratiche" },
    { id: "RW-2079", title: "Rifacimento Ponte Tirino", from: "01 mag", to: "30 giu",
      severity: "closed", impact: "8 pratiche" },
    { id: "RW-2092", title: "Restringimento Via Tiburtina", from: "15 mag", to: "16 mag",
      severity: "advisory", impact: "1 pratica" },
  ];

  return (
    <div style={{ padding: 20, display: "flex", flexDirection: "column", gap: 16, height: "100%", overflow: "auto" }}>
      <div style={{ display: "flex", alignItems: "flex-end", gap: 16 }}>
        <div>
          <div style={{ fontSize: 10.5, letterSpacing: 1.4, color: "var(--ink-3)", textTransform: "uppercase" }}>
            Scrivania · Comune di Sulmona
          </div>
          <h1 style={{ margin: "4px 0 0", fontSize: 22, fontWeight: 600, letterSpacing: "-0.015em" }}>
            Pareri di competenza
          </h1>
          <div style={{ marginTop: 4, fontSize: 12.5, color: "var(--ink-3)" }}>
            Codice ISTAT 066099 · PEC sulmona@pec.it
          </div>
        </div>
        <div style={{ flex: 1 }} />
        <div className="chip" style={{ background: "var(--accent-bg)", color: "var(--accent-ink)",
                                       border: "1px solid color-mix(in oklch, var(--accent), white 70%)" }}>
          <Ict d={IIt.bell} size={11} /> 1 scaduto · 2 in coda
        </div>
      </div>

      {/* Tabs */}
      <div style={{ display: "flex", gap: 4, borderBottom: "1px solid var(--line)" }}>
        {[
          { l: "Nulla Osta", n: 3, active: true },
          { l: "Cantieri", n: 3 },
          { l: "Storico pareri", n: 89 },
          { l: "Tariffario", n: null },
        ].map(t => (
          <button key={t.l} style={{
            padding: "8px 14px", border: "none", background: "transparent",
            borderBottom: t.active ? "2px solid var(--ink)" : "2px solid transparent",
            color: t.active ? "var(--ink)" : "var(--ink-3)",
            fontWeight: t.active ? 600 : 500, fontSize: 13,
            cursor: "pointer", marginBottom: -1, fontFamily: "inherit",
            display: "flex", alignItems: "center", gap: 6,
          }}>
            {t.l}
            {t.n != null && <Ct tone={t.active ? "amber" : "default"}>{t.n}</Ct>}
          </button>
        ))}
      </div>

      <div style={{ display: "grid", gridTemplateColumns: "1.4fr 1fr", gap: 16 }}>
        {/* Nulla osta inbox */}
        <div className="card" style={{ overflow: "hidden" }}>
          <div style={{ padding: "12px 16px", borderBottom: "1px solid var(--line)",
                        display: "flex", alignItems: "center" }}>
            <div style={{ fontSize: 13, fontWeight: 600 }}>Richieste in attesa</div>
            <div style={{ flex: 1 }} />
            <button className="btn btn-sm">Ordina · Scadenza</button>
          </div>
          {requests.map((r, i) => (
            <div key={r.id} style={{
              padding: "14px 16px",
              borderBottom: i < requests.length - 1 ? "1px solid var(--line)" : "none",
            }}>
              <div style={{ display: "flex", alignItems: "center", gap: 10, marginBottom: 6 }}>
                <span className="mono" style={{ fontSize: 11.5, fontWeight: 600 }}>{r.id}</span>
                <Ct tone={r.status === "overdue" ? "danger" : "amber"}>
                  {r.status === "overdue" ? "Scaduto" : `Risposta entro ${r.deadline}`}
                </Ct>
                <div style={{ flex: 1 }} />
                <span style={{ fontSize: 11.5, color: "var(--ink-3)" }}>{r.weight}</span>
              </div>
              <div style={{ fontSize: 13, fontWeight: 500 }}>{r.req}</div>
              <div style={{ fontSize: 12, color: "var(--ink-3)", marginTop: 2 }}>
                {r.from} → {r.to} · <strong style={{ color: "var(--ink-2)" }}>{r.km}</strong> di tratta nel territorio
              </div>
              <div style={{ display: "flex", gap: 8, marginTop: 10 }}>
                <button className="btn btn-sm">Vedi tratta su mappa</button>
                <button className="btn btn-sm">Documentazione</button>
                <div style={{ flex: 1 }} />
                <button className="btn btn-sm" style={{ color: "var(--danger)" }}>
                  <Ict d={IIt.x} size={11} /> Rifiuta
                </button>
                <button className="btn btn-sm btn-primary">
                  <Ict d={IIt.check} size={11} stroke={2} /> Approva
                </button>
              </div>
            </div>
          ))}
        </div>

        {/* Cantieri */}
        <div className="card" style={{ display: "flex", flexDirection: "column", overflow: "hidden" }}>
          <div style={{ padding: "12px 16px", borderBottom: "1px solid var(--line)",
                        display: "flex", alignItems: "center" }}>
            <div style={{ fontSize: 13, fontWeight: 600 }}>Cantieri di competenza</div>
            <div style={{ flex: 1 }} />
            <button className="btn btn-sm btn-primary">
              <Ict d={IIt.plus} size={11} /> Nuovo
            </button>
          </div>

          <div style={{ padding: 12 }}>
            <GTEMap height={180} variant="minimal" showLegend={false} showZoom={false}
                    showCompass={false} showLabels={false} showRoute={false} showBridges={false} />
          </div>

          {cantieri.map((c, i) => {
            const sevColor = c.severity === "closed" ? "var(--danger)"
                           : c.severity === "restricted" ? "var(--accent)"
                           : "var(--info)";
            const sevLabel = c.severity === "closed" ? "Chiuso"
                           : c.severity === "restricted" ? "Limitato"
                           : "Informativo";
            return (
              <div key={c.id} style={{
                padding: "12px 16px",
                borderTop: "1px solid var(--line)",
                display: "flex", alignItems: "center", gap: 12,
              }}>
                <div style={{
                  width: 8, alignSelf: "stretch", marginRight: -4,
                  background: sevColor, opacity: 0.85, borderRadius: 2,
                }} />
                <div style={{ flex: 1, minWidth: 0 }}>
                  <div style={{ display: "flex", alignItems: "center", gap: 8 }}>
                    <span className="mono" style={{ fontSize: 11, color: "var(--ink-3)" }}>{c.id}</span>
                    <span style={{ fontSize: 11, color: sevColor, fontWeight: 600 }}>{sevLabel}</span>
                  </div>
                  <div style={{ fontSize: 13, fontWeight: 500, marginTop: 2 }}>{c.title}</div>
                  <div style={{ fontSize: 11.5, color: "var(--ink-3)", marginTop: 2,
                                display: "flex", gap: 12 }}>
                    <span><Ict d={IIt.clock} size={10} /> {c.from} → {c.to}</span>
                    <span>· Impatta {c.impact}</span>
                  </div>
                </div>
                <button className="btn btn-sm">Modifica</button>
              </div>
            );
          })}
        </div>
      </div>
    </div>
  );
}

window.GTEScreens = { ...(window.GTEScreens || {}), ThirdPartyScreen };

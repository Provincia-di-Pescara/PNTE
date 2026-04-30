/* global React, GTE, GTEMap, GTEShell */
const { Chip: C, StatusPill: SP, Avatar: Av, Icon: Ic, I: II, APPLICATIONS: APPS, ENTITIES_CROSSED: EC } = window.GTE;

// ===========================================================================
// 1) WIZARD — Tracciamento percorso (HERO, multi-role: cittadino/azienda)
// ===========================================================================
function WizardScreen() {
  const steps = [
    { n: 1, label: "Azienda", done: true },
    { n: 2, label: "Convoglio", done: true },
    { n: 3, label: "Percorso", done: false, current: true },
    { n: 4, label: "Riepilogo", done: false },
    { n: 5, label: "Pagamento", done: false },
  ];

  return (
    <div style={{ padding: 20, display: "flex", flexDirection: "column", gap: 16, height: "100%", overflow: "auto" }}>
      {/* breadcrumb + actions */}
      <div style={{ display: "flex", alignItems: "center", gap: 12 }}>
        <div style={{ fontSize: 12, color: "var(--ink-3)" }}>
          Pratiche · <span style={{ color: "var(--ink-2)" }}>Nuova domanda</span>
        </div>
        <div style={{ flex: 1 }} />
        <button className="btn btn-ghost btn-sm">Salva bozza</button>
        <button className="btn btn-sm">Annulla</button>
      </div>

      <div style={{ display: "flex", alignItems: "flex-start", gap: 12 }}>
        <div>
          <div style={{ fontSize: 10.5, letterSpacing: 1.4, color: "var(--ink-3)", textTransform: "uppercase" }}>
            Pratica · GTE-2026-002419 · Bozza
          </div>
          <h1 style={{ margin: "4px 0 0", fontSize: 22, fontWeight: 600, letterSpacing: "-0.015em" }}>
            Tracciamento percorso
          </h1>
          <p style={{ margin: "4px 0 0", color: "var(--ink-3)", fontSize: 13, maxWidth: 560 }}>
            Disegna il percorso sulla mappa. Il sistema lo aggancia alla rete stradale (snap-to-road),
            calcola le intersezioni con i confini comunali e individua automaticamente gli enti competenti.
          </p>
        </div>
      </div>

      {/* stepper */}
      <div className="card" style={{ padding: 14, display: "flex", alignItems: "center", gap: 6 }}>
        {steps.map((s, i) => (
          <React.Fragment key={s.n}>
            <div style={{ display: "flex", alignItems: "center", gap: 10, flex: "0 0 auto" }}>
              <div style={{
                width: 24, height: 24, borderRadius: 999,
                display: "flex", alignItems: "center", justifyContent: "center",
                fontSize: 11, fontWeight: 600,
                background: s.done ? "var(--ink)" : s.current ? "var(--accent)" : "var(--surface-2)",
                color: s.done ? "var(--bg)" : s.current ? "oklch(0.20 0.03 60)" : "var(--ink-3)",
                border: s.done || s.current ? "none" : "1px solid var(--line)",
              }}>
                {s.done ? <Ic d={II.check} size={12} stroke={2} /> : s.n}
              </div>
              <span style={{
                fontSize: 13,
                fontWeight: s.current ? 600 : 500,
                color: s.current ? "var(--ink)" : s.done ? "var(--ink-2)" : "var(--ink-3)",
              }}>
                {s.label}
              </span>
            </div>
            {i < steps.length - 1 && (
              <div style={{ flex: 1, height: 1, background: "var(--line)", margin: "0 6px" }} />
            )}
          </React.Fragment>
        ))}
      </div>

      {/* main split: map + side panel */}
      <div style={{ display: "grid", gridTemplateColumns: "1fr 360px", gap: 16, minHeight: 520 }}>
        {/* map area with toolbar */}
        <div className="card" style={{ padding: 12, display: "flex", flexDirection: "column", gap: 10 }}>
          <div style={{ display: "flex", alignItems: "center", gap: 8 }}>
            <div className="chip" style={{ background: "var(--accent-bg)", color: "var(--accent-ink)",
                                            border: "1px solid color-mix(in oklch, var(--accent), white 70%)" }}>
              <Ic d={II.bolt} size={11} />
              Modalità: snap-to-road · OSRM
            </div>
            <div style={{ flex: 1 }} />
            <button className="btn btn-sm"><Ic d={II.layers} size={12} /> Layer</button>
            <button className="btn btn-sm"><Ic d={II.cone} size={12} /> Cantieri</button>
            <button className="btn btn-sm"><Ic d={II.refresh} size={12} /> Ricalcola</button>
            <button className="btn btn-sm btn-primary"><Ic d={II.share} size={12} /> Condividi</button>
          </div>

          <div style={{ position: "relative", flex: 1 }}>
            <GTEMap height={520} />

            {/* roadwork callout */}
            <div style={{
              position: "absolute", top: 110, left: "44%",
              transform: "translateX(-30%)",
              background: "var(--surface)", border: "1px solid var(--line)",
              borderRadius: 8, padding: "10px 12px", width: 240,
              boxShadow: "0 6px 20px -8px rgba(0,0,0,.18)",
              fontSize: 12.5,
            }}>
              <div style={{ display: "flex", alignItems: "center", gap: 6, marginBottom: 4 }}>
                <span style={{ width: 6, height: 6, borderRadius: 999, background: "var(--accent)" }} />
                <strong style={{ fontSize: 12 }}>Cantiere su SS17</strong>
                <span style={{ marginLeft: "auto", fontSize: 10.5, color: "var(--ink-3)" }} className="mono">RW-2086</span>
              </div>
              <div style={{ color: "var(--ink-2)", lineHeight: 1.45 }}>
                Riasfaltatura km 14+200 → 16+800. Severità <strong>limitata</strong>.<br/>
                Attivo fino al 20 mag 2026.
              </div>
              <div style={{ display: "flex", gap: 6, marginTop: 8 }}>
                <button className="btn btn-sm" style={{ flex: 1 }}>Dettagli</button>
                <button className="btn btn-sm btn-accent" style={{ flex: 1 }}>Usa alternativa</button>
              </div>
            </div>
          </div>

          {/* metric strip */}
          <div style={{ display: "grid", gridTemplateColumns: "repeat(5, 1fr)", gap: 0,
                        border: "1px solid var(--line)", borderRadius: 8, overflow: "hidden" }}>
            {[
              { l: "Lunghezza", v: "84,2 km", sub: "snap-to-road" },
              { l: "Tempo stimato", v: "1h 48m", sub: "70 km/h medi" },
              { l: "Enti attraversati", v: "6", sub: "auto-rilevati" },
              { l: "Ponti su tratto", v: "2", sub: "1 AINOP idoneo" },
              { l: "Cantieri in conflitto", v: "1", sub: "alternativa disponibile",
                tone: "amber" },
            ].map((m, i) => (
              <div key={i} style={{
                padding: "10px 14px",
                borderRight: i < 4 ? "1px solid var(--line)" : "none",
                background: m.tone === "amber" ? "var(--accent-bg)" : "var(--surface)",
              }}>
                <div style={{ fontSize: 10.5, color: "var(--ink-3)",
                              textTransform: "uppercase", letterSpacing: 1, fontWeight: 500 }}>
                  {m.l}
                </div>
                <div className="num" style={{ fontSize: 18, fontWeight: 600, marginTop: 2 }}>{m.v}</div>
                <div style={{ fontSize: 11, color: "var(--ink-3)" }}>{m.sub}</div>
              </div>
            ))}
          </div>
        </div>

        {/* side panel: enti attraversati */}
        <div className="card" style={{ padding: 0, display: "flex", flexDirection: "column" }}>
          <div style={{ padding: "14px 16px", borderBottom: "1px solid var(--line)" }}>
            <div style={{ fontSize: 13, fontWeight: 600 }}>Enti attraversati</div>
            <div style={{ fontSize: 11.5, color: "var(--ink-3)", marginTop: 2 }}>
              Auto-detection da intersezione spaziale ST_Intersection · MariaDB GIS
            </div>
          </div>
          <div style={{ overflow: "auto", flex: 1 }}>
            {EC.map((e, i) => (
              <div key={e.name} style={{
                padding: "12px 16px",
                borderBottom: i < EC.length - 1 ? "1px solid var(--line)" : "none",
                display: "flex", alignItems: "center", gap: 10,
              }}>
                <div style={{
                  width: 28, height: 28, borderRadius: 7,
                  background: e.status === "issuer" ? "var(--accent-bg)" :
                              e.status === "approved" ? "var(--success-bg)" :
                              "var(--surface-2)",
                  color: e.status === "issuer" ? "var(--accent-ink)" :
                         e.status === "approved" ? "var(--success)" :
                         "var(--ink-3)",
                  display: "flex", alignItems: "center", justifyContent: "center",
                  border: "1px solid var(--line)",
                }}>
                  <Ic d={e.type === "Provincia" ? II.flag : e.type === "Comune" ? II.pin : II.layers} size={13} />
                </div>
                <div style={{ flex: 1, minWidth: 0 }}>
                  <div style={{ fontSize: 12.5, fontWeight: 600, lineHeight: 1.25 }}>{e.name}</div>
                  <div style={{ fontSize: 11, color: "var(--ink-3)", display: "flex", gap: 8 }}>
                    <span>{e.type}</span>
                    <span>·</span>
                    <span className="num">{e.km.toFixed(1)} km</span>
                  </div>
                </div>
                {e.status === "issuer" && <C tone="amber">Capofila</C>}
                {e.status === "approved" && <C tone="success">Pre-OK</C>}
                {e.status === "pending" && <C tone="default">N.O. richiesto</C>}
              </div>
            ))}
          </div>

          {/* AINOP */}
          <div style={{ padding: 14, borderTop: "1px solid var(--line)",
                        background: "var(--surface-2)" }}>
            <div style={{ display: "flex", alignItems: "center", gap: 8 }}>
              <Ic d={II.bridge} size={14} />
              <strong style={{ fontSize: 12.5 }}>AINOP · 2/2 ponti idonei</strong>
              <span style={{ marginLeft: "auto" }} className="mono"
                    style={{ fontSize: 10.5, color: "var(--ink-3)" }}>via PDND</span>
            </div>
            <div style={{ fontSize: 11.5, color: "var(--ink-3)", marginTop: 4, lineHeight: 1.45 }}>
              Convoglio 92,1 t · capacità minima rilevata 60 t. Tutti i manufatti
              sul tratto sono compatibili.
            </div>
          </div>

          {/* footer cta */}
          <div style={{ padding: 14, borderTop: "1px solid var(--line)",
                        display: "flex", gap: 8 }}>
            <button className="btn" style={{ flex: 1 }}>Indietro</button>
            <button className="btn btn-primary" style={{ flex: 1.4 }}>
              Conferma percorso <Ic d={II.arrow} size={12} stroke={2} />
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}

window.GTEScreens = { ...(window.GTEScreens || {}), WizardScreen };

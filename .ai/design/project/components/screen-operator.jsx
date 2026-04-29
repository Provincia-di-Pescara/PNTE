/* global React, GTE, GTEMap */
const { Chip: Co, StatusPill: SPp, Avatar: Avp, Icon: Icp, I: IIp, APPLICATIONS: APPSp, ENTITIES_CROSSED: ECp } = window.GTE;

// ===========================================================================
// Operator (Provincia di Pescara) — Dashboard with table + state timeline
// ===========================================================================
function OperatorScreen() {
  const [selected, setSelected] = React.useState("GTE-2026-002417");
  const sel = APPSp.find(a => a.id === selected) || APPSp[1];

  // KPIs
  const kpis = [
    { label: "Pratiche aperte", value: "47", sub: "+6 questa settimana" },
    { label: "In attesa nulla osta", value: "18", sub: "9 enti coinvolti", tone: "amber" },
    { label: "In attesa pagamento", value: "11", sub: "€ 4.812 attesi" },
    { label: "Approvate (mese)", value: "112", sub: "+24% vs aprile", tone: "success" },
  ];

  // state machine
  const states = [
    { key: "draft",              label: "Bozza",              done: true,  date: "10 mag · 09:12" },
    { key: "submitted",          label: "Inviata",            done: true,  date: "10 mag · 11:48" },
    { key: "instruct",           label: "Istruttoria",        done: true,  date: "11 mag · 08:30", actor: "M. Cipriani" },
    { key: "waiting_clearances", label: "Nulla osta",         current: true, date: "11 mag · 14:02", sub: "5 di 7 ricevuti" },
    { key: "waiting_payment",    label: "Pagamento",          done: false },
    { key: "approved",           label: "Autorizzazione",     done: false },
  ];

  return (
    <div style={{ padding: 20, display: "flex", flexDirection: "column", gap: 16, height: "100%", overflow: "auto" }}>
      <div style={{ display: "flex", alignItems: "flex-end", gap: 16 }}>
        <div>
          <div style={{ fontSize: 10.5, letterSpacing: 1.4, color: "var(--ink-3)", textTransform: "uppercase" }}>
            Scrivania · Provincia di Pescara
          </div>
          <h1 style={{ margin: "4px 0 0", fontSize: 22, fontWeight: 600, letterSpacing: "-0.015em" }}>
            Istruttoria pratiche
          </h1>
        </div>
        <div style={{ flex: 1 }} />
        <button className="btn"><Icp d={IIp.download} size={12} /> Esporta CSV</button>
        <button className="btn btn-primary"><Icp d={IIp.plus} size={12} /> Nuova pratica</button>
      </div>

      {/* KPIs */}
      <div style={{ display: "grid", gridTemplateColumns: "repeat(4, 1fr)", gap: 12 }}>
        {kpis.map(k => (
          <div key={k.label} className="card" style={{ padding: 14 }}>
            <div style={{ fontSize: 11, color: "var(--ink-3)", letterSpacing: 0.8,
                          textTransform: "uppercase", fontWeight: 500 }}>
              {k.label}
            </div>
            <div className="num" style={{ fontSize: 26, fontWeight: 600, marginTop: 4,
                                          color: k.tone === "amber" ? "var(--accent-ink)"
                                               : k.tone === "success" ? "var(--success)"
                                               : "var(--ink)" }}>
              {k.value}
            </div>
            <div style={{ fontSize: 11.5, color: "var(--ink-3)", marginTop: 2 }}>{k.sub}</div>
          </div>
        ))}
      </div>

      <div style={{ display: "grid", gridTemplateColumns: "1.4fr 1fr", gap: 16 }}>
        {/* Table */}
        <div className="card" style={{ overflow: "hidden" }}>
          {/* table toolbar */}
          <div style={{ padding: "10px 14px", borderBottom: "1px solid var(--line)",
                        display: "flex", alignItems: "center", gap: 8 }}>
            <div style={{ fontSize: 13, fontWeight: 600 }}>Pratiche · Ultime 30</div>
            <Co tone="default">{APPSp.length}</Co>
            <div style={{ flex: 1 }} />
            <button className="btn btn-sm"><Icp d={IIp.filter} size={12} /> Filtri</button>
            <button className="btn btn-sm"><Icp d={IIp.refresh} size={12} /></button>
          </div>

          <div style={{ overflow: "auto", maxHeight: 460 }}>
            <table style={{ width: "100%", borderCollapse: "collapse", fontSize: 12.5 }}>
              <thead>
                <tr style={{ background: "var(--surface-2)",
                             color: "var(--ink-3)", fontSize: 10.5,
                             textTransform: "uppercase", letterSpacing: 1 }}>
                  {["Pratica", "Richiedente", "Tratta", "Stato", "Data", "Importo"].map(h => (
                    <th key={h} style={{ textAlign: "left", padding: "8px 12px",
                                         fontWeight: 500,
                                         borderBottom: "1px solid var(--line)" }}>
                      {h}
                    </th>
                  ))}
                </tr>
              </thead>
              <tbody>
                {APPSp.map(a => {
                  const isSel = a.id === selected;
                  return (
                    <tr key={a.id} onClick={() => setSelected(a.id)} className="row-hover"
                        style={{ cursor: "pointer",
                                 background: isSel ? "var(--accent-bg)" : "transparent",
                                 borderLeft: isSel ? "3px solid var(--accent)" : "3px solid transparent" }}>
                      <td style={{ padding: "10px 12px", borderBottom: "1px solid var(--line)" }}>
                        <div className="mono" style={{ fontSize: 11.5, fontWeight: 600 }}>{a.id}</div>
                        <div style={{ fontSize: 11, color: "var(--ink-3)" }}>{a.plate} · {a.weight} t · {a.axles} assi</div>
                      </td>
                      <td style={{ padding: "10px 12px", borderBottom: "1px solid var(--line)" }}>
                        <div style={{ fontWeight: 500 }}>{a.company}</div>
                      </td>
                      <td style={{ padding: "10px 12px", borderBottom: "1px solid var(--line)" }}>
                        <div>{a.from}</div>
                        <div style={{ fontSize: 11, color: "var(--ink-3)" }}>→ {a.to} · {a.km.toFixed(1)} km</div>
                      </td>
                      <td style={{ padding: "10px 12px", borderBottom: "1px solid var(--line)" }}>
                        <SPp state={a.state} />
                      </td>
                      <td style={{ padding: "10px 12px", borderBottom: "1px solid var(--line)",
                                   color: "var(--ink-2)" }}>
                        {a.day}
                      </td>
                      <td style={{ padding: "10px 12px", borderBottom: "1px solid var(--line)",
                                   textAlign: "right" }} className="num">
                        {a.fee ? `€ ${a.fee.toFixed(2)}` : "—"}
                      </td>
                    </tr>
                  );
                })}
              </tbody>
            </table>
          </div>
        </div>

        {/* Detail panel — state machine + actions */}
        <div className="card" style={{ display: "flex", flexDirection: "column", overflow: "hidden" }}>
          <div style={{ padding: "12px 16px", borderBottom: "1px solid var(--line)",
                        display: "flex", alignItems: "center", gap: 10 }}>
            <div style={{ flex: 1 }}>
              <div className="mono" style={{ fontSize: 11, color: "var(--ink-3)" }}>{sel.id}</div>
              <div style={{ fontWeight: 600, fontSize: 14 }}>{sel.company}</div>
            </div>
            <SPp state={sel.state} />
          </div>

          {/* mini map */}
          <div style={{ padding: 12 }}>
            <GTEMap height={180} variant="minimal" showLegend={false} showZoom={false}
                    showCompass={false} showLabels={false} showRoadworks={false} showBridges={false} />
          </div>

          <div style={{ padding: "0 16px 8px", display: "grid",
                        gridTemplateColumns: "repeat(3, 1fr)", gap: 8, fontSize: 12 }}>
            <div>
              <div style={{ color: "var(--ink-3)", fontSize: 10.5,
                            textTransform: "uppercase", letterSpacing: 1 }}>Tratta</div>
              <div>{sel.from} → {sel.to}</div>
            </div>
            <div>
              <div style={{ color: "var(--ink-3)", fontSize: 10.5,
                            textTransform: "uppercase", letterSpacing: 1 }}>Convoglio</div>
              <div className="num">{sel.weight} t · {sel.axles} assi</div>
            </div>
            <div>
              <div style={{ color: "var(--ink-3)", fontSize: 10.5,
                            textTransform: "uppercase", letterSpacing: 1 }}>Validità</div>
              <div>{sel.day}</div>
            </div>
          </div>

          {/* Timeline */}
          <div style={{ padding: "10px 16px 14px", borderTop: "1px solid var(--line)",
                        marginTop: 6 }}>
            <div style={{ fontSize: 11, color: "var(--ink-3)", letterSpacing: 1,
                          textTransform: "uppercase", marginBottom: 10 }}>
              Macchina a stati
            </div>
            <div style={{ position: "relative", paddingLeft: 18 }}>
              <div style={{ position: "absolute", left: 8, top: 4, bottom: 4,
                            width: 1, background: "var(--line)" }} />
              {states.map((s, i) => {
                const dotColor = s.done ? "var(--ink)" : s.current ? "var(--accent)" : "var(--surface-2)";
                const dotBorder = s.done || s.current ? "none" : "1px solid var(--line-2)";
                return (
                  <div key={s.key} style={{ position: "relative", paddingBottom: i < states.length - 1 ? 14 : 0 }}>
                    <div style={{
                      position: "absolute", left: -14, top: 2,
                      width: 12, height: 12, borderRadius: 999,
                      background: dotColor, border: dotBorder,
                      boxShadow: s.current ? "0 0 0 4px var(--accent-bg)" : "none",
                    }} />
                    <div style={{ display: "flex", alignItems: "baseline", gap: 8 }}>
                      <div style={{ fontWeight: s.current ? 600 : 500, fontSize: 13,
                                    color: s.done || s.current ? "var(--ink)" : "var(--ink-3)" }}>
                        {s.label}
                      </div>
                      {s.actor && <span style={{ fontSize: 11, color: "var(--ink-3)" }}>· {s.actor}</span>}
                      <div style={{ flex: 1 }} />
                      <div style={{ fontSize: 11, color: "var(--ink-3)" }} className="mono">{s.date || ""}</div>
                    </div>
                    {s.sub && (
                      <div style={{ fontSize: 11.5, color: "var(--accent-ink)", marginTop: 2,
                                    display: "flex", alignItems: "center", gap: 6 }}>
                        <span style={{ width: 6, height: 6, background: "var(--accent)", borderRadius: 999 }} />
                        {s.sub}
                      </div>
                    )}
                  </div>
                );
              })}
            </div>
          </div>

          <div style={{ flex: 1 }} />
          <div style={{ padding: 12, borderTop: "1px solid var(--line)",
                        display: "flex", gap: 8 }}>
            <button className="btn" style={{ flex: 1 }}>Apri PDF bozza</button>
            <button className="btn btn-primary" style={{ flex: 1.4 }}>Sollecita enti</button>
          </div>
        </div>
      </div>
    </div>
  );
}

window.GTEScreens = { ...(window.GTEScreens || {}), OperatorScreen };

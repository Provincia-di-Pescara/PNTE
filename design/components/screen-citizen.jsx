/* global React, PNTE */
const { Chip: Cg, StatusPill: SPg, Icon: Icg, I: IIg, APPLICATIONS: APPSg } = window.PNTE;

// ===========================================================================
// Cittadino / Azienda — Le mie pratiche + Garage Virtuale
// ===========================================================================
function CitizenScreen() {
  const garage = [
    { type: "Motrice", model: "Volvo FH16 750 8x4", plate: "FT 728 ZR", axles: 4, mass: "32,0 t", primary: true },
    { type: "Rimorchio", model: "Cometto MSPE", plate: "FT 728 ZR/R", axles: 6, mass: "44,4 t", primary: false },
    { type: "Motrice", model: "Mercedes Actros 4163", plate: "FT 304 LM", axles: 4, mass: "30,5 t", primary: false },
    { type: "Mezzo d'opera", model: "Liebherr LTM 1500", plate: "FT 219 RX", axles: 8, mass: "96,0 t", primary: false },
  ];

  // axle distribution preview
  const axles = [12.0, 12.0, 11.5, 11.5, 7.0, 7.0, 7.5, 7.5];

  const myApps = APPSg.slice(0, 4);

  return (
    <div style={{ padding: 20, display: "flex", flexDirection: "column", gap: 16, height: "100%", overflow: "auto" }}>
      <div style={{ display: "flex", alignItems: "flex-end", gap: 16 }}>
        <div>
          <div style={{ fontSize: 10.5, letterSpacing: 1.4, color: "var(--ink-3)", textTransform: "uppercase" }}>
            Ferraris Trasporti S.p.A. · P.IVA 01428360689
          </div>
          <h1 style={{ margin: "4px 0 0", fontSize: 22, fontWeight: 600, letterSpacing: "-0.015em" }}>
            Le mie pratiche
          </h1>
        </div>
        <div style={{ flex: 1 }} />
        <button className="btn"><Icg d={IIg.user} size={12} /> Deleghe</button>
        <button className="btn btn-primary"><Icg d={IIg.plus} size={12} /> Nuova domanda</button>
      </div>

      {/* mini KPIs */}
      <div style={{ display: "grid", gridTemplateColumns: "repeat(4, 1fr)", gap: 12 }}>
        {[
          { l: "In corso", v: "3" },
          { l: "Approvate (anno)", v: "47" },
          { l: "Speso 2026", v: "€ 12.480", sub: "indennizzi usura" },
          { l: "Mezzi a garage", v: "4", sub: "1 motrice primaria" },
        ].map(k => (
          <div key={k.l} className="card" style={{ padding: 14 }}>
            <div style={{ fontSize: 11, color: "var(--ink-3)", letterSpacing: 0.8,
                          textTransform: "uppercase", fontWeight: 500 }}>{k.l}</div>
            <div className="num" style={{ fontSize: 24, fontWeight: 600, marginTop: 4 }}>{k.v}</div>
            {k.sub && <div style={{ fontSize: 11.5, color: "var(--ink-3)" }}>{k.sub}</div>}
          </div>
        ))}
      </div>

      <div style={{ display: "grid", gridTemplateColumns: "1.3fr 1fr", gap: 16 }}>
        {/* Pratiche */}
        <div className="card" style={{ overflow: "hidden" }}>
          <div style={{ padding: "12px 16px", borderBottom: "1px solid var(--line)" }}>
            <div style={{ fontSize: 13, fontWeight: 600 }}>Pratiche recenti</div>
          </div>
          {myApps.map((a, i) => (
            <div key={a.id} className="row-hover" style={{
              padding: "14px 16px",
              borderBottom: i < myApps.length - 1 ? "1px solid var(--line)" : "none",
              display: "flex", alignItems: "center", gap: 12, cursor: "pointer",
            }}>
              <div style={{
                width: 36, height: 36, borderRadius: 8,
                background: "var(--surface-2)", border: "1px solid var(--line)",
                display: "flex", alignItems: "center", justifyContent: "center",
                color: "var(--ink-2)",
              }}>
                <Icg d={IIg.truck} size={16} />
              </div>
              <div style={{ flex: 1, minWidth: 0 }}>
                <div style={{ display: "flex", alignItems: "center", gap: 8 }}>
                  <span className="mono" style={{ fontSize: 11, color: "var(--ink-3)" }}>{a.id}</span>
                  <SPg state={a.state} />
                </div>
                <div style={{ fontSize: 13, fontWeight: 500, marginTop: 2 }}>
                  {a.from} → {a.to}
                </div>
                <div style={{ fontSize: 11.5, color: "var(--ink-3)", marginTop: 1 }}>
                  {a.plate} · {a.weight} t · {a.km.toFixed(1)} km · {a.entities} enti
                </div>
              </div>
              <div style={{ textAlign: "right" }}>
                <div className="num" style={{ fontSize: 13, fontWeight: 600 }}>
                  {a.fee ? `€ ${a.fee.toFixed(2)}` : "—"}
                </div>
                <div style={{ fontSize: 11, color: "var(--ink-3)" }}>{a.day}</div>
              </div>
            </div>
          ))}
        </div>

        {/* Garage Virtuale */}
        <div className="card" style={{ display: "flex", flexDirection: "column", overflow: "hidden" }}>
          <div style={{ padding: "12px 16px", borderBottom: "1px solid var(--line)",
                        display: "flex", alignItems: "center" }}>
            <div style={{ fontSize: 13, fontWeight: 600 }}>Garage Virtuale</div>
            <div style={{ flex: 1 }} />
            <button className="btn btn-sm"><Icg d={IIg.plus} size={11} /> Aggiungi</button>
          </div>

          {/* Convoglio attivo — assi */}
          <div style={{ padding: 16, borderBottom: "1px solid var(--line)",
                        background: "var(--surface-2)" }}>
            <div style={{ display: "flex", alignItems: "center", gap: 6, marginBottom: 10 }}>
              <div style={{ fontSize: 11, color: "var(--ink-3)", letterSpacing: 1,
                            textTransform: "uppercase", fontWeight: 500 }}>
                Convoglio composto · 8 assi
              </div>
              <div style={{ flex: 1 }} />
              <span style={{ fontSize: 12, fontWeight: 600 }} className="num">76,4 t totali</span>
            </div>

            {/* axle visualization */}
            <div style={{
              position: "relative", height: 70,
              background: "var(--surface)",
              border: "1px solid var(--line)", borderRadius: 8,
              padding: "10px 14px",
              display: "flex", alignItems: "flex-end", gap: 0,
            }}>
              {/* truck silhouette */}
              <div style={{
                position: "absolute", left: 14, right: 14, top: 8, height: 22,
                background: "var(--ink)", borderRadius: "4px 12px 4px 4px",
                opacity: 0.92,
              }} />
              <div style={{
                position: "absolute", left: 14, top: 4, width: 36, height: 12,
                background: "var(--ink)", borderRadius: "3px 3px 0 0", opacity: 0.92,
              }} />

              {/* axle dots & loads */}
              <div style={{ position: "absolute", left: 14, right: 14, bottom: 8,
                            display: "flex", justifyContent: "space-between", alignItems: "flex-end" }}>
                {axles.map((load, i) => (
                  <div key={i} style={{ display: "flex", flexDirection: "column", alignItems: "center", gap: 3 }}>
                    <span className="num" style={{ fontSize: 9.5, color: "var(--ink-3)" }}>
                      {load.toFixed(1)}t
                    </span>
                    <div style={{
                      width: 14, height: 14, borderRadius: 999,
                      background: "var(--surface)",
                      border: "2px solid var(--ink)",
                    }} />
                  </div>
                ))}
              </div>
            </div>
            <div style={{ fontSize: 11.5, color: "var(--ink-3)", marginTop: 8, lineHeight: 1.5 }}>
              Calcolo usura D.P.R. 495/1992 · coeff. v.2026.04 ·{" "}
              <span className="mono" style={{ color: "var(--ink-2)" }}>4,89 €/km × 84,2 km</span>
            </div>
          </div>

          {/* Mezzi list */}
          <div style={{ flex: 1, overflow: "auto" }}>
            {garage.map((g, i) => (
              <div key={g.plate} style={{
                padding: "12px 16px",
                borderBottom: i < garage.length - 1 ? "1px solid var(--line)" : "none",
                display: "flex", alignItems: "center", gap: 10,
              }}>
                <div style={{
                  width: 30, height: 30, borderRadius: 7,
                  background: g.primary ? "var(--accent-bg)" : "var(--surface-2)",
                  color: g.primary ? "var(--accent-ink)" : "var(--ink-2)",
                  border: "1px solid var(--line)",
                  display: "flex", alignItems: "center", justifyContent: "center",
                }}>
                  <Icg d={g.type === "Rimorchio" ? IIg.layers : IIg.truck} size={14} />
                </div>
                <div style={{ flex: 1, minWidth: 0 }}>
                  <div style={{ fontSize: 12.5, fontWeight: 600 }}>{g.model}</div>
                  <div style={{ fontSize: 11, color: "var(--ink-3)", display: "flex", gap: 8 }}>
                    <span>{g.type}</span>
                    <span className="mono">{g.plate}</span>
                    <span>·</span>
                    <span>{g.axles} assi · {g.mass}</span>
                  </div>
                </div>
                {g.primary && <Cg tone="amber">In uso</Cg>}
              </div>
            ))}
          </div>
        </div>
      </div>
    </div>
  );
}

window.PNTEScreens = { ...(window.PNTEScreens || {}), CitizenScreen };

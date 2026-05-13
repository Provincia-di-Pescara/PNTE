/* global React, PNTE, PNTEMap */
const { Chip: Cf, Icon: Icf, I: IIf } = window.PNTE;

// ===========================================================================
// Forze dell'Ordine — verifica QR + targa, transiti del giorno, cantieri
// (Vista desktop dense)
// ===========================================================================
function LawScreen() {
  const transits = [
    { plate: "FT 728 ZR", company: "Ferraris Trasporti S.p.A.", route: "Pescara → Sulmona",
      window: "12 mag · 06:00–22:00", id: "PNTE-2026-002418", weight: "76,4 t" },
    { plate: "AL 091 KP", company: "Adriatica Logistic S.r.l.", route: "Pescara → L'Aquila",
      window: "14 mag · 04:00–10:00", id: "PNTE-2026-002417", weight: "92,1 t" },
    { plate: "ED 442 BB", company: "Edilstrade Abruzzo", route: "Chieti → Avezzano",
      window: "13 mag · 08:00–14:00", id: "PNTE-2026-002416", weight: "64,8 t" },
    { plate: "TM 619 GH", company: "Trasporti Maiella", route: "Lanciano → Roseto",
      window: "13 mag · 22:00–05:00", id: "PNTE-2026-002413", weight: "84,2 t" },
  ];

  return (
    <div style={{ padding: 20, display: "flex", flexDirection: "column", gap: 16, height: "100%", overflow: "auto" }}>
      <div style={{ display: "flex", alignItems: "flex-end", gap: 16 }}>
        <div>
          <div style={{ fontSize: 10.5, letterSpacing: 1.4, color: "var(--ink-3)", textTransform: "uppercase" }}>
            Forze dell'Ordine · Sola lettura
          </div>
          <h1 style={{ margin: "4px 0 0", fontSize: 22, fontWeight: 600, letterSpacing: "-0.015em" }}>
            Verifica trasporti in transito · Oggi
          </h1>
        </div>
        <div style={{ flex: 1 }} />
        <div className="chip">
          <span className="mono">13 mag 2026 · 14:32</span>
        </div>
      </div>

      <div style={{ display: "grid", gridTemplateColumns: "360px 1fr", gap: 16 }}>
        {/* Verifica panel */}
        <div style={{ display: "flex", flexDirection: "column", gap: 16 }}>
          <div className="card" style={{ padding: 16 }}>
            <div style={{ fontSize: 11, color: "var(--ink-3)", letterSpacing: 1,
                          textTransform: "uppercase", marginBottom: 8 }}>
              Verifica sul campo
            </div>
            <div style={{ display: "flex", gap: 8, marginBottom: 14 }}>
              <button className="btn btn-primary" style={{ flex: 1 }}>
                <Icf d={IIf.qr} size={13} /> Scansiona QR
              </button>
              <button className="btn" style={{ flex: 1 }}>
                <Icf d={IIf.search} size={13} /> Cerca per targa
              </button>
            </div>
            <div style={{ position: "relative" }}>
              <input
                placeholder="Inserisci targa (es. FT 728 ZR)"
                defaultValue="FT 728 ZR"
                style={{
                  width: "100%", height: 38, padding: "0 12px",
                  border: "1px solid var(--line-2)", borderRadius: 8,
                  fontFamily: "DM Mono, monospace", fontSize: 14, letterSpacing: 1,
                  color: "var(--ink)", background: "var(--surface)",
                  outline: "none",
                }} />
            </div>
          </div>

          {/* Match card */}
          <div className="card" style={{ overflow: "hidden",
                                         borderColor: "color-mix(in oklch, var(--success), white 60%)",
                                         boxShadow: "0 0 0 3px var(--success-bg)" }}>
            <div style={{ padding: "12px 16px",
                          background: "var(--success-bg)",
                          borderBottom: "1px solid color-mix(in oklch, var(--success), white 60%)",
                          display: "flex", alignItems: "center", gap: 8 }}>
              <Icf d={IIf.check} size={14} stroke={2.4} />
              <strong style={{ fontSize: 13, color: "var(--success)" }}>Autorizzazione valida</strong>
              <div style={{ flex: 1 }} />
              <span style={{ fontSize: 11, color: "var(--success)" }} className="mono">verificata 14:32</span>
            </div>
            <div style={{ padding: 16, display: "flex", flexDirection: "column", gap: 8, fontSize: 13 }}>
              <Row k="Targa" v="FT 728 ZR" mono />
              <Row k="Pratica" v="PNTE-2026-002418" mono />
              <Row k="Richiedente" v="Ferraris Trasporti S.p.A." />
              <Row k="P.IVA" v="01428360689" mono />
              <Row k="Convoglio" v="Motrice + 3 assi rim." />
              <Row k="Massa totale" v="76,4 t" mono />
              <Row k="Tratta" v="Pescara → Sulmona via SS17" />
              <Row k="Validità" v="12 mag 2026 · 06:00–22:00" />
              <Row k="Ente emittente" v="Provincia di Pescara" />
              <Row k="Firma PAdES" v="Aruba CA · valida" tone="success" />
            </div>
            <div style={{ padding: 12, borderTop: "1px solid var(--line)",
                          display: "flex", gap: 8 }}>
              <button className="btn btn-sm" style={{ flex: 1 }}>
                <Icf d={IIf.doc} size={11} /> Apri PDF firmato
              </button>
              <button className="btn btn-sm" style={{ flex: 1 }}>Mappa percorso</button>
            </div>
          </div>
        </div>

        {/* Right: map + transits */}
        <div style={{ display: "flex", flexDirection: "column", gap: 16 }}>
          <div className="card" style={{ padding: 12 }}>
            <div style={{ display: "flex", alignItems: "center", gap: 8, marginBottom: 10 }}>
              <div style={{ fontSize: 13, fontWeight: 600 }}>Trasporti attivi · Regione Abruzzo</div>
              <Cf tone="amber">{transits.length}</Cf>
              <div style={{ flex: 1 }} />
              <button className="btn btn-sm">Cantieri</button>
              <button className="btn btn-sm">Filtra tratta</button>
            </div>
            <PNTEMap height={260} showLegend={false} />
          </div>

          <div className="card" style={{ overflow: "hidden" }}>
            <div style={{ padding: "10px 14px", borderBottom: "1px solid var(--line)",
                          display: "flex", alignItems: "center" }}>
              <div style={{ fontSize: 13, fontWeight: 600 }}>Lista transiti</div>
              <div style={{ flex: 1 }} />
              <span style={{ fontSize: 11, color: "var(--ink-3)" }}>Aggiornato in tempo reale</span>
            </div>
            <table style={{ width: "100%", borderCollapse: "collapse", fontSize: 12.5 }}>
              <thead>
                <tr style={{ background: "var(--surface-2)",
                             color: "var(--ink-3)", fontSize: 10.5,
                             textTransform: "uppercase", letterSpacing: 1 }}>
                  {["Targa", "Richiedente", "Tratta", "Finestra", "Pratica"].map(h => (
                    <th key={h} style={{ textAlign: "left", padding: "8px 12px",
                                         fontWeight: 500, borderBottom: "1px solid var(--line)" }}>{h}</th>
                  ))}
                </tr>
              </thead>
              <tbody>
                {transits.map((t, i) => (
                  <tr key={t.id} className="row-hover" style={{ cursor: "pointer" }}>
                    <td style={{ padding: "10px 12px", borderBottom: i < transits.length - 1 ? "1px solid var(--line)" : "none" }}
                        className="mono">
                      <strong>{t.plate}</strong>
                    </td>
                    <td style={{ padding: "10px 12px", borderBottom: i < transits.length - 1 ? "1px solid var(--line)" : "none" }}>
                      {t.company}<div style={{ fontSize: 11, color: "var(--ink-3)" }}>{t.weight}</div>
                    </td>
                    <td style={{ padding: "10px 12px", borderBottom: i < transits.length - 1 ? "1px solid var(--line)" : "none" }}>
                      {t.route}
                    </td>
                    <td style={{ padding: "10px 12px", borderBottom: i < transits.length - 1 ? "1px solid var(--line)" : "none" }}>
                      <span className="mono" style={{ fontSize: 11.5 }}>{t.window}</span>
                    </td>
                    <td style={{ padding: "10px 12px", borderBottom: i < transits.length - 1 ? "1px solid var(--line)" : "none" }}
                        className="mono">
                      {t.id}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  );
}

function Row({ k, v, mono, tone }) {
  return (
    <div style={{ display: "flex", alignItems: "baseline", gap: 8 }}>
      <span style={{ width: 110, fontSize: 11.5, color: "var(--ink-3)",
                     textTransform: "uppercase", letterSpacing: 1 }}>{k}</span>
      <span className={mono ? "mono" : ""}
            style={{ flex: 1, fontWeight: mono ? 600 : 500,
                     color: tone === "success" ? "var(--success)" : "var(--ink)" }}>
        {v}
      </span>
    </div>
  );
}

window.PNTEScreens = { ...(window.PNTEScreens || {}), LawScreen };

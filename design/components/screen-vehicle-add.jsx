/* global React, PNTE */
const { Chip: Cv, Icon: Icv, I: IIv } = window.PNTE;

// ===========================================================================
// Cittadino / Azienda — Garage Virtuale → Aggiungi convoglio
// Step 3 di 5: Geometria & assi
// ===========================================================================
function VehicleAddScreen() {
  // ---- step ribbon -------------------------------------------------------
  const steps = [
    { n: 1, k: "type",   label: "Tipo mezzo",        sub: "Categoria · uso" },
    { n: 2, k: "id",     label: "Identità",          sub: "Targa · libretto" },
    { n: 3, k: "geom",   label: "Geometria & assi",  sub: "Dimensioni · masse" },
    { n: 4, k: "docs",   label: "Documenti",         sub: "Carta circ. · assicur." },
    { n: 5, k: "review", label: "Riepilogo",         sub: "Salva nel Garage" },
  ];
  const activeIdx = 2; // step 3

  // ---- vehicle composition (motrice + semirimorchio = 8 assi) -----------
  // Each axle: position (m from front), load (t), spread (m to next), twin tires, steered
  const axles = [
    { pos: 1.40, load: 7.50, spread: 3.60, twin: false, steer: true,  tag: "1°" },
    { pos: 5.00, load: 7.50, spread: 1.35, twin: true,  steer: false, tag: "2°" },
    { pos: 6.35, load: 11.50, spread: 1.35, twin: true, steer: false, tag: "3°" },
    { pos: 7.70, load: 11.50, spread: 5.20, twin: true, steer: false, tag: "4°" },
    { pos: 12.90, load: 12.00, spread: 1.41, twin: true, steer: false, tag: "5°" },
    { pos: 14.31, load: 12.00, spread: 1.41, twin: true, steer: false, tag: "6°" },
    { pos: 15.72, load: 7.20, spread: 1.41, twin: false, steer: true, tag: "7°" },
    { pos: 17.13, load: 7.20, spread: null, twin: false, steer: true, tag: "8°" },
  ];

  const totalLength = 18.55; // m
  const totalWidth = 3.00;
  const totalHeight = 4.50;
  const totalMass = axles.reduce((a, b) => a + b.load, 0);
  const dryMass = 35.4;
  const payload = totalMass - dryMass;

  // ---- compliance checks against D.M. 19/1/2017 + C.d.S. ----------------
  // Not real legal, but plausible
  const checks = [
    { ok: true,  label: "Massa per asse singolo ≤ 12,0 t",          detail: "max rilevato 12,0 t (asse 5°-6°)" },
    { ok: true,  label: "Massa per asse tandem ≤ 24,0 t",            detail: "tandem 5°-6° = 24,0 t · spread 1,41 m" },
    { ok: false, label: "Massa per asse tridem ≤ 24,0 t",           detail: "tridem 2°-3°-4° = 30,5 t · richiede deroga" },
    { ok: true,  label: "Lunghezza convoglio ≤ 18,75 m",             detail: "18,55 m · margine 20 cm" },
    { ok: true,  label: "Larghezza ≤ 3,00 m (con scorta)",           detail: "3,00 m esatti · scorta tecnica obbligatoria" },
    { ok: true,  label: "Altezza ≤ 4,50 m",                          detail: "4,50 m · check ponti AINOP attivo" },
    { ok: false, label: "Sporgenza posteriore ≤ 3,00 m",             detail: "3,40 m · richiede pannello segnal." },
  ];
  const violations = checks.filter(c => !c.ok).length;

  // ---- silhouette geometry ----------------------------------------------
  // Render the convoy in scale: SVG viewBox 0..L (m), 0..2.4 (m height)
  const L = totalLength;
  const H = 2.4;
  const padL = 0.4, padR = 0.6;

  return (
    <div style={{ display: "flex", flexDirection: "column", height: "100%", overflow: "hidden" }}>
      {/* ---- top bar: breadcrumb + step ribbon ---------------------------- */}
      <div style={{ padding: "16px 24px 0", display: "flex", flexDirection: "column", gap: 14,
                    borderBottom: "1px solid var(--line)", paddingBottom: 14 }}>
        <div style={{ display: "flex", alignItems: "center", gap: 6, fontSize: 12, color: "var(--ink-3)" }}>
          <span>Garage Virtuale</span>
          <Icv d={IIv.chevron} size={11} />
          <span style={{ color: "var(--ink-2)" }}>Aggiungi convoglio</span>
          <div style={{ flex: 1 }} />
          <span className="mono" style={{ fontSize: 11 }}>BOZZA · GTV-2026-00187</span>
          <span style={{ color: "var(--ink-3)" }}>·</span>
          <span style={{ fontSize: 12 }}>Salvataggio automatico · 14:32</span>
        </div>

        <div style={{ display: "flex", alignItems: "flex-start", gap: 16 }}>
          <div>
            <h1 style={{ margin: 0, fontSize: 22, fontWeight: 600, letterSpacing: "-0.015em" }}>
              Nuovo convoglio
            </h1>
            <div style={{ fontSize: 12.5, color: "var(--ink-3)", marginTop: 2 }}>
              Configura motrice + rimorchio · le geometrie si verificano contro D.M. 19 gen. 2017
              e art. 61-62 C.d.S.
            </div>
          </div>
          <div style={{ flex: 1 }} />
          <div style={{ display: "flex", gap: 8 }}>
            <button className="btn btn-ghost"><Icv d={IIv.x} size={11} /> Annulla</button>
            <button className="btn"><Icv d={IIv.download} size={11} /> Salva bozza</button>
          </div>
        </div>

        {/* step ribbon */}
        <div style={{ display: "flex", gap: 4, marginTop: 4 }}>
          {steps.map((s, i) => {
            const done = i < activeIdx;
            const active = i === activeIdx;
            return (
              <div key={s.k} style={{ flex: 1, display: "flex", alignItems: "center", gap: 0 }}>
                <div style={{
                  display: "flex", alignItems: "center", gap: 10,
                  padding: "10px 14px",
                  background: active ? "var(--surface)" : "transparent",
                  border: active ? "1px solid var(--line)" : "1px solid transparent",
                  borderBottom: active ? "1px solid var(--surface)" : "1px solid transparent",
                  borderRadius: "8px 8px 0 0",
                  marginBottom: -1,
                  flex: 1,
                  position: "relative",
                }}>
                  <div style={{
                    width: 22, height: 22, borderRadius: 999,
                    background: done ? "var(--ink)" : active ? "var(--accent-bg)" : "var(--surface-2)",
                    color: done ? "var(--bg)" : active ? "var(--accent-ink)" : "var(--ink-3)",
                    border: active ? "1px solid var(--accent)" : "1px solid var(--line)",
                    display: "flex", alignItems: "center", justifyContent: "center",
                    fontSize: 11, fontWeight: 600,
                  }}>
                    {done ? <Icv d={IIv.check} size={12} stroke={2.2} /> : s.n}
                  </div>
                  <div style={{ minWidth: 0 }}>
                    <div style={{ fontSize: 12.5, fontWeight: active ? 600 : 500,
                                  color: active ? "var(--ink)" : done ? "var(--ink-2)" : "var(--ink-3)",
                                  whiteSpace: "nowrap" }}>{s.label}</div>
                    <div style={{ fontSize: 10.5, color: "var(--ink-3)", whiteSpace: "nowrap" }}>{s.sub}</div>
                  </div>
                </div>
                {i < steps.length - 1 && (
                  <div style={{ width: 8, height: 1, background: "var(--line)", flexShrink: 0 }} />
                )}
              </div>
            );
          })}
        </div>
      </div>

      {/* ---- body: 2-col --------------------------------------------------- */}
      <div style={{ flex: 1, minHeight: 0, display: "grid",
                    gridTemplateColumns: "minmax(0, 1.05fr) minmax(0, 1fr)",
                    overflow: "hidden" }}>

        {/* ===== LEFT: form ============================================== */}
        <div style={{ padding: 20, overflow: "auto", display: "flex", flexDirection: "column", gap: 16 }}>

          {/* dimensions */}
          <div>
            <SectionTitle n="3.1" title="Dimensioni d'ingombro"
                          hint="incluso carico · misurate a vuoto + sporgenze massime ammesse" />
            <div style={{ display: "grid", gridTemplateColumns: "repeat(3, 1fr)", gap: 12 }}>
              <FieldNum label="Lunghezza" suffix="m" value="18,55" warn={false} />
              <FieldNum label="Larghezza" suffix="m" value="3,00" warn={true}
                        hint="≥ 2,55 m → scorta tecnica" />
              <FieldNum label="Altezza" suffix="m" value="4,50" warn={false} />
            </div>
            <div style={{ display: "grid", gridTemplateColumns: "repeat(3, 1fr)", gap: 12, marginTop: 10 }}>
              <FieldNum label="Sporg. anter." suffix="m" value="0,80" warn={false} />
              <FieldNum label="Sporg. poster." suffix="m" value="3,40" warn={true}
                        hint="> 3,00 m → pannello sagoma" />
              <FieldNum label="Passo motrice" suffix="m" value="3,60" warn={false} />
            </div>
          </div>

          {/* axle layout */}
          <div>
            <SectionTitle n="3.2" title="Configurazione assi"
                          hint="trascina per riposizionare · clicca per editare" right={
                <div style={{ display: "flex", gap: 6 }}>
                  <button className="btn btn-sm"><Icv d={IIv.refresh} size={11} /> Da catalogo</button>
                  <button className="btn btn-sm"><Icv d={IIv.plus} size={11} /> Asse</button>
                </div>
              }/>

            {/* table */}
            <div className="card" style={{ overflow: "hidden" }}>
              <div style={{
                display: "grid",
                gridTemplateColumns: "44px 1fr 1fr 1fr 88px 88px 30px",
                padding: "8px 12px", fontSize: 10.5, color: "var(--ink-3)",
                letterSpacing: 0.8, textTransform: "uppercase", fontWeight: 500,
                background: "var(--surface-2)", borderBottom: "1px solid var(--line)",
              }}>
                <div>#</div>
                <div>Posizione</div>
                <div>Carico</div>
                <div>Interasse</div>
                <div>Gemellati</div>
                <div>Sterzanti</div>
                <div></div>
              </div>
              {axles.map((a, i) => {
                const overload = a.load > 11.5;
                return (
                  <div key={i} style={{
                    display: "grid",
                    gridTemplateColumns: "44px 1fr 1fr 1fr 88px 88px 30px",
                    padding: "10px 12px",
                    borderBottom: i < axles.length - 1 ? "1px solid var(--line)" : "none",
                    alignItems: "center", fontSize: 12.5,
                    background: overload ? "color-mix(in oklch, var(--danger-bg) 35%, transparent)" : "transparent",
                  }}>
                    <div className="mono" style={{ color: "var(--ink-3)" }}>{a.tag}</div>
                    <div className="num">{a.pos.toFixed(2)} m</div>
                    <div style={{ display: "flex", alignItems: "center", gap: 6 }}>
                      <span className="num" style={{ fontWeight: overload ? 600 : 500,
                                                     color: overload ? "var(--danger)" : "inherit" }}>
                        {a.load.toFixed(2)} t
                      </span>
                      {overload && <Cv tone="danger">limite</Cv>}
                    </div>
                    <div className="num" style={{ color: "var(--ink-3)" }}>
                      {a.spread !== null ? `${a.spread.toFixed(2)} m` : "—"}
                    </div>
                    <div>
                      <Toggle on={a.twin} />
                    </div>
                    <div>
                      <Toggle on={a.steer} />
                    </div>
                    <button className="btn btn-ghost btn-sm" style={{ width: 22, padding: 0 }}>
                      <Icv d={IIv.more} size={12} />
                    </button>
                  </div>
                );
              })}
            </div>

            {/* sum bar */}
            <div style={{
              marginTop: 8, padding: "10px 14px",
              display: "flex", alignItems: "center", gap: 16,
              border: "1px dashed var(--line-2)", borderRadius: 8,
              fontSize: 12.5,
            }}>
              <span style={{ color: "var(--ink-3)" }}>Σ assi</span>
              <strong className="num">{totalMass.toFixed(1)} t</strong>
              <span style={{ color: "var(--ink-3)" }}>·</span>
              <span style={{ color: "var(--ink-3)" }}>tara</span>
              <strong className="num">{dryMass.toFixed(1)} t</strong>
              <span style={{ color: "var(--ink-3)" }}>·</span>
              <span style={{ color: "var(--ink-3)" }}>portata residua</span>
              <strong className="num">{payload.toFixed(1)} t</strong>
              <div style={{ flex: 1 }} />
              <Cv tone={violations === 0 ? "success" : "amber"}>
                {violations === 0 ? "Configurazione conforme" : `${violations} deroghe richieste`}
              </Cv>
            </div>
          </div>

          {/* group categorization */}
          <div>
            <SectionTitle n="3.3" title="Classificazione AINOP"
                          hint="determina l'eligibilità sui ponti del percorso" />
            <div style={{ display: "grid", gridTemplateColumns: "repeat(3, 1fr)", gap: 8 }}>
              {[
                { v: "I", title: "Cat. I", sub: "≤ 26 t · 2 assi", on: false },
                { v: "II", title: "Cat. II", sub: "≤ 44 t · 3-4 assi", on: false },
                { v: "III", title: "Cat. III", sub: "44 ÷ 108 t · 5-12 assi", on: true },
              ].map(c => (
                <div key={c.v} style={{
                  padding: "10px 12px", borderRadius: 8,
                  border: `1px solid ${c.on ? "var(--accent)" : "var(--line)"}`,
                  background: c.on ? "var(--accent-bg)" : "var(--surface)",
                  color: c.on ? "var(--accent-ink)" : "var(--ink)",
                }}>
                  <div style={{ fontSize: 12, fontWeight: 600 }}>{c.title}</div>
                  <div style={{ fontSize: 11, color: c.on ? "var(--accent-ink)" : "var(--ink-3)" }}>
                    {c.sub}
                  </div>
                </div>
              ))}
            </div>
            <div style={{ display: "flex", alignItems: "center", gap: 10, marginTop: 12,
                          padding: "10px 12px", border: "1px solid var(--line)", borderRadius: 8,
                          background: "var(--surface-2)" }}>
              <Icv d={IIv.bridge} size={14} />
              <div style={{ fontSize: 12.5, flex: 1 }}>
                Su <strong className="num">2.418 ponti AINOP</strong> della rete Abruzzo,{" "}
                <strong className="num">2.281</strong> sono compatibili con questa configurazione,{" "}
                <strong className="num">94</strong> richiedono pre-verifica strutturale,{" "}
                <strong className="num">43</strong> sono interdetti.
              </div>
              <button className="btn btn-sm">Vedi mappa</button>
            </div>
          </div>
        </div>

        {/* ===== RIGHT: live preview ===================================== */}
        <div style={{
          background: "var(--surface-2)",
          borderLeft: "1px solid var(--line)",
          display: "flex", flexDirection: "column",
          overflow: "auto",
        }}>
          {/* silhouette */}
          <div style={{ padding: 20, borderBottom: "1px solid var(--line)" }}>
            <div style={{ display: "flex", alignItems: "center", marginBottom: 10 }}>
              <div style={{ fontSize: 11, color: "var(--ink-3)", letterSpacing: 1.2,
                            textTransform: "uppercase", fontWeight: 500 }}>
                Anteprima silhouette · scala 1:80
              </div>
              <div style={{ flex: 1 }} />
              <div style={{ display: "flex", gap: 4 }}>
                <button className="btn btn-sm btn-ghost" title="Vista laterale">Lato</button>
                <button className="btn btn-sm btn-ghost" title="Vista dall'alto">Alto</button>
                <button className="btn btn-sm btn-ghost" title="Vista frontale">Front</button>
              </div>
            </div>

            {/* SVG silhouette */}
            <div style={{
              background: "var(--surface)", border: "1px solid var(--line)",
              borderRadius: 10, padding: "20px 16px 12px",
            }}>
              <ConvoySilhouette axles={axles} L={L} H={H} padL={padL} padR={padR}
                                length={totalLength} />
            </div>

            {/* dimension callouts */}
            <div style={{ display: "grid", gridTemplateColumns: "repeat(4, 1fr)", gap: 0,
                          marginTop: 14, border: "1px solid var(--line)", borderRadius: 8,
                          background: "var(--surface)", overflow: "hidden" }}>
              {[
                ["L · totale", `${totalLength.toFixed(2)} m`, false],
                ["W · totale", `${totalWidth.toFixed(2)} m`, true],
                ["H · totale", `${totalHeight.toFixed(2)} m`, false],
                ["MTM",        `${totalMass.toFixed(1)} t`, false],
              ].map(([l, v, warn], i) => (
                <div key={l} style={{
                  padding: "10px 12px",
                  borderRight: i < 3 ? "1px solid var(--line)" : "none",
                  background: warn ? "color-mix(in oklch, var(--accent-bg) 50%, transparent)" : "transparent",
                }}>
                  <div style={{ fontSize: 10, color: "var(--ink-3)", letterSpacing: 1,
                                textTransform: "uppercase", fontWeight: 500 }}>{l}</div>
                  <div className="num" style={{ fontSize: 16, fontWeight: 600 }}>{v}</div>
                </div>
              ))}
            </div>
          </div>

          {/* compliance */}
          <div style={{ padding: 20, flex: 1 }}>
            <div style={{ display: "flex", alignItems: "center", marginBottom: 10 }}>
              <div style={{ fontSize: 11, color: "var(--ink-3)", letterSpacing: 1.2,
                            textTransform: "uppercase", fontWeight: 500 }}>
                Verifiche live · D.M. 19/01/2017 + art. 61-62 C.d.S.
              </div>
              <div style={{ flex: 1 }} />
              <Cv tone={violations === 0 ? "success" : "amber"}>
                {checks.length - violations}/{checks.length}
              </Cv>
            </div>

            <div className="card" style={{ overflow: "hidden" }}>
              {checks.map((c, i) => (
                <div key={i} style={{
                  display: "flex", alignItems: "flex-start", gap: 10,
                  padding: "10px 12px",
                  borderBottom: i < checks.length - 1 ? "1px solid var(--line)" : "none",
                }}>
                  <div style={{
                    width: 20, height: 20, borderRadius: 999,
                    flexShrink: 0, marginTop: 1,
                    background: c.ok ? "var(--success-bg)" : "var(--accent-bg)",
                    color: c.ok ? "var(--success)" : "var(--accent-ink)",
                    border: `1px solid ${c.ok ? "color-mix(in oklch, var(--success), white 70%)"
                                              : "color-mix(in oklch, var(--accent), white 70%)"}`,
                    display: "flex", alignItems: "center", justifyContent: "center",
                  }}>
                    {c.ok
                      ? <Icv d={IIv.check} size={11} stroke={2.4} />
                      : <Icv d={IIv.alert} size={11} stroke={2} />}
                  </div>
                  <div style={{ flex: 1 }}>
                    <div style={{ fontSize: 12.5, fontWeight: 500 }}>{c.label}</div>
                    <div style={{ fontSize: 11.5, color: "var(--ink-3)", marginTop: 1 }}>
                      {c.detail}
                    </div>
                  </div>
                  {!c.ok && (
                    <button className="btn btn-sm btn-ghost" style={{ fontSize: 11.5 }}>
                      Allega deroga
                    </button>
                  )}
                </div>
              ))}
            </div>

            <div style={{ marginTop: 12, padding: "10px 12px",
                          border: "1px solid var(--line)", borderRadius: 8,
                          background: "var(--surface)", display: "flex",
                          alignItems: "center", gap: 10 }}>
              <Icv d={IIv.doc} size={14} />
              <div style={{ fontSize: 12, flex: 1 }}>
                Convoglio non ancora omologato in archivio AINOP. Allega carta di
                circolazione + scheda tecnica al passo successivo per ottenere il
                <strong style={{ color: "var(--ink)" }}> codice AINOP-V</strong>.
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* ---- footer wizard ---- */}
      <div style={{
        padding: "12px 24px",
        borderTop: "1px solid var(--line)",
        background: "var(--surface)",
        display: "flex", alignItems: "center", gap: 12,
      }}>
        <div style={{ fontSize: 12, color: "var(--ink-3)" }}>
          Step <strong style={{ color: "var(--ink)" }}>3 di 5</strong> · Geometria & assi
        </div>
        <div style={{ flex: 1 }} />
        <button className="btn"><Icv d={IIv.chevron} size={11} /> Indietro</button>
        <button className="btn btn-ghost">Salva ed esci</button>
        <button className="btn btn-primary">
          Avanti · Documenti <Icv d={IIv.arrow} size={11} />
        </button>
      </div>
    </div>
  );
}

// ===========================================================================
// Subcomponents
// ===========================================================================
function SectionTitle({ n, title, hint, right }) {
  return (
    <div style={{ display: "flex", alignItems: "flex-end", gap: 10, marginBottom: 10 }}>
      <div>
        <div style={{ display: "flex", alignItems: "center", gap: 8 }}>
          <span className="mono" style={{ fontSize: 10.5, color: "var(--ink-3)", letterSpacing: 0.4 }}>
            {n}
          </span>
          <h3 style={{ margin: 0, fontSize: 13.5, fontWeight: 600 }}>{title}</h3>
        </div>
        {hint && <div style={{ fontSize: 11.5, color: "var(--ink-3)", marginTop: 2 }}>{hint}</div>}
      </div>
      <div style={{ flex: 1 }} />
      {right}
    </div>
  );
}

function FieldNum({ label, suffix, value, warn, hint }) {
  return (
    <label style={{ display: "flex", flexDirection: "column", gap: 4 }}>
      <span style={{ fontSize: 11, color: "var(--ink-3)", letterSpacing: 0.4,
                     textTransform: "uppercase", fontWeight: 500 }}>{label}</span>
      <div style={{
        display: "flex", alignItems: "center",
        border: `1px solid ${warn ? "color-mix(in oklch, var(--accent), white 50%)" : "var(--line-2)"}`,
        borderRadius: 7, height: 34,
        background: warn ? "var(--accent-bg)" : "var(--surface)",
      }}>
        <input className="num" defaultValue={value} style={{
          flex: 1, minWidth: 0, border: "none", background: "transparent",
          padding: "0 10px", fontSize: 13.5, fontWeight: 500, color: "var(--ink)",
          fontFamily: "DM Mono, ui-monospace, monospace", outline: "none",
        }} />
        <span style={{ padding: "0 10px", fontSize: 11.5, color: "var(--ink-3)",
                       borderLeft: "1px solid var(--line)" }}>{suffix}</span>
      </div>
      {hint && (
        <span style={{ fontSize: 10.5, color: warn ? "var(--accent-ink)" : "var(--ink-3)" }}>
          {hint}
        </span>
      )}
    </label>
  );
}

function Toggle({ on }) {
  return (
    <div style={{
      width: 30, height: 17, borderRadius: 999,
      background: on ? "var(--ink)" : "var(--surface-3)",
      border: "1px solid var(--line-2)",
      position: "relative", cursor: "pointer",
    }}>
      <div style={{
        position: "absolute", top: 1, left: on ? 14 : 1,
        width: 13, height: 13, borderRadius: 999,
        background: "var(--surface)",
        boxShadow: "0 1px 2px rgba(0,0,0,0.15)",
        transition: "left .12s ease",
      }} />
    </div>
  );
}

// ---- silhouette: SVG render of convoy ---------------------------------------
function ConvoySilhouette({ axles, L, H, padL, padR, length }) {
  // viewBox: x in metres, with padding
  const totalW = L + padL + padR;
  const totalH = H + 1.4; // extra room for callouts top + ground bottom

  // body geometry (approx): cab 0..2.4, motrice frame 2.4..6.0,
  // hinge 8.0, semi 8.0..L
  const cabX = 0.4, cabW = 2.0, cabH = 1.6, cabY = 0.5;
  const truckBodyX = 2.4, truckBodyW = 5.6, truckBodyH = 1.4, truckBodyY = 0.7;
  const semiX = 8.2, semiW = L - 8.4, semiH = 1.7, semiY = 0.5;
  const wheelR = 0.32;
  const groundY = H + 0.3;

  // helper to convert meters to viewBox units (1:1)
  const stroke = "var(--ink)";
  const fillBody = "var(--ink)";

  return (
    <svg viewBox={`0 0 ${totalW} ${totalH}`}
         style={{ width: "100%", height: "auto", display: "block",
                  fontFamily: "DM Mono, ui-monospace, monospace" }}
         preserveAspectRatio="xMidYMid meet">
      {/* ground */}
      <line x1={0} x2={totalW} y1={groundY} y2={groundY}
            stroke="var(--line-2)" strokeWidth={0.025} strokeDasharray="0.1 0.1" />

      <g transform={`translate(${padL}, 0)`}>

        {/* cab */}
        <rect x={cabX} y={cabY + 0.5} width={cabW} height={cabH - 0.4}
              fill={fillBody} rx={0.18} />
        {/* cab roof bevel */}
        <path d={`M ${cabX + 0.2} ${cabY + 0.5}
                  L ${cabX + cabW - 0.05} ${cabY + 0.5}
                  L ${cabX + cabW - 0.05} ${cabY + 0.95}
                  L ${cabX + 0.2} ${cabY + 0.95} Z`}
              fill={fillBody} />
        {/* windshield */}
        <path d={`M ${cabX + 1.2} ${cabY + 0.55}
                  L ${cabX + cabW - 0.15} ${cabY + 0.55}
                  L ${cabX + cabW - 0.15} ${cabY + 0.92}
                  L ${cabX + 1.4} ${cabY + 0.92} Z`}
              fill="var(--info-bg)" stroke="var(--info)" strokeWidth={0.025} />

        {/* motrice frame */}
        <rect x={truckBodyX} y={truckBodyY + 0.5} width={truckBodyW} height={truckBodyH - 0.3}
              fill={fillBody} rx={0.06} />
        {/* fifth wheel platform */}
        <rect x={truckBodyX + truckBodyW - 1.4} y={truckBodyY + 0.4}
              width={1.4} height={0.18} fill="var(--ink-2)" />

        {/* semi (low loader) */}
        {/* gooseneck */}
        <path d={`M ${semiX} ${semiY + 0.55}
                  L ${semiX + 1.2} ${semiY + 0.55}
                  L ${semiX + 1.6} ${semiY + 1.0}
                  L ${semiX + semiW - 2.2} ${semiY + 1.0}
                  L ${semiX + semiW - 1.8} ${semiY + 0.55}
                  L ${semiX + semiW - 0.4} ${semiY + 0.55}
                  L ${semiX + semiW - 0.4} ${semiY + 1.6}
                  L ${semiX} ${semiY + 1.6} Z`}
              fill={fillBody} />
        {/* deck top accent */}
        <line x1={semiX + 1.6} x2={semiX + semiW - 2.2}
              y1={semiY + 1.0} y2={semiY + 1.0}
              stroke="var(--accent)" strokeWidth={0.04} />
        {/* load placeholder (transformer) */}
        <rect x={semiX + 2.0} y={semiY + 0.0} width={semiW - 4.6} height={1.0}
              fill="var(--surface-2)" stroke="var(--ink)" strokeWidth={0.04}
              strokeDasharray="0.15 0.1" />
        <text x={semiX + 2.0 + (semiW - 4.6) / 2} y={semiY + 0.55}
              fontSize="0.36" textAnchor="middle" fill="var(--ink-3)">
          Carico · 41,0 t
        </text>

        {/* axles + wheels */}
        {axles.map((a, i) => {
          const overload = a.load > 11.5;
          const cy = groundY - wheelR;
          return (
            <g key={i}>
              {/* axle line */}
              <line x1={a.pos} x2={a.pos} y1={groundY - 0.15} y2={groundY - wheelR * 2}
                    stroke="var(--ink-2)" strokeWidth={0.04} />
              {/* wheel */}
              <circle cx={a.pos} cy={cy} r={wheelR}
                      fill={overload ? "var(--danger-bg)" : "var(--surface-2)"}
                      stroke={overload ? "var(--danger)" : "var(--ink)"}
                      strokeWidth={overload ? 0.06 : 0.05} />
              <circle cx={a.pos} cy={cy} r={wheelR * 0.45}
                      fill="var(--ink)" />
              {/* steered indicator */}
              {a.steer && (
                <circle cx={a.pos} cy={cy} r={wheelR + 0.12}
                        fill="none" stroke="var(--accent)" strokeWidth={0.04}
                        strokeDasharray="0.08 0.08" />
              )}
              {/* twin tire indicator */}
              {a.twin && (
                <circle cx={a.pos} cy={cy} r={wheelR + 0.06}
                        fill="none" stroke="var(--ink)" strokeWidth={0.025} />
              )}
              {/* load label below */}
              <text x={a.pos} y={groundY + 0.42}
                    fontSize="0.24" textAnchor="middle"
                    fill={overload ? "var(--danger)" : "var(--ink-3)"}
                    fontWeight={overload ? 600 : 400}>
                {a.load.toFixed(1)}t
              </text>
              {/* axle tag above */}
              <text x={a.pos} y={cy - wheelR - 0.15}
                    fontSize="0.20" textAnchor="middle" fill="var(--ink-3)">
                {a.tag}
              </text>
            </g>
          );
        })}

        {/* total length dimension */}
        <g transform={`translate(0, ${groundY + 0.85})`}>
          <line x1={0.1} x2={L - 0.1} y1={0} y2={0}
                stroke="var(--ink-2)" strokeWidth={0.03} />
          <line x1={0.1} x2={0.1} y1={-0.12} y2={0.12}
                stroke="var(--ink-2)" strokeWidth={0.03} />
          <line x1={L - 0.1} x2={L - 0.1} y1={-0.12} y2={0.12}
                stroke="var(--ink-2)" strokeWidth={0.03} />
          <rect x={L / 2 - 0.7} y={-0.18} width={1.4} height={0.36}
                fill="var(--surface)" />
          <text x={L / 2} y={0.05} fontSize="0.26" textAnchor="middle"
                fill="var(--ink)" fontWeight={500}>
            {length.toFixed(2)} m
          </text>
        </g>

        {/* height dimension callout (right side) */}
        <g transform={`translate(${L + 0.05}, 0)`}>
          <line x1={0} x2={0} y1={0.2} y2={groundY - 0.05}
                stroke="var(--ink-2)" strokeWidth={0.03} />
          <line x1={-0.08} x2={0.08} y1={0.2} y2={0.2}
                stroke="var(--ink-2)" strokeWidth={0.03} />
          <line x1={-0.08} x2={0.08} y1={groundY - 0.05} y2={groundY - 0.05}
                stroke="var(--ink-2)" strokeWidth={0.03} />
          <text x={0.18} y={(groundY) / 2 + 0.08} fontSize="0.24"
                fill="var(--ink-2)" fontWeight={500}>
            4,50
          </text>
        </g>
      </g>
    </svg>
  );
}

window.PNTEScreens = { ...(window.PNTEScreens || {}), VehicleAddScreen };

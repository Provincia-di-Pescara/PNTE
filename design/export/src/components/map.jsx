/* global React */
const { useState: useStateMap, useMemo: useMemoMap } = React;

// Stylized Abruzzo map. Coordinates are designed; not real GIS.
// View box: 800x520. Coast at right; mountains on left.

// Comuni polygons (rough cells, used for shading + intersection visuals)
const COMUNI = [
  { id: "pescara",  name: "Pescara",  d: "M610 215 L660 210 L675 245 L640 270 L605 252 Z" },
  { id: "spoltore", name: "Spoltore", d: "M560 200 L610 215 L605 252 L555 248 L548 220 Z" },
  { id: "chieti",   name: "Chieti",   d: "M560 280 L612 268 L640 305 L595 322 L555 305 Z" },
  { id: "popoli",   name: "Popoli T.",d: "M420 230 L478 222 L490 268 L432 278 L412 252 Z" },
  { id: "sulmona",  name: "Sulmona",  d: "M328 290 L388 282 L400 332 L342 342 L320 318 Z" },
  { id: "aquila",   name: "L'Aquila", d: "M180 220 L242 212 L260 268 L200 282 L168 248 Z" },
  { id: "avezzano", name: "Avezzano", d: "M150 360 L210 352 L222 402 L162 412 L138 388 Z" },
  { id: "teramo",   name: "Teramo",   d: "M395 110 L460 100 L478 150 L412 162 L385 142 Z" },
  { id: "penne",    name: "Penne",    d: "M520 140 L578 130 L590 175 L530 188 L508 168 Z" },
  { id: "lanciano", name: "Lanciano", d: "M615 340 L675 332 L688 378 L625 388 L605 365 Z" },
  { id: "vasto",    name: "Vasto",    d: "M680 400 L735 395 L745 445 L688 455 L670 425 Z" },
];

// Provincial boundaries (coarse)
const PROVINCES = [
  { id: "te", name: "Teramo",  d: "M120 60 L520 60 L520 175 L320 195 L120 175 Z" },
  { id: "pe", name: "Pescara", d: "M320 195 L520 175 L700 200 L700 290 L320 290 Z" },
  { id: "aq", name: "L'Aquila",d: "M40 195 L320 195 L320 470 L40 470 Z" },
  { id: "ch", name: "Chieti",  d: "M320 290 L700 290 L760 460 L320 470 Z" },
];

// Strade — A14, A25, SS17, SS5
const ROADS = [
  // A14 coast
  { id: "A14", kind: "highway", d: "M650 110 C 660 200, 690 320, 740 470", label: "A14" },
  // A25
  { id: "A25", kind: "highway", d: "M120 380 C 240 360, 360 320, 470 290 C 560 270, 620 250, 680 235", label: "A25" },
  // SS17
  { id: "SS17", kind: "trunk", d: "M635 232 C 580 250, 500 270, 410 280 C 360 285, 300 310, 250 350 C 200 400, 165 415, 130 430", label: "SS17" },
  // SS5
  { id: "SS5", kind: "trunk", d: "M180 235 C 250 250, 320 250, 400 245 C 470 240, 540 235, 620 225", label: "SS5" },
  // local
  { id: "SP1", kind: "local", d: "M425 130 C 470 160, 510 200, 555 230" },
  { id: "SP2", kind: "local", d: "M540 165 C 560 200, 580 215, 615 230" },
  { id: "SP3", kind: "local", d: "M345 320 C 290 360, 240 380, 180 395" },
  { id: "SP4", kind: "local", d: "M615 250 C 640 280, 660 320, 670 350" },
];

// User-traced route: Pescara → Sulmona along SS17
// Anchor points on SS17, drawn as an OSRM-snapped LineString.
const ROUTE_PATH = "M635 232 C 580 250, 500 270, 410 280 C 380 285, 360 295, 345 320";
// Alternative route via A25
const ROUTE_ALT = "M635 232 C 600 232, 560 245, 510 268 C 460 285, 410 295, 380 305 C 360 312, 350 318, 345 320";

// Roadworks (active)
const ROADWORKS = [
  { id: "rw1", x: 470, y: 287, severity: "restricted", title: "Riasfaltatura SS17 km 14+200", until: "20 mag" },
  { id: "rw2", x: 720, y: 380, severity: "advisory",   title: "Restringimento A14 — uscita Lanciano", until: "31 mag" },
];

// Bridges with AINOP id (clickable later)
const BRIDGES = [
  { id: "br1", x: 412, y: 281, ainop: "AB-064-A1289", capacity: "60 t" },
  { id: "br2", x: 358, y: 308, ainop: "AB-064-A0712", capacity: "44 t" },
];

// Cities (labels)
const CITIES = [
  { x: 642, y: 230, name: "Pescara", major: true },
  { x: 580, y: 215, name: "Spoltore" },
  { x: 595, y: 300, name: "Chieti" },
  { x: 450, y: 252, name: "Popoli T." },
  { x: 358, y: 315, name: "Sulmona" },
  { x: 210, y: 245, name: "L'Aquila", major: true },
  { x: 178, y: 385, name: "Avezzano" },
  { x: 425, y: 132, name: "Teramo", major: true },
  { x: 552, y: 158, name: "Penne" },
  { x: 642, y: 360, name: "Lanciano" },
  { x: 705, y: 425, name: "Vasto" },
];

// ---- map component --------------------------------------------------------
function PNTEMap({
  height = 460,
  showRoute = true,
  showAlt = false,
  highlightIntersected = true,
  showRoadworks = true,
  showBridges = true,
  showLabels = true,
  showLegend = true,
  showZoom = true,
  showCompass = true,
  variant = "default", // "default" | "minimal"
}) {
  const intersected = new Set(["pescara", "spoltore", "popoli", "sulmona"]);
  const provIntersected = new Set(["pe", "aq"]);

  const styles = {
    container: {
      position: "relative",
      width: "100%",
      height,
      background: "var(--map-bg)",
      borderRadius: 8,
      overflow: "hidden",
      border: "1px solid var(--line)",
    },
  };

  return (
    <div style={styles.container}>
      <svg viewBox="0 0 800 520" width="100%" height="100%" preserveAspectRatio="xMidYMid slice">
        <defs>
          <pattern id="hatch" width="6" height="6" patternUnits="userSpaceOnUse" patternTransform="rotate(45)">
            <line x1="0" y1="0" x2="0" y2="6" stroke="var(--accent)" strokeWidth="1.4" opacity="0.35" />
          </pattern>
          <pattern id="dotgrid" width="14" height="14" patternUnits="userSpaceOnUse">
            <circle cx="1" cy="1" r="0.6" fill="var(--ink-3)" opacity="0.18" />
          </pattern>
          <filter id="softshadow" x="-20%" y="-20%" width="140%" height="140%">
            <feDropShadow dx="0" dy="1" stdDeviation="0.8" floodOpacity="0.18" />
          </filter>
        </defs>

        {/* dotted background */}
        <rect width="800" height="520" fill="url(#dotgrid)" />

        {/* coast — sea band right */}
        <path d="M755 0 L800 0 L800 520 L760 520 C 740 460, 740 400, 750 320 C 760 220, 750 140, 755 0 Z"
              fill="var(--map-water)" opacity="0.7" />

        {/* provinces */}
        {PROVINCES.map(p => (
          <path key={p.id} d={p.d}
                fill={provIntersected.has(p.id) ? "color-mix(in oklch, var(--accent-bg), white 30%)" : "var(--surface-2)"}
                stroke="var(--line-2)" strokeWidth="0.8" strokeDasharray="4 3" />
        ))}

        {/* comuni */}
        {COMUNI.map(c => {
          const hit = highlightIntersected && intersected.has(c.id);
          return (
            <path key={c.id} d={c.d}
                  fill={hit ? "url(#hatch)" : "var(--surface)"}
                  stroke={hit ? "var(--accent)" : "var(--line)"}
                  strokeWidth={hit ? "1.2" : "0.6"}
                  opacity={variant === "minimal" ? 0.6 : 1} />
          );
        })}

        {/* roads */}
        {ROADS.map(r => {
          const w = r.kind === "highway" ? 3.4 : r.kind === "trunk" ? 2.2 : 1.2;
          const c = r.kind === "highway" ? "var(--ink-2)" : r.kind === "trunk" ? "var(--ink-3)" : "var(--line-2)";
          return <path key={r.id} d={r.d} fill="none" stroke={c} strokeWidth={w} strokeLinecap="round" />;
        })}

        {/* highway labels */}
        {variant !== "minimal" && (
          <g>
            <g transform="translate(700,160)">
              <rect x="-12" y="-8" width="24" height="14" rx="2" fill="var(--ink)" />
              <text x="0" y="2" textAnchor="middle" fill="white" fontSize="9" fontFamily="DM Mono" fontWeight="600">A14</text>
            </g>
            <g transform="translate(380,295)">
              <rect x="-12" y="-8" width="24" height="14" rx="2" fill="var(--ink)" />
              <text x="0" y="2" textAnchor="middle" fill="white" fontSize="9" fontFamily="DM Mono" fontWeight="600">A25</text>
            </g>
            <g transform="translate(560,260)">
              <rect x="-13" y="-8" width="26" height="13" rx="2" fill="var(--surface)" stroke="var(--ink-3)" />
              <text x="0" y="2" textAnchor="middle" fill="var(--ink-2)" fontSize="9" fontFamily="DM Mono" fontWeight="600">SS17</text>
            </g>
          </g>
        )}

        {/* alternative route (faint) */}
        {showAlt && (
          <path d={ROUTE_ALT} fill="none" stroke="var(--route-alt)" strokeWidth="3"
                strokeDasharray="6 4" strokeLinecap="round" opacity="0.85" />
        )}

        {/* main route */}
        {showRoute && (
          <>
            <path d={ROUTE_PATH} fill="none" stroke="white" strokeWidth="6.5" strokeLinecap="round" opacity="0.9" />
            <path d={ROUTE_PATH} fill="none" stroke="var(--route)" strokeWidth="3.4" strokeLinecap="round" />
          </>
        )}

        {/* route waypoints */}
        {showRoute && (
          <>
            <g transform="translate(635,232)">
              <circle r="7" fill="white" stroke="var(--route)" strokeWidth="2" />
              <circle r="3" fill="var(--route)" />
            </g>
            <g transform="translate(345,320)">
              <circle r="9" fill="var(--route)" />
              <path d="M0 -4 L0 0 L3 2" stroke="white" strokeWidth="1.6" fill="none" strokeLinecap="round" />
            </g>
          </>
        )}

        {/* bridges */}
        {showBridges && BRIDGES.map(b => (
          <g key={b.id} transform={`translate(${b.x},${b.y})`}>
            <rect x="-7" y="-7" width="14" height="14" rx="2" fill="var(--surface)" stroke="var(--ink-2)" strokeWidth="1" />
            <path d="M-4 -1 L4 -1 M-4 -1 L-4 3 M4 -1 L4 3 M-2.5 1 C -2.5 2.2, -1.2 3, 0 3 C 1.2 3, 2.5 2.2, 2.5 1"
                  stroke="var(--ink-2)" strokeWidth="0.9" fill="none" />
          </g>
        ))}

        {/* roadworks */}
        {showRoadworks && ROADWORKS.map(r => (
          <g key={r.id} transform={`translate(${r.x},${r.y})`} filter="url(#softshadow)">
            <circle r="11" fill="white" stroke={r.severity === "restricted" ? "var(--danger)" : "var(--accent)"} strokeWidth="1.6" />
            <path d="M0 -5 L4 4 L-4 4 Z" fill={r.severity === "restricted" ? "var(--danger)" : "var(--accent)"} />
            <rect x="-0.5" y="-2.5" width="1" height="3" fill="white" />
            <rect x="-0.5" y="2" width="1" height="0.8" fill="white" />
          </g>
        ))}

        {/* city labels */}
        {showLabels && CITIES.map(c => (
          <g key={c.name} transform={`translate(${c.x},${c.y})`}>
            <circle r={c.major ? 3 : 2} fill="var(--ink)" />
            <text x={c.major ? 7 : 5} y="3" fontSize={c.major ? 11 : 10}
                  fill="var(--ink)" fontWeight={c.major ? 600 : 500}>
              {c.name}
            </text>
          </g>
        ))}
      </svg>

      {/* overlays */}
      {showZoom && (
        <div style={{ position: "absolute", top: 12, right: 12, display: "flex", flexDirection: "column",
                      background: "var(--surface)", border: "1px solid var(--line)", borderRadius: 7,
                      boxShadow: "0 1px 2px rgba(0,0,0,.04)" }}>
          {["+", "−", "⌖"].map((s, i) => (
            <button key={i} style={{
              width: 30, height: 30, border: "none", background: "transparent",
              borderTop: i === 0 ? "none" : "1px solid var(--line)",
              cursor: "pointer", color: "var(--ink-2)", fontSize: 14, fontWeight: 500,
            }}>{s}</button>
          ))}
        </div>
      )}

      {showCompass && (
        <div style={{ position: "absolute", top: 12, left: 12,
                      background: "var(--surface)", border: "1px solid var(--line)",
                      borderRadius: 999, width: 32, height: 32,
                      display: "flex", alignItems: "center", justifyContent: "center",
                      fontSize: 10, fontWeight: 600, color: "var(--ink-2)",
                      letterSpacing: 1 }}>
          N
        </div>
      )}

      {showLegend && (
        <div style={{ position: "absolute", bottom: 12, left: 12,
                      background: "var(--surface)", border: "1px solid var(--line)",
                      borderRadius: 8, padding: "8px 10px", fontSize: 11,
                      display: "flex", gap: 14, alignItems: "center" }}>
          <span style={{ display: "flex", alignItems: "center", gap: 6 }}>
            <span style={{ width: 16, height: 3, background: "var(--route)", borderRadius: 2 }} />
            Percorso
          </span>
          <span style={{ display: "flex", alignItems: "center", gap: 6 }}>
            <span style={{ width: 16, height: 3, background: "var(--route-alt)", borderRadius: 2,
                           backgroundImage: "repeating-linear-gradient(90deg, var(--route-alt) 0 4px, transparent 4px 7px)" }} />
            Alternativa
          </span>
          <span style={{ display: "flex", alignItems: "center", gap: 6 }}>
            <span style={{ width: 10, height: 10, borderRadius: 999, background: "var(--accent-bg)",
                           border: "1px solid var(--accent)" }} />
            Cantiere
          </span>
          <span style={{ display: "flex", alignItems: "center", gap: 6 }}>
            <span style={{ width: 10, height: 10, background: "url(#hatch), var(--accent-bg)",
                           border: "1px solid var(--accent)", borderRadius: 2 }} />
            Comune attraversato
          </span>
        </div>
      )}

      {/* scale bar */}
      <div style={{ position: "absolute", bottom: 12, right: 12,
                    background: "var(--surface)", border: "1px solid var(--line)",
                    borderRadius: 6, padding: "5px 8px", fontSize: 10.5,
                    color: "var(--ink-2)", display: "flex", gap: 8, alignItems: "center" }}>
        <span style={{ width: 40, height: 4, borderLeft: "1px solid var(--ink-3)",
                       borderRight: "1px solid var(--ink-3)",
                       borderBottom: "1px solid var(--ink-3)" }} />
        <span className="mono">10 km</span>
      </div>
    </div>
  );
}

window.PNTEMap = PNTEMap;

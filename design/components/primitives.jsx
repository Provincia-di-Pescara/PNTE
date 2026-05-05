/* global React */
const { useState, useMemo, useEffect, useRef } = React;

// ---- shared bits ----------------------------------------------------------
const Chip = ({ tone = "default", dot = true, children }) => (
  <span className={`chip chip-${tone}`}>
    {dot && <span className="dot" />}
    {children}
  </span>
);

const StatusPill = ({ state }) => {
  const map = {
    draft:               { tone: "default", label: "Bozza" },
    submitted:           { tone: "info",    label: "Inviata" },
    waiting_clearances:  { tone: "amber",   label: "Attesa nulla osta" },
    waiting_payment:     { tone: "amber",   label: "Attesa pagamento" },
    approved:            { tone: "success", label: "Autorizzata" },
    rejected:            { tone: "danger",  label: "Respinta" },
    expired:             { tone: "default", label: "Scaduta" },
  };
  const m = map[state] || map.draft;
  return <Chip tone={m.tone}>{m.label}</Chip>;
};

const Avatar = ({ name, tone = "amber" }) => {
  const initials = name.split(" ").map(w => w[0]).slice(0, 2).join("").toUpperCase();
  const bg = tone === "amber" ? "var(--accent-bg)"
           : tone === "info" ? "var(--info-bg)"
           : "var(--surface-2)";
  const fg = tone === "amber" ? "var(--accent-ink)"
           : tone === "info" ? "var(--info)"
           : "var(--ink-2)";
  return (
    <div style={{
      width: 28, height: 28, borderRadius: 999,
      background: bg, color: fg,
      display: "flex", alignItems: "center", justifyContent: "center",
      fontWeight: 600, fontSize: 11, letterSpacing: 0.5,
      border: "1px solid var(--line)"
    }}>
      {initials}
    </div>
  );
};

// minimalist line-icon helper (one stroke set, 16px viewbox)
const Icon = ({ d, size = 16, stroke = 1.6 }) => (
  <svg width={size} height={size} viewBox="0 0 16 16" fill="none"
       stroke="currentColor" strokeWidth={stroke}
       strokeLinecap="round" strokeLinejoin="round">
    <path d={d} />
  </svg>
);
const I = {
  search:   "M7.2 12.4a5.2 5.2 0 1 0 0-10.4 5.2 5.2 0 0 0 0 10.4Zm3.7-1.5L14 14",
  plus:     "M8 3v10M3 8h10",
  check:    "M3 8.5 6.5 12 13 4.5",
  x:        "M3.5 3.5l9 9M12.5 3.5l-9 9",
  chevron:  "M6 4l4 4-4 4",
  chevDown: "M4 6l4 4 4-4",
  truck:    "M1.5 5.5h7v5h-7zM8.5 7h3.5l2 2v1.5h-5.5zM4 12.5a1.2 1.2 0 1 0 0-2.4 1.2 1.2 0 0 0 0 2.4Zm7.5 0a1.2 1.2 0 1 0 0-2.4 1.2 1.2 0 0 0 0 2.4Z",
  pin:      "M8 14s4.5-4.2 4.5-7.5a4.5 4.5 0 0 0-9 0C3.5 9.8 8 14 8 14Zm0-5.8a1.7 1.7 0 1 0 0-3.4 1.7 1.7 0 0 0 0 3.4Z",
  layers:   "M8 1.5l6.5 3.3L8 8.1 1.5 4.8 8 1.5ZM1.5 8 8 11.3 14.5 8M1.5 11.3 8 14.5l6.5-3.2",
  filter:   "M2 3.5h12L9.5 8.6V13L6.5 14.5V8.6L2 3.5Z",
  doc:      "M3.5 1.5h6L13 5v9.5H3.5zM9 1.5V5h4",
  flag:     "M3.5 14V2.5h7l-1 2.5 1 2.5h-7",
  bridge:   "M1.5 6.5h13M3 6.5V11M5.5 6.5C5.5 8 6.6 9.5 8 9.5s2.5-1.5 2.5-3M13 6.5V11M1.5 11h13",
  bell:     "M4 11h8l-1.2-1.5V7a2.8 2.8 0 0 0-5.6 0v2.5L4 11Zm2.5 1.5a1.5 1.5 0 0 0 3 0",
  user:     "M8 8.5a2.7 2.7 0 1 0 0-5.4 2.7 2.7 0 0 0 0 5.4ZM2.5 14c.5-2.5 2.8-4 5.5-4s5 1.5 5.5 4",
  qr:       "M2 2h4v4H2zM10 2h4v4h-4zM2 10h4v4H2zM10 10h2v2h-2zM12 12h2v2h-2zM10 14h2",
  axles:    "M2 8h12M5 8a1.5 1.5 0 1 0-3 0 1.5 1.5 0 0 0 3 0Zm9 0a1.5 1.5 0 1 1-3 0 1.5 1.5 0 1 1 3 0Z",
  euro:     "M11.5 4.5A4 4 0 1 0 11.5 11.5M3.5 7h6M3.5 9h6",
  alert:    "M8 2 14.5 13.5h-13L8 2Zm0 4v3.5M8 11.5v.6",
  arrow:    "M3 8h10m-3.5-3.5L13 8l-3.5 3.5",
  download: "M8 2v8m-3-3 3 3 3-3M3 13h10",
  share:    "M11.5 2.5a1.8 1.8 0 1 1 0 3.6 1.8 1.8 0 0 1 0-3.6Zm-7 4a1.8 1.8 0 1 1 0 3.6 1.8 1.8 0 0 1 0-3.6Zm7 4a1.8 1.8 0 1 1 0 3.6 1.8 1.8 0 0 1 0-3.6ZM6 7.5l4-2M6 9l4 2",
  refresh:  "M14 8a6 6 0 1 1-1.7-4.2M14 2v3.5h-3.5",
  more:     "M3.5 8a.7.7 0 1 1 0-.1ZM8 8a.7.7 0 1 1 0-.1ZM12.5 8a.7.7 0 1 1 0-.1Z",
  cone:     "M8 2 4.5 13.5h7L8 2Zm-2 7h4M5 11h6",
  clock:    "M8 14.5a6.5 6.5 0 1 0 0-13 6.5 6.5 0 0 0 0 13Zm0-9.5V8l2.5 1.5",
  map:      "M5.5 2 1.5 3.5v10.5L5.5 12.5l5 1.5 4-1.5V2l-4 1.5-5-1.5ZM5.5 2v10.5M10.5 3.5V14",
  bolt:     "M9 1.5 4 9h3.5l-.5 5.5L12 7H8.5l.5-5.5Z",
};

// shared "company" picker mock + applications dataset
const APPLICATIONS = [
  { id: "GTE-2026-002418", company: "Ferraris Trasporti S.p.A.", from: "Pescara (PE)", to: "Sulmona (AQ)",
    state: "approved", km: 84.2, weight: 76.4, axles: 8, day: "12 mag 2026", entities: 6, fee: 412.80, plate: "FT 728 ZR" },
  { id: "GTE-2026-002417", company: "Adriatica Logistic S.r.l.", from: "Pescara (PE)", to: "L'Aquila (AQ)",
    state: "waiting_clearances", km: 112.8, weight: 92.1, axles: 10, day: "14 mag 2026", entities: 9, fee: 0, plate: "AL 091 KP" },
  { id: "GTE-2026-002416", company: "Edilstrade Abruzzo", from: "Chieti (CH)", to: "Avezzano (AQ)",
    state: "waiting_payment", km: 98.5, weight: 64.8, axles: 7, day: "13 mag 2026", entities: 7, fee: 287.40, plate: "ED 442 BB" },
  { id: "GTE-2026-002415", company: "Ferraris Trasporti S.p.A.", from: "Penne (PE)", to: "Teramo (TE)",
    state: "submitted", km: 56.1, weight: 48.0, axles: 6, day: "15 mag 2026", entities: 4, fee: 0, plate: "FT 304 LM" },
  { id: "GTE-2026-002414", company: "Trasporti Maiella", from: "Lanciano (CH)", to: "Roseto degli Abruzzi (TE)",
    state: "rejected", km: 121.0, weight: 84.2, axles: 9, day: "10 mag 2026", entities: 8, fee: 0, plate: "TM 619 GH" },
  { id: "GTE-2026-002413", company: "Adriatica Logistic S.r.l.", from: "Vasto (CH)", to: "Pescara (PE)",
    state: "draft", km: 73.5, weight: 58.0, axles: 6, day: "—", entities: 5, fee: 0, plate: "AL 220 RM" },
];

// crossed entities (used by route detail)
const ENTITIES_CROSSED = [
  { name: "Provincia di Pescara",   type: "Provincia",  km: 18.2, status: "issuer", fee: 96.40 },
  { name: "Comune di Pescara",      type: "Comune",     km: 4.6,  status: "approved", fee: 22.10 },
  { name: "Comune di Spoltore",     type: "Comune",     km: 3.1,  status: "approved", fee: 14.80 },
  { name: "ANAS — SS17",            type: "Gestore",    km: 31.4, status: "approved", fee: 168.20 },
  { name: "Comune di Popoli Terme", type: "Comune",     km: 5.2,  status: "pending",  fee: 24.50 },
  { name: "Comune di Sulmona",      type: "Comune",     km: 8.0,  status: "pending",  fee: 38.10 },
  { name: "Provincia dell'Aquila",  type: "Provincia",  km: 13.7, status: "approved", fee: 48.70 },
];

window.GTE = { Chip, StatusPill, Avatar, Icon, I, APPLICATIONS, ENTITIES_CROSSED };

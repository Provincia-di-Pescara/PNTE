/* global React, PNTE, PNTEMap */
const { useState: useStateApp } = React;
const { Chip, StatusPill, Avatar, Icon, I, APPLICATIONS, ENTITIES_CROSSED } = window.PNTE;

// ============================================================================
// Top bar (shared across roles)
// ============================================================================
function TopBar({ role, roleLabel, onRoleHover }) {
  return (
    <div style={{
      display: "flex", alignItems: "center", gap: 16,
      padding: "10px 20px",
      borderBottom: "1px solid var(--line)",
      background: "var(--surface)",
      height: 56,
    }}>
      {/* logo */}
      <div style={{ display: "flex", alignItems: "center", gap: 10 }}>
        <div style={{
          width: 28, height: 28, borderRadius: 6,
          background: "var(--ink)", color: "var(--bg)",
          display: "flex", alignItems: "center", justifyContent: "center",
          fontWeight: 700, fontSize: 12, letterSpacing: 0.5,
        }}>
          PNTE
        </div>
        <div style={{ display: "flex", flexDirection: "column", lineHeight: 1.15 }}>
          <span style={{ fontWeight: 600, fontSize: 13.5 }}>PNTE</span>
          <span style={{ fontSize: 11, color: "var(--ink-3)" }}>Provincia di Pescara · Ente capofila</span>
        </div>
      </div>

      <div style={{
        height: 22, width: 1, background: "var(--line)", marginLeft: 4
      }} />

      {/* tenant */}
      <div className="chip" style={{ background: "var(--surface-2)" }}>
        <span style={{ width: 6, height: 6, borderRadius: 999, background: "var(--success)" }} />
        Ambiente: Produzione
      </div>

      {/* search */}
      <div style={{
        flex: 1, maxWidth: 480,
        display: "flex", alignItems: "center", gap: 8,
        height: 34, padding: "0 12px",
        background: "var(--surface-2)", border: "1px solid var(--line)",
        borderRadius: 8, color: "var(--ink-3)",
      }}>
        <Icon d={I.search} size={14} />
        <span style={{ fontSize: 13 }}>Cerca pratica, targa, P.IVA, comune…</span>
        <span style={{ marginLeft: "auto", display: "flex", gap: 3 }}>
          <span className="kbd">⌘</span><span className="kbd">K</span>
        </span>
      </div>

      {/* right cluster */}
      <button className="btn btn-ghost" style={{ padding: 6 }} aria-label="Notifiche">
        <Icon d={I.bell} />
      </button>
      <div style={{
        display: "flex", alignItems: "center", gap: 8,
        padding: "4px 10px 4px 4px", border: "1px solid var(--line)",
        borderRadius: 999, background: "var(--surface-2)",
      }}>
        <Avatar name={roleLabel} tone="amber" />
        <div style={{ display: "flex", flexDirection: "column", lineHeight: 1.15 }}>
          <span style={{ fontSize: 12.5, fontWeight: 600 }}>{roleLabel}</span>
          <span style={{ fontSize: 10.5, color: "var(--ink-3)" }}>{role}</span>
        </div>
      </div>
    </div>
  );
}

// ============================================================================
// Side nav
// ============================================================================
function SideNav({ items, active, onSelect }) {
  return (
    <nav style={{
      width: 220, padding: 16,
      borderRight: "1px solid var(--line)",
      background: "var(--surface)",
      display: "flex", flexDirection: "column", gap: 2,
      flexShrink: 0,
    }}>
      <div style={{ fontSize: 10.5, color: "var(--ink-3)",
                    textTransform: "uppercase", letterSpacing: 1.2,
                    padding: "8px 8px 4px" }}>
        Navigazione
      </div>
      {items.map(it => {
        const isActive = it.key === active;
        return (
          <button key={it.key} onClick={() => onSelect && onSelect(it.key)}
            style={{
              display: "flex", alignItems: "center", gap: 10,
              padding: "8px 10px", borderRadius: 7,
              border: "none", background: isActive ? "var(--surface-3)" : "transparent",
              color: isActive ? "var(--ink)" : "var(--ink-2)",
              fontSize: 13, fontWeight: isActive ? 600 : 500,
              cursor: "pointer", textAlign: "left",
              fontFamily: "inherit",
            }}>
            <Icon d={I[it.icon]} />
            <span style={{ flex: 1 }}>{it.label}</span>
            {it.badge && <Chip tone={it.badgeTone || "default"}>{it.badge}</Chip>}
          </button>
        );
      })}

      <div style={{ flex: 1 }} />
      <div style={{ padding: "10px", borderTop: "1px solid var(--line)", marginTop: 12,
                    fontSize: 11, color: "var(--ink-3)", lineHeight: 1.5 }}>
        <span className="mono">v0.5.2</span> · EUPL-1.2<br/>
        Riuso · Developers Italia
      </div>
    </nav>
  );
}

window.PNTEShell = { TopBar, SideNav };

/* global React, PNTE */
const { Chip: Cy, Avatar: Avy, Icon: Icy, I: IIy } = window.PNTE;

// ===========================================================================
// system-admin — Pannello /system
// Ruolo separato: zero accesso ai dati di business, solo infrastruttura.
// Vault connettori · SMTP/IMAP · scheduler · telemetria anonima · tenant
// ===========================================================================
function SystemAdminScreen() {
  const [section, setSection] = React.useState("overview");

  const groups = [
    { title: "Piattaforma", items: [
      { key: "overview",  icon: IIy.layers, label: "Overview",        sub: "Salute servizi" },
      { key: "tenants",   icon: IIy.flag,   label: "Tenant",          sub: "9 enti · 1 capofila" },
      { key: "telemetry", icon: IIy.bolt,   label: "Telemetria",      sub: "Aggregata · anonima" },
    ]},
    { title: "Vault & connettori", items: [
      { key: "vault",     icon: IIy.qr,     label: "Vault connettori", sub: "PDND · OIDC · PagoPA", badge: "•" },
      { key: "smtp",      icon: IIy.bell,   label: "SMTP/IMAP madre",  sub: "PEC istituzionale" },
      { key: "scheduler", icon: IIy.clock,  label: "Scheduler",        sub: "12 job attivi" },
    ]},
    { title: "Dataset geo", items: [
      { key: "geo",       icon: IIy.map,    label: "Geo dataset",      sub: "OSM · AINOP · cantieri", badge: "•" },
    ]},
    { title: "Sistema", items: [
      { key: "audit",     icon: IIy.doc,    label: "Audit infra",      sub: "Solo livello sistema" },
      { key: "release",   icon: IIy.refresh,label: "Release & migrazioni", sub: "v0.5.2 · 13 mag" },
    ]},
  ];

  return (
    <div style={{ display: "flex", height: "100%", overflow: "hidden" }}>

      {/* Left rail */}
      <aside style={{ width: 264, flexShrink: 0, borderRight: "1px solid var(--line)",
                      background: "var(--surface)", display: "flex", flexDirection: "column" }}>
        <div style={{ padding: "18px 20px 14px", borderBottom: "1px solid var(--line)" }}>
          <div style={{ fontSize: 10.5, letterSpacing: 1.4, color: "var(--ink-3)",
                        textTransform: "uppercase", fontWeight: 500 }}>Pannello /system</div>
          <h1 style={{ margin: "4px 0 0", fontSize: 18, fontWeight: 600 }}>Infrastruttura</h1>
          <div style={{ fontSize: 11.5, color: "var(--ink-3)", marginTop: 2, lineHeight: 1.45 }}>
            Nessun accesso a pratiche, P.IVA, targhe o PDF.
          </div>
        </div>

        <div style={{ flex: 1, overflow: "auto", padding: "10px 8px 16px" }}>
          {groups.map(g => (
            <div key={g.title} style={{ marginBottom: 12 }}>
              <div style={{ fontSize: 10, color: "var(--ink-3)", letterSpacing: 1.2,
                            textTransform: "uppercase", fontWeight: 600,
                            padding: "8px 12px 4px" }}>{g.title}</div>
              {g.items.map(it => {
                const on = it.key === section;
                return (
                  <button key={it.key} onClick={() => setSection(it.key)} className="row-hover"
                    style={{ width: "100%", textAlign: "left", border: "none",
                             background: on ? "var(--accent-bg)" : "transparent",
                             color: on ? "var(--accent-ink)" : "var(--ink)",
                             display: "flex", alignItems: "center", gap: 10,
                             padding: "8px 12px", borderRadius: 7, cursor: "pointer",
                             fontFamily: "inherit" }}>
                    <span style={{ width: 28, height: 28, borderRadius: 7,
                                   background: on ? "var(--surface)" : "var(--surface-2)",
                                   border: "1px solid var(--line)",
                                   display: "flex", alignItems: "center",
                                   justifyContent: "center",
                                   color: on ? "var(--accent-ink)" : "var(--ink-2)" }}>
                      <Icy d={it.icon} size={14} />
                    </span>
                    <span style={{ flex: 1, minWidth: 0 }}>
                      <span style={{ display: "block", fontSize: 12.5, fontWeight: on ? 600 : 500 }}>{it.label}</span>
                      <span style={{ display: "block", fontSize: 10.5, color: "var(--ink-3)" }}>{it.sub}</span>
                    </span>
                    {it.badge === "•" && <span style={{ width: 7, height: 7, borderRadius: 999, background: "var(--accent)" }} />}
                  </button>
                );
              })}
            </div>
          ))}
        </div>

        <div style={{ borderTop: "1px solid var(--line)", padding: "10px 14px",
                      display: "flex", alignItems: "center", gap: 10 }}>
          <Avy name="Roberto Iezzi" tone="info" />
          <div style={{ flex: 1, minWidth: 0 }}>
            <div style={{ fontSize: 12, fontWeight: 600 }}>Roberto Iezzi</div>
            <div style={{ fontSize: 10.5, color: "var(--ink-3)" }}>system-admin · IT Provincia</div>
          </div>
        </div>
      </aside>

      {/* Main */}
      <div style={{ flex: 1, display: "flex", flexDirection: "column", overflow: "hidden", minWidth: 0 }}>
        {section === "overview"  && <OverviewSec />}
        {section === "tenants"   && <TenantsSec />}
        {section === "vault"     && <VaultSec />}
        {section === "smtp"      && <SmtpSec />}
        {section === "scheduler" && <SchedulerSec />}
        {section === "telemetry" && <TelemetrySec />}
        {section === "geo"       && <GeoDatasetSec />}
        {section === "audit"     && <AuditSec />}
        {section === "release"   && <ReleaseSec />}
      </div>
    </div>
  );
}

// ============================================================================
function HeadY({ title, subtitle, right }) {
  return (
    <div style={{ display: "flex", alignItems: "flex-end", gap: 14 }}>
      <div style={{ flex: 1 }}>
        <h2 style={{ margin: 0, fontSize: 20, fontWeight: 600, letterSpacing: "-0.01em" }}>{title}</h2>
        {subtitle && <div style={{ fontSize: 12.5, color: "var(--ink-3)", marginTop: 4, maxWidth: 720, lineHeight: 1.5 }}>{subtitle}</div>}
      </div>
      <div style={{ display: "flex", gap: 8 }}>{right}</div>
    </div>
  );
}
function DivY({ title }) {
  return (
    <div style={{ display: "flex", alignItems: "center", gap: 12, margin: "20px 0 10px" }}>
      <div style={{ fontSize: 11, color: "var(--ink-3)", letterSpacing: 1.2,
                    textTransform: "uppercase", fontWeight: 600 }}>{title}</div>
      <div style={{ flex: 1, height: 1, background: "var(--line)" }} />
    </div>
  );
}

// ============================================================================
function OverviewSec() {
  const services = [
    { name: "app · Laravel 13",      ver: "v0.5.2",     status: "ok",   uptime: "99,98%", lat: "84 ms" },
    { name: "db · PostgreSQL 16",    ver: "PostGIS 3.4",status: "ok",   uptime: "100%",   lat: "3 ms" },
    { name: "redis · queue",         ver: "7.2",        status: "ok",   uptime: "99,99%", lat: "1 ms" },
    { name: "osrm · routing",        ver: "Italia 26.03",status: "ok",  uptime: "99,71%", lat: "184 ms" },
    { name: "browsershot · PDF",     ver: "Chromium 132",status: "warn",uptime: "98,1%",  lat: "1.4 s" },
    { name: "imap · listener PEC",   ver: "scheduler",  status: "ok",   uptime: "99,9%",  lat: "—" },
  ];
  return (
    <div style={{ padding: "20px 24px", overflow: "auto", flex: 1 }}>
      <HeadY title="Overview · piattaforma" subtitle="Salute aggregata dei servizi. Nessun dato applicativo è esposto su questa pagina."
        right={<><button className="btn"><Icy d={IIy.refresh} size={11} /> Refresh</button>
                <button className="btn btn-primary">Apri runbook</button></>} />

      <div style={{ display: "grid", gridTemplateColumns: "repeat(4,1fr)", gap: 12, marginTop: 16 }}>
        {[
          ["Tenant attivi","9","1 capofila"],
          ["Job in coda","48","redis · 0 fail (24h)"],
          ["Storage usato","124,8 GB","ParER · 10 anni"],
          ["SLA piattaforma","99,94%","ult. 30 gg", "success"],
        ].map(([l,v,s,t]) => (
          <div key={l} className="card" style={{ padding: 14 }}>
            <div style={{ fontSize: 11, color: "var(--ink-3)", letterSpacing: 0.8,
                          textTransform: "uppercase", fontWeight: 500 }}>{l}</div>
            <div className="num" style={{ fontSize: 24, fontWeight: 600, marginTop: 4,
                          color: t === "success" ? "var(--success)" : "var(--ink)" }}>{v}</div>
            <div style={{ fontSize: 11.5, color: "var(--ink-3)" }}>{s}</div>
          </div>
        ))}
      </div>

      <DivY title="Servizi" />
      <div className="card" style={{ overflow: "hidden" }}>
        <div style={{ display: "grid", gridTemplateColumns: "1.4fr 1fr 100px 100px 80px 80px",
                      padding: "10px 14px", fontSize: 10.5, color: "var(--ink-3)",
                      letterSpacing: 0.8, textTransform: "uppercase", fontWeight: 500,
                      background: "var(--surface-2)", borderBottom: "1px solid var(--line)" }}>
          <div>Servizio</div><div>Build</div><div>Stato</div><div>Uptime 30gg</div><div>Lat. p95</div><div></div>
        </div>
        {services.map((s,i) => (
          <div key={s.name} className="row-hover" style={{ display: "grid",
            gridTemplateColumns: "1.4fr 1fr 100px 100px 80px 80px",
            padding: "12px 14px", alignItems: "center", fontSize: 12.5,
            borderBottom: i < services.length - 1 ? "1px solid var(--line)" : "none" }}>
            <div className="mono" style={{ fontWeight: 500 }}>{s.name}</div>
            <div className="mono" style={{ color: "var(--ink-3)", fontSize: 11.5 }}>{s.ver}</div>
            <Cy tone={s.status === "ok" ? "success" : s.status === "warn" ? "amber" : "danger"}>
              {s.status === "ok" ? "operativo" : s.status === "warn" ? "attenzione" : "errore"}
            </Cy>
            <div className="num">{s.uptime}</div>
            <div className="num" style={{ color: "var(--ink-3)" }}>{s.lat}</div>
            <button className="btn btn-sm btn-ghost" style={{ width: 26, padding: 0 }}>
              <Icy d={IIy.more} size={12} />
            </button>
          </div>
        ))}
      </div>
    </div>
  );
}

// ============================================================================
function TenantsSec() {
  const tenants = [
    { ipa: "c_g482", name: "Provincia di Pescara",  capofila: true,  users: 23, since: "01 mar 2026", apps30: 142, status: "ok" },
    { ipa: "c_a345", name: "Provincia di L'Aquila", capofila: false, users: 14, since: "12 mar 2026", apps30: 98,  status: "ok" },
    { ipa: "c_c632", name: "Provincia di Chieti",   capofila: false, users: 11, since: "02 apr 2026", apps30: 76,  status: "ok" },
    { ipa: "c_l103", name: "Provincia di Teramo",   capofila: false, users: 9,  since: "18 apr 2026", apps30: 54,  status: "ok" },
    { ipa: "c_g482", name: "Comune di Sulmona",     capofila: false, users: 4,  since: "21 apr 2026", apps30: 18,  status: "warn" },
    { ipa: "c_e372", name: "Comune di Avezzano",    capofila: false, users: 3,  since: "23 apr 2026", apps30: 12,  status: "ok" },
  ];
  return (
    <div style={{ padding: "20px 24px", overflow: "auto", flex: 1 }}>
      <HeadY title="Tenant · enti aderenti"
        subtitle="Elenco enti con piattaforma attiva. Il system-admin abilita il tenant; l'onboarding dell'admin-ente avviene poi via SPID + P7M."
        right={<button className="btn btn-primary"><Icy d={IIy.plus} size={11} /> Abilita tenant</button>} />

      <div className="card" style={{ marginTop: 16, overflow: "hidden" }}>
        <div style={{ display: "grid", gridTemplateColumns: "120px 1fr 100px 80px 110px 90px 50px",
                      padding: "10px 14px", fontSize: 10.5, color: "var(--ink-3)",
                      letterSpacing: 0.8, textTransform: "uppercase", fontWeight: 500,
                      background: "var(--surface-2)", borderBottom: "1px solid var(--line)" }}>
          <div>Cod. IPA</div><div>Denominazione</div><div>Ruolo</div><div>Utenti</div>
          <div>Censito da</div><div>Pratiche 30gg</div><div></div>
        </div>
        {tenants.map((t,i) => (
          <div key={t.ipa+i} className="row-hover" style={{ display: "grid",
            gridTemplateColumns: "120px 1fr 100px 80px 110px 90px 50px",
            padding: "12px 14px", alignItems: "center", fontSize: 12.5,
            borderBottom: i < tenants.length - 1 ? "1px solid var(--line)" : "none" }}>
            <div className="mono" style={{ color: "var(--ink-3)" }}>{t.ipa}</div>
            <div style={{ fontWeight: 500 }}>{t.name}</div>
            <div>
              {t.capofila
                ? <Cy tone="amber">capofila</Cy>
                : <Cy>tenant</Cy>}
            </div>
            <div className="num">{t.users}</div>
            <div className="mono" style={{ color: "var(--ink-3)", fontSize: 11.5 }}>{t.since}</div>
            <div className="num">{t.apps30}</div>
            <button className="btn btn-sm btn-ghost" style={{ width: 26, padding: 0 }}>
              <Icy d={IIy.more} size={12} />
            </button>
          </div>
        ))}
      </div>
    </div>
  );
}

// ============================================================================
function VaultSec() {
  const items = [
    { k: "spid",   name: "OIDC SPID/CIE",       org: "Aruba PEC · IDP", type: "client_secret", rotated: "12 gg fa", expires: "in 78 gg",  status: "ok" },
    { k: "pdnd",   name: "PDND · X.509 voucher",org: "DTD · PDND",      type: "X.509 + JWT",   rotated: "2 gg fa",  expires: "in 12 gg",  status: "warn" },
    { k: "pagopa", name: "PagoPA · API key",    org: "AgID · pagoPA",   type: "Bearer + HSM",  rotated: "ieri",      expires: "in 89 gg",  status: "ok" },
    { k: "ipa",    name: "IPA · OAuth lettura", org: "AgID · IPA",      type: "client_secret", rotated: "30 gg fa", expires: "in 60 gg",  status: "ok" },
    { k: "ainop",  name: "AINOP · X.509 firma", org: "MIT",             type: "X.509",         rotated: "8 gg fa",  expires: "in 110 gg", status: "ok" },
    { k: "aruba",  name: "Aruba Firma PAdES",   org: "Aruba PEC",       type: "OAuth2 + PIN",  rotated: "n/d",       expires: "—",        status: "off" },
  ];
  return (
    <div style={{ padding: "20px 24px", overflow: "auto", flex: 1 }}>
      <HeadY title="Vault connettori"
        subtitle="Credenziali e certificati custoditi in HSM AgID. Le chiavi non sono mai mostrate in chiaro."
        right={<><button className="btn"><Icy d={IIy.refresh} size={11} /> Verifica tutte</button>
                <button className="btn btn-primary"><Icy d={IIy.plus} size={11} /> Aggiungi credenziale</button></>} />

      <div style={{ display: "flex", flexDirection: "column", gap: 10, marginTop: 16 }}>
        {items.map(it => (
          <div key={it.k} className="card" style={{ padding: 14, display: "flex", alignItems: "center", gap: 14 }}>
            <div style={{ width: 40, height: 40, borderRadius: 9,
                          background: it.status === "off" ? "var(--surface-2)" : "var(--accent-bg)",
                          color: it.status === "off" ? "var(--ink-3)" : "var(--accent-ink)",
                          border: "1px solid var(--line)",
                          display: "flex", alignItems: "center", justifyContent: "center" }}>
              <Icy d={IIy.qr} size={18} />
            </div>
            <div style={{ flex: 1 }}>
              <div style={{ display: "flex", alignItems: "center", gap: 8 }}>
                <div style={{ fontSize: 13.5, fontWeight: 600 }}>{it.name}</div>
                <Cy tone={it.status === "ok" ? "success" : it.status === "warn" ? "amber" : "default"}>
                  {it.status === "ok" ? "valido" : it.status === "warn" ? "rotazione consigliata" : "non configurato"}
                </Cy>
              </div>
              <div style={{ fontSize: 11.5, color: "var(--ink-3)", marginTop: 2 }}>{it.org} · {it.type}</div>
            </div>
            <div style={{ textAlign: "right", marginRight: 12 }}>
              <div style={{ fontSize: 11, color: "var(--ink-3)" }}>scade {it.expires}</div>
              <div className="mono" style={{ fontSize: 11, color: "var(--ink-3)" }}>rot. {it.rotated}</div>
            </div>
            <button className="btn btn-sm">Ruota</button>
            <button className="btn btn-sm btn-ghost" style={{ width: 26, padding: 0 }}>
              <Icy d={IIy.more} size={12} />
            </button>
          </div>
        ))}
      </div>
    </div>
  );
}

// ============================================================================
function SmtpSec() {
  return (
    <div style={{ padding: "20px 24px", overflow: "auto", flex: 1 }}>
      <HeadY title="SMTP/IMAP madre"
        subtitle="Casella PEC istituzionale di sistema. Il listener IMAP lavora qui per riconoscere l'ID pratica nell'oggetto."
        right={<><button className="btn">Invia mail di test</button>
                <button className="btn btn-primary">Salva</button></>} />

      <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 14, marginTop: 16 }}>
        <div className="card" style={{ padding: 16 }}>
          <h3 style={{ margin: 0, fontSize: 13.5, fontWeight: 600 }}>SMTP in uscita</h3>
          {[
            ["Host","mail.aruba.pec.it"],["Porta","465 · SSL"],
            ["Username","sistema.PNTE@legalmail.it"],
            ["Mittente","PNTE · Sistema"],
            ["Rate limit","30 mail/min"],
          ].map(([l,v]) => (
            <div key={l} style={{ display: "flex", alignItems: "center", gap: 12,
                  padding: "8px 0", borderBottom: "1px solid var(--line)" }}>
              <span style={{ fontSize: 12.5, color: "var(--ink-2)", flex: 1 }}>{l}</span>
              <span className="mono" style={{ fontSize: 12 }}>{v}</span>
            </div>
          ))}
        </div>
        <div className="card" style={{ padding: 16 }}>
          <h3 style={{ margin: 0, fontSize: 13.5, fontWeight: 600 }}>IMAP in entrata · Listener PEC</h3>
          {[
            ["Host","imaps.pec.aruba.it"],["Porta","993 · TLS 1.3"],
            ["Frequenza poll","ogni 5 min"],
            ["Match oggetto","regex `PNTE-\\d{4}-\\d{6}`"],
            ["Esito default","pending_review"],
          ].map(([l,v]) => (
            <div key={l} style={{ display: "flex", alignItems: "center", gap: 12,
                  padding: "8px 0", borderBottom: "1px solid var(--line)" }}>
              <span style={{ fontSize: 12.5, color: "var(--ink-2)", flex: 1 }}>{l}</span>
              <span className="mono" style={{ fontSize: 12 }}>{v}</span>
            </div>
          ))}
        </div>
      </div>

      <DivY title="Ultimi messaggi PEC processati" />
      <div className="card" style={{ overflow: "hidden" }}>
        {[
          ["14:31","PNTE-2026-002417","Comune di Popoli Terme","allegato PDF · pending_review"],
          ["13:08","PNTE-2026-002411","ANAS — SS17","allegato PDF · pending_review"],
          ["11:48","PNTE-2026-002409","Comune di Sulmona","ricevuta consegna"],
          ["09:14","PNTE-2026-002405","Provincia dell'Aquila","ricevuta accettazione"],
        ].map(([when,prat,who,what],i,arr) => (
          <div key={i} style={{ display: "grid", gridTemplateColumns: "60px 160px 1.2fr 1fr",
                padding: "10px 14px", gap: 10, alignItems: "center", fontSize: 12.5,
                borderBottom: i < arr.length - 1 ? "1px solid var(--line)" : "none" }}>
            <span className="mono" style={{ color: "var(--ink-3)" }}>{when}</span>
            <span className="mono">{prat}</span>
            <span>{who}</span>
            <span style={{ color: "var(--ink-3)" }}>{what}</span>
          </div>
        ))}
      </div>
    </div>
  );
}

// ============================================================================
function SchedulerSec() {
  const jobs = [
    { name: "ipa:sync-pec",                cron: "0 3 * * *",  last: "stanotte 03:00", last_dur: "47s",  status: "ok" },
    { name: "infocamere:sync-companies",   cron: "*/30 * * * *",last: "5 min fa",      last_dur: "12s",  status: "ok" },
    { name: "imap:listen-pec",             cron: "*/5 * * * *", last: "1 min fa",      last_dur: "3s",   status: "ok" },
    { name: "weather:check-allerta",       cron: "0 */1 * * *", last: "13 min fa",     last_dur: "8s",   status: "ok" },
    { name: "agency:re-sync-ateco",        cron: "0 4 1 * *",  last: "1 mag · 04:00", last_dur: "2m 14s",status: "ok" },
    { name: "clearings:expire-T-30",       cron: "0 6 * * *",  last: "stanotte 06:00", last_dur: "11s",  status: "ok" },
    { name: "siope:export-monthly",        cron: "0 2 1 * *",  last: "1 mag · 02:00", last_dur: "1m 02s",status: "warn" },
    { name: "ainop:check-bridges",         cron: "0 3 * * 1",  last: "lun · 03:00",   last_dur: "—",    status: "off" },
  ];
  return (
    <div style={{ padding: "20px 24px", overflow: "auto", flex: 1 }}>
      <HeadY title="Scheduler · job in cron"
        subtitle="Job batch della piattaforma. Sincronizzazioni Once-Only e listener asincroni."
        right={<button className="btn btn-primary">Esegui ora</button>} />
      <div className="card" style={{ marginTop: 16, overflow: "hidden" }}>
        <div style={{ display: "grid", gridTemplateColumns: "1.6fr 130px 1fr 100px 100px 50px",
                      padding: "10px 14px", fontSize: 10.5, color: "var(--ink-3)",
                      letterSpacing: 0.8, textTransform: "uppercase", fontWeight: 500,
                      background: "var(--surface-2)", borderBottom: "1px solid var(--line)" }}>
          <div>Job</div><div>Cron</div><div>Ult. esecuzione</div><div>Durata</div><div>Stato</div><div></div>
        </div>
        {jobs.map((j,i) => (
          <div key={j.name} className="row-hover" style={{ display: "grid",
            gridTemplateColumns: "1.6fr 130px 1fr 100px 100px 50px",
            padding: "10px 14px", alignItems: "center", fontSize: 12.5,
            borderBottom: i < jobs.length - 1 ? "1px solid var(--line)" : "none" }}>
            <div className="mono" style={{ fontWeight: 500 }}>{j.name}</div>
            <div className="mono" style={{ color: "var(--ink-3)" }}>{j.cron}</div>
            <div>{j.last}</div>
            <div className="mono num" style={{ color: "var(--ink-3)" }}>{j.last_dur}</div>
            <Cy tone={j.status === "ok" ? "success" : j.status === "warn" ? "amber" : "default"}>
              {j.status === "ok" ? "ok" : j.status === "warn" ? "ritardo" : "disattivo"}
            </Cy>
            <button className="btn btn-sm btn-ghost" style={{ width: 26, padding: 0 }}>
              <Icy d={IIy.more} size={12} />
            </button>
          </div>
        ))}
      </div>
    </div>
  );
}

// ============================================================================
function TelemetrySec() {
  const stats = [
    ["Login SPID/CIE (24h)","892"],
    ["Pratiche create (24h)","41"],
    ["IUV PagoPA (24h)","27"],
    ["PEC out (24h)","1.842"],
    ["PEC in (24h)","312"],
    ["PDF generati (24h)","36"],
  ];
  // sparkline-ish bars
  const bars = [12,18,15,22,28,24,32,29,35,40,38,44,42,48,52,46,50];
  return (
    <div style={{ padding: "20px 24px", overflow: "auto", flex: 1 }}>
      <HeadY title="Telemetria aggregata"
        subtitle="Solo metriche aggregate e anonime. Nessun dato personale, P.IVA, targa o PDF è raggiungibile da qui."
        right={<button className="btn"><Icy d={IIy.download} size={11} /> Esporta CSV</button>} />

      <div style={{ display: "grid", gridTemplateColumns: "repeat(3,1fr)", gap: 12, marginTop: 16 }}>
        {stats.map(([l,v]) => (
          <div key={l} className="card" style={{ padding: 14 }}>
            <div style={{ fontSize: 11, color: "var(--ink-3)", letterSpacing: 0.8,
                          textTransform: "uppercase", fontWeight: 500 }}>{l}</div>
            <div className="num" style={{ fontSize: 24, fontWeight: 600, marginTop: 4 }}>{v}</div>
          </div>
        ))}
      </div>

      <DivY title="Carico applicativo · ultime 17 ore" />
      <div className="card" style={{ padding: 18 }}>
        <div style={{ display: "flex", alignItems: "flex-end", gap: 6, height: 110 }}>
          {bars.map((h,i) => (
            <div key={i} style={{ flex: 1, height: `${h*1.6}%`,
                                  background: "var(--accent)",
                                  borderRadius: "3px 3px 0 0", opacity: .85 }} />
          ))}
        </div>
        <div style={{ display: "flex", justifyContent: "space-between",
                      fontSize: 10.5, color: "var(--ink-3)", marginTop: 8 }} className="mono">
          <span>00:00</span><span>06:00</span><span>12:00</span><span>17:00</span>
        </div>
      </div>
    </div>
  );
}

// ============================================================================
function AuditSec() {
  return (
    <div style={{ padding: "20px 24px", overflow: "auto", flex: 1 }}>
      <HeadY title="Audit infrastruttura"
        subtitle="Solo eventi di livello sistema: rotazioni chiavi, deploy, abilitazione tenant. Nessun evento di pratica."
        right={<button className="btn"><Icy d={IIy.download} size={11} /> CSV</button>} />
      <div className="card" style={{ marginTop: 16, overflow: "hidden" }}>
        {[
          ["14 mag · 22:11","R. Iezzi","Rotazione chiave PDND voucher (warn 12gg)"],
          ["13 mag · 09:14","Sistema","Deploy v0.5.2 · zero-downtime"],
          ["12 mag · 16:48","R. Iezzi","Abilitato tenant Comune di Avezzano (c_e372)"],
          ["10 mag · 11:02","R. Iezzi","Disabilitata SMTP fallback Mailgun"],
          ["08 mag · 03:01","Sistema","Backup geo-ridondato · OK · 2 datacenter"],
        ].map(([when,who,what],i,arr) => (
          <div key={i} style={{ display: "grid", gridTemplateColumns: "150px 140px 1fr",
                padding: "10px 14px", gap: 10, alignItems: "center", fontSize: 12.5,
                borderBottom: i < arr.length - 1 ? "1px solid var(--line)" : "none" }}>
            <span className="mono" style={{ color: "var(--ink-3)" }}>{when}</span>
            <span style={{ fontWeight: 500 }}>{who}</span>
            <span style={{ color: "var(--ink-2)" }}>{what}</span>
          </div>
        ))}
      </div>
    </div>
  );
}

// ============================================================================
function ReleaseSec() {
  return (
    <div style={{ padding: "20px 24px", overflow: "auto", flex: 1 }}>
      <HeadY title="Release & migrazioni"
        subtitle="Catalogo Developers Italia · EUPL-1.2"
        right={<button className="btn">publiccode.yml</button>} />
      <div className="card" style={{ marginTop: 16, padding: 16 }}>
        <div style={{ fontSize: 13, fontWeight: 600 }}>v0.5.2 · in produzione</div>
        <div style={{ fontSize: 11.5, color: "var(--ink-3)", marginTop: 2 }}>
          M4 · State Machine + Check-in viaggio + Radar FdO · 13 mag 2026
        </div>
      </div>
      <div style={{ marginTop: 12, display: "grid", gridTemplateColumns: "repeat(4,1fr)", gap: 10 }}>
        {[
          ["v0.6.x","M5 · Pagamenti & SEPA","🔜 staging"],
          ["v0.7.x","M6 · Open Data","⏳ pianificato"],
          ["v1.0.0","GA · AINOP+PDND","⏳ pianificato"],
          ["LTS","Laravel 13.6 · PHP 8.4","ok"],
        ].map(([v,l,s]) => (
          <div key={v} className="card" style={{ padding: 12 }}>
            <div className="mono" style={{ fontSize: 11, color: "var(--ink-3)" }}>{v}</div>
            <div style={{ fontSize: 12.5, fontWeight: 500, marginTop: 2 }}>{l}</div>
            <div style={{ fontSize: 11, color: "var(--ink-3)", marginTop: 4 }}>{s}</div>
          </div>
        ))}
      </div>
    </div>
  );
}

// ============================================================================
// Geo dataset · QA mappe e dati geografici importati
// Il system-admin VEDE struttura/freschezza/coverage dei layer; NON vede
// pratiche, percorsi calcolati, P.IVA o targhe.
// ============================================================================
function GeoDatasetSec() {
  const layers = [
    { id: "osm-italia",      name: "OSM · Italia base map",   provider: "OpenStreetMap",  ver: "26.04.30", size: "8,4 GB",   features: "—",        updated: "30 apr · 02:14", freq: "settimanale", status: "ok",   coverage: 100 },
    { id: "ainop-ponti",     name: "AINOP · ponti & viadotti", provider: "MIT · PDND",      ver: "2026-Q2",  size: "184 MB",   features: "62.418",   updated: "12 mag · 04:00", freq: "settimanale", status: "ok",   coverage: 98.4 },
    { id: "ainop-gallerie",  name: "AINOP · gallerie",          provider: "MIT · PDND",      ver: "2026-Q2",  size: "31 MB",    features: "1.842",    updated: "12 mag · 04:00", freq: "settimanale", status: "ok",   coverage: 100 },
    { id: "anas-cantieri",   name: "ANAS · cantieri attivi",     provider: "ANAS open data",ver: "live",      size: "2,1 MB",   features: "287",      updated: "14 mag · 06:00", freq: "ogni 6h",     status: "ok",   coverage: 100 },
    { id: "prov-cantieri",   name: "Provincia PE · cantieri",    provider: "tenant capofila",ver: "live",     size: "612 KB",   features: "94",       updated: "14 mag · 05:00", freq: "ogni 1h",     status: "ok",   coverage: 100 },
    { id: "comuni-zone",     name: "Comuni · ZTL e divieti",     provider: "32 comuni",     ver: "—",         size: "4,2 MB",   features: "1.140",    updated: "11 mag · 17:32", freq: "manuale",     status: "warn", coverage: 71.8 },
    { id: "regolamenti",     name: "Regolamenti DM 2017",     provider: "MIT (immutabile)", ver: "DM 2017", size: "—",         features: "regole",  updated: "—",            freq: "—",          status: "ok",   coverage: 100 },
    { id: "elev-srtm",       name: "SRTM · elevazione",          provider: "NASA · mirror",   ver: "v3",       size: "1,9 GB",   features: "raster",   updated: "fissa",        freq: "—",          status: "ok",   coverage: 100 },
    { id: "comuni-istat",    name: "ISTAT · confini comunali",   provider: "ISTAT",          ver: "2025-01-01", size: "62 MB",   features: "7.901",    updated: "01 gen",       freq: "annuale",     status: "stale",coverage: 100 },
  ];
  const [selected, setSelected] = React.useState("ainop-ponti");
  const [layerToggles, setLayerToggles] = React.useState({
    osm: true, ainop: true, cantieri: true, ztl: false, gaps: true, tiles: false,
  });
  const sel = layers.find(l => l.id === selected);
  const toneFor = (s) => s === "ok" ? "success" : s === "warn" ? "amber" : s === "stale" ? "default" : "danger";
  const labelFor = (s) => s === "ok" ? "fresco" : s === "warn" ? "incompleto" : s === "stale" ? "stantio" : "errore";

  return (
    <div style={{ padding: "20px 24px", overflow: "auto", flex: 1 }}>
      <HeadY title="Geo dataset · controllo qualità"
             subtitle="Struttura, freschezza e copertura dei layer geografici importati. Nessuna pratica, P.IVA o targa è esposta su questa pagina."
             right={<><button className="btn"><Icy d={IIy.refresh} size={11} /> Risincronizza tutti</button>
                      <button className="btn"><Icy d={IIy.download} size={11} /> Manifest JSON</button></>} />

      {/* KPI banner */}
      <div style={{ display: "grid", gridTemplateColumns: "repeat(4,1fr)", gap: 12, marginTop: 16 }}>
        {[
          ["Layer attivi","9","2 con avvisi"],
          ["Storage geo","10,7 GB","PostGIS · tile cache"],
          ["Ult. sync OK","12 mag · 04:00","AINOP via PDND"],
          ["Tile build queue","0","ult. build 11 mag", "success"],
        ].map(([l,v,s,t]) => (
          <div key={l} className="card" style={{ padding: 14 }}>
            <div style={{ fontSize: 11, color: "var(--ink-3)", letterSpacing: 0.8,
                          textTransform: "uppercase", fontWeight: 500 }}>{l}</div>
            <div className="num" style={{ fontSize: 24, fontWeight: 600, marginTop: 4,
                  color: t === "success" ? "var(--success)" : "var(--ink)" }}>{v}</div>
            <div style={{ fontSize: 11.5, color: "var(--ink-3)" }}>{s}</div>
          </div>
        ))}
      </div>

      {/* Map + sidebar */}
      <DivY title="Mappa di controllo" />
      <div className="card" style={{ padding: 0, overflow: "hidden" }}>
        <div style={{ display: "grid", gridTemplateColumns: "1fr 320px",
                      borderBottom: "1px solid var(--line)" }}>
          {/* Map area */}
          <div style={{ position: "relative", minHeight: 480, background: "var(--surface-2)" }}>
            <window.PNTEMap height={480} showRoute={false} showRoadworks={false}
              showBridges={false} highlightIntersected={false} variant="minimal" />

            {/* Overlay strip — controlli layer (sopra la mappa) */}
            <div style={{ position: "absolute", top: 12, left: 12, right: 12,
                          display: "flex", gap: 6, flexWrap: "wrap", pointerEvents: "auto" }}>
              {[
                ["osm","OSM base"],
                ["ainop","AINOP ponti+gallerie"],
                ["cantieri","Cantieri ANAS+Prov"],
                ["ztl","ZTL comuni"],
                ["gaps","Buchi copertura"],
                ["tiles","Griglia tile cache"],
              ].map(([k,l]) => {
                const on = layerToggles[k];
                return (
                  <button key={k}
                    onClick={() => setLayerToggles(t => ({...t, [k]: !t[k]}))}
                    style={{
                      padding: "5px 10px", borderRadius: 999, fontSize: 11, fontWeight: 500,
                      border: "1px solid " + (on ? "var(--ink)" : "var(--line-2)"),
                      background: on ? "var(--ink)" : "var(--surface)",
                      color: on ? "var(--bg)" : "var(--ink-2)",
                      cursor: "pointer", fontFamily: "inherit",
                      boxShadow: "0 1px 2px rgba(0,0,0,0.04)",
                    }}>
                    {l}
                  </button>
                );
              })}
            </div>

            {/* Overlay BL — coordinate cursor + zoom (decorative, mostra che è esplorabile) */}
            <div style={{ position: "absolute", bottom: 12, left: 12,
                          display: "flex", gap: 6, alignItems: "center" }}>
              <div className="mono" style={{ padding: "4px 8px", borderRadius: 6,
                    background: "var(--surface)", border: "1px solid var(--line)",
                    fontSize: 11, color: "var(--ink-3)" }}>
                42.4647° N, 14.2156° E · z 12
              </div>
            </div>

            {/* Overlay BR — legend */}
            <div style={{ position: "absolute", bottom: 12, right: 12,
                          padding: "10px 12px", background: "var(--surface)",
                          border: "1px solid var(--line)", borderRadius: 8,
                          fontSize: 11, lineHeight: 1.6, minWidth: 180 }}>
              <div style={{ fontSize: 10, color: "var(--ink-3)", letterSpacing: 1,
                            textTransform: "uppercase", fontWeight: 600, marginBottom: 4 }}>Legenda</div>
              <div style={{ display: "flex", alignItems: "center", gap: 6 }}>
                <span style={{ width: 10, height: 10, borderRadius: 2, background: "var(--success)" }} />
                Layer fresco (&lt; 24h)
              </div>
              <div style={{ display: "flex", alignItems: "center", gap: 6 }}>
                <span style={{ width: 10, height: 10, borderRadius: 2, background: "var(--accent)" }} />
                Da risincronizzare
              </div>
              <div style={{ display: "flex", alignItems: "center", gap: 6 }}>
                <span style={{ width: 10, height: 10, borderRadius: 2, background: "var(--ink-3)",
                               opacity: 0.4 }} />
                Buco di copertura
              </div>
            </div>
          </div>

          {/* Sidebar */}
          <div style={{ borderLeft: "1px solid var(--line)", padding: 16,
                        background: "var(--surface)", display: "flex", flexDirection: "column", gap: 14,
                        maxHeight: 480, overflowY: "auto" }}>
            <div>
              <div style={{ fontSize: 10.5, color: "var(--ink-3)", letterSpacing: 1.2,
                            textTransform: "uppercase", fontWeight: 600 }}>Layer selezionato</div>
              <div style={{ fontSize: 14, fontWeight: 600, marginTop: 4 }}>{sel.name}</div>
              <div style={{ fontSize: 11.5, color: "var(--ink-3)", marginTop: 2 }}>{sel.provider}</div>
            </div>

            <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 8 }}>
              {[
                ["Versione", sel.ver],
                ["Dimensione", sel.size],
                ["Feature count", sel.features],
                ["Frequenza", sel.freq],
              ].map(([l,v]) => (
                <div key={l} style={{ padding: "8px 10px", border: "1px solid var(--line)",
                                       borderRadius: 7, background: "var(--surface-2)" }}>
                  <div style={{ fontSize: 9.5, color: "var(--ink-3)", letterSpacing: 1,
                                textTransform: "uppercase", fontWeight: 500 }}>{l}</div>
                  <div className="mono" style={{ fontSize: 12, fontWeight: 600, marginTop: 2 }}>{v}</div>
                </div>
              ))}
            </div>

            <div>
              <div style={{ fontSize: 10.5, color: "var(--ink-3)", letterSpacing: 1.2,
                            textTransform: "uppercase", fontWeight: 600, marginBottom: 6 }}>
                Copertura territorio
              </div>
              <div style={{ height: 10, borderRadius: 999, background: "var(--surface-2)",
                            overflow: "hidden", border: "1px solid var(--line)" }}>
                <div style={{ height: "100%", width: sel.coverage + "%",
                              background: sel.coverage >= 95 ? "var(--success)"
                                          : sel.coverage >= 80 ? "var(--accent)" : "var(--danger)" }} />
              </div>
              <div style={{ fontSize: 11, color: "var(--ink-3)", marginTop: 4 }}>
                {sel.coverage}% area provinciale coperta
              </div>
            </div>

            <div>
              <div style={{ fontSize: 10.5, color: "var(--ink-3)", letterSpacing: 1.2,
                            textTransform: "uppercase", fontWeight: 600, marginBottom: 6 }}>
                Stato sync
              </div>
              <div style={{ display: "flex", alignItems: "center", gap: 8 }}>
                <Cy tone={toneFor(sel.status)}>{labelFor(sel.status)}</Cy>
                <span className="mono" style={{ fontSize: 11, color: "var(--ink-3)" }}>
                  ult. {sel.updated}
                </span>
              </div>
            </div>

            <div style={{ display: "flex", gap: 6, flexWrap: "wrap" }}>
              <button className="btn btn-sm btn-primary"><Icy d={IIy.refresh} size={10} /> Risincronizza</button>
              <button className="btn btn-sm">Apri sample (.geojson)</button>
              <button className="btn btn-sm btn-ghost">Disabilita layer</button>
            </div>

            <div style={{ padding: 10, borderRadius: 7, background: "var(--surface-2)",
                          border: "1px dashed var(--line-2)", fontSize: 11, color: "var(--ink-3)",
                          lineHeight: 1.5 }}>
              <strong style={{ color: "var(--ink-2)", fontWeight: 600 }}>Privacy by design.</strong>
              {" "}Il sample mostra solo geometria e attributi tecnici; nessun campo applicativo
              (P.IVA, targa, pratica) è incluso.
            </div>
          </div>
        </div>
      </div>

      {/* Tabella dataset */}
      <DivY title="Tutti i dataset" />
      <div className="card" style={{ overflow: "hidden" }}>
        <div style={{ display: "grid",
                      gridTemplateColumns: "1.6fr 1fr 110px 100px 110px 130px 100px 70px",
                      padding: "10px 14px", fontSize: 10.5, color: "var(--ink-3)",
                      letterSpacing: 0.8, textTransform: "uppercase", fontWeight: 500,
                      background: "var(--surface-2)", borderBottom: "1px solid var(--line)" }}>
          <div>Layer</div><div>Provider</div><div>Versione</div><div>Feature</div>
          <div>Copertura</div><div>Ult. sync</div><div>Stato</div><div></div>
        </div>
        {layers.map((l,i) => {
          const on = l.id === selected;
          return (
            <div key={l.id} className="row-hover"
              onClick={() => setSelected(l.id)}
              style={{ display: "grid",
                gridTemplateColumns: "1.6fr 1fr 110px 100px 110px 130px 100px 70px",
                padding: "12px 14px", alignItems: "center", fontSize: 12.5,
                cursor: "pointer",
                background: on ? "var(--accent-bg)" : "transparent",
                borderBottom: i < layers.length - 1 ? "1px solid var(--line)" : "none" }}>
              <div>
                <div style={{ fontWeight: on ? 600 : 500 }}>{l.name}</div>
                <div className="mono" style={{ fontSize: 10.5, color: "var(--ink-3)" }}>{l.id}</div>
              </div>
              <div style={{ fontSize: 11.5, color: "var(--ink-3)" }}>{l.provider}</div>
              <div className="mono" style={{ fontSize: 11.5 }}>{l.ver}</div>
              <div className="num" style={{ fontSize: 11.5, color: "var(--ink-3)" }}>{l.features}</div>
              <div style={{ display: "flex", alignItems: "center", gap: 6 }}>
                <div style={{ flex: 1, height: 5, borderRadius: 999, background: "var(--surface-2)",
                              border: "1px solid var(--line)", overflow: "hidden" }}>
                  <div style={{ height: "100%", width: l.coverage + "%",
                                background: l.coverage >= 95 ? "var(--success)"
                                            : l.coverage >= 80 ? "var(--accent)" : "var(--danger)" }} />
                </div>
                <span className="mono" style={{ fontSize: 10.5, color: "var(--ink-3)", minWidth: 30 }}>
                  {l.coverage}%
                </span>
              </div>
              <div className="mono" style={{ fontSize: 11, color: "var(--ink-3)" }}>{l.updated}</div>
              <Cy tone={toneFor(l.status)}>{labelFor(l.status)}</Cy>
              <div style={{ display: "flex", gap: 4, justifyContent: "flex-end" }}>
                <button className="btn btn-sm" onClick={(e) => { e.stopPropagation(); }}>
                  <Icy d={IIy.refresh} size={10} />
                </button>
              </div>
            </div>
          );
        })}
      </div>

      {/* Issues / drift */}
      <DivY title="Avvisi attivi" />
      <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 12 }}>
        <div className="card" style={{ padding: 14, borderColor: "color-mix(in oklch, var(--accent), white 60%)" }}>
          <div style={{ display: "flex", alignItems: "center", gap: 8 }}>
            <Icy d={IIy.alert} size={13} />
            <strong style={{ fontSize: 12.5 }}>Comuni · ZTL incompleta · 9 comuni mancanti</strong>
            <Cy tone="amber">incompleto</Cy>
          </div>
          <div style={{ fontSize: 11.5, color: "var(--ink-3)", marginTop: 6, lineHeight: 1.5 }}>
            Bussi sul T., Caramanico, Lettomanoppello e altri 6 non hanno fornito file ZTL aggiornato.
            Questo non blocca il routing, ma può portare a NO non automatici.
          </div>
          <button className="btn btn-sm" style={{ marginTop: 10 }}>Notifica i tenant</button>
        </div>
        <div className="card" style={{ padding: 14 }}>
          <div style={{ display: "flex", alignItems: "center", gap: 8 }}>
            <Icy d={IIy.clock} size={13} />
            <strong style={{ fontSize: 12.5 }}>ISTAT confini comunali · ult. update 1 gen</strong>
            <Cy>stantio</Cy>
          </div>
          <div style={{ fontSize: 11.5, color: "var(--ink-3)", marginTop: 6, lineHeight: 1.5 }}>
            Il dataset è annuale. Nessuna azione richiesta finché ISTAT non rilascia 2026-Q3.
          </div>
        </div>
      </div>
    </div>
  );
}

window.PNTEScreens = { ...(window.PNTEScreens || {}), SystemAdminScreen };

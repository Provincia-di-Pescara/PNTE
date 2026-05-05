/* global React, GTE */
const { Chip: Cs, Avatar: Avs, Icon: Ics, I: IIs } = window.GTE;

// ===========================================================================
// Operator (super-admin Provincia) — Impostazioni
// Layout: left rail of categories · main panel · right "audit / context" pane
// ===========================================================================
function OperatorSettingsScreen() {
  const [section, setSection] = React.useState("integrazioni");

  const groups = [
    {
      title: "Ente",
      items: [
        { key: "ente",      icon: IIs.flag,   label: "Profilo ente",        sub: "Provincia di Pescara · IPA c_g482" },
        { key: "tariffe",   icon: IIs.euro,   label: "Tariffario",          sub: "v.2026.04 · pubblicato" },
        { key: "territorio",icon: IIs.map,    label: "Territorio & strade", sub: "812 strade · 248 ponti" },
      ],
    },
    {
      title: "Persone",
      items: [
        { key: "utenti",    icon: IIs.user,   label: "Utenti & ruoli",      sub: "23 attivi · 4 super-admin", badge: "2" },
        { key: "deleghe",   icon: IIs.share,  label: "Deleghe & SPID",      sub: "Cittadini autorizzati" },
        { key: "enti",      icon: IIs.layers, label: "Enti terzi federati", sub: "47 comuni · 3 gestori" },
      ],
    },
    {
      title: "Workflow",
      items: [
        { key: "stati",     icon: IIs.refresh,label: "Stati pratica",       sub: "Macchina a stati" },
        { key: "sla",       icon: IIs.clock,  label: "SLA & promemoria",    sub: "30 gg standard · 7 gg urgenti" },
        { key: "modelli",   icon: IIs.doc,    label: "Modelli atti",        sub: "8 template attivi" },
      ],
    },
    {
      title: "Sistema",
      items: [
        { key: "integrazioni", icon: IIs.bolt, label: "Integrazioni",        sub: "PagoPA · AINOP · PDND · IPA", badge: "•" },
        { key: "notifiche", icon: IIs.bell,   label: "Notifiche & PEC",     sub: "PEC istituzionale · IO app" },
        { key: "sicurezza", icon: IIs.qr,     label: "Sicurezza & audit",   sub: "MFA obbligatoria · 90gg" },
        { key: "branding",  icon: IIs.pin,    label: "Identità visiva",     sub: "Logo · palette · header" },
      ],
    },
  ];

  return (
    <div style={{ display: "flex", height: "100%", overflow: "hidden" }}>

      {/* ============== LEFT: settings nav rail =================== */}
      <aside style={{
        width: 264, flexShrink: 0,
        borderRight: "1px solid var(--line)",
        background: "var(--surface)",
        display: "flex", flexDirection: "column", overflow: "hidden",
      }}>
        <div style={{ padding: "18px 20px 14px", borderBottom: "1px solid var(--line)" }}>
          <div style={{ fontSize: 10.5, letterSpacing: 1.4, color: "var(--ink-3)",
                        textTransform: "uppercase", fontWeight: 500 }}>Configurazione</div>
          <h1 style={{ margin: "4px 0 0", fontSize: 18, fontWeight: 600,
                       letterSpacing: "-0.01em" }}>Impostazioni</h1>
          <div style={{ fontSize: 11.5, color: "var(--ink-3)", marginTop: 2 }}>
            Modifiche tracciate nel registro audit.
          </div>
        </div>

        <div style={{ flex: 1, overflow: "auto", padding: "10px 8px 20px" }}>
          {groups.map(g => (
            <div key={g.title} style={{ marginBottom: 12 }}>
              <div style={{ fontSize: 10, color: "var(--ink-3)", letterSpacing: 1.2,
                            textTransform: "uppercase", fontWeight: 600,
                            padding: "8px 12px 4px" }}>{g.title}</div>
              {g.items.map(it => {
                const on = it.key === section;
                return (
                  <button key={it.key} onClick={() => setSection(it.key)}
                          className="row-hover"
                          style={{
                            width: "100%", textAlign: "left", border: "none",
                            background: on ? "var(--accent-bg)" : "transparent",
                            color: on ? "var(--accent-ink)" : "var(--ink)",
                            display: "flex", alignItems: "center", gap: 10,
                            padding: "8px 12px", borderRadius: 7, cursor: "pointer",
                            fontFamily: "inherit",
                          }}>
                    <span style={{
                      width: 28, height: 28, borderRadius: 7, flexShrink: 0,
                      background: on ? "var(--surface)" : "var(--surface-2)",
                      border: "1px solid var(--line)",
                      display: "flex", alignItems: "center", justifyContent: "center",
                      color: on ? "var(--accent-ink)" : "var(--ink-2)",
                    }}>
                      <Ics d={it.icon} size={14} />
                    </span>
                    <span style={{ flex: 1, minWidth: 0 }}>
                      <span style={{ display: "block", fontSize: 12.5,
                                     fontWeight: on ? 600 : 500, lineHeight: 1.2 }}>
                        {it.label}
                      </span>
                      <span style={{ display: "block", fontSize: 10.5,
                                     color: "var(--ink-3)", lineHeight: 1.3,
                                     marginTop: 1, whiteSpace: "nowrap",
                                     overflow: "hidden", textOverflow: "ellipsis" }}>
                        {it.sub}
                      </span>
                    </span>
                    {it.badge && (
                      <span style={{
                        flexShrink: 0,
                        minWidth: 18, height: 18, padding: "0 5px",
                        borderRadius: 999,
                        background: it.badge === "•" ? "var(--accent)" : "var(--ink)",
                        color: "var(--bg)",
                        fontSize: 10.5, fontWeight: 600,
                        display: "flex", alignItems: "center", justifyContent: "center",
                      }}>{it.badge === "•" ? "" : it.badge}</span>
                    )}
                  </button>
                );
              })}
            </div>
          ))}
        </div>

        <div style={{ borderTop: "1px solid var(--line)",
                      padding: "10px 14px", display: "flex", alignItems: "center", gap: 10 }}>
          <Avs name="Marta Cipriani" />
          <div style={{ flex: 1, minWidth: 0 }}>
            <div style={{ fontSize: 12, fontWeight: 600 }}>Marta Cipriani</div>
            <div style={{ fontSize: 10.5, color: "var(--ink-3)" }}>Super-admin · Provincia</div>
          </div>
        </div>
      </aside>

      {/* ============== CENTER: section panel ====================== */}
      <div style={{ flex: 1, display: "flex", flexDirection: "column",
                    minWidth: 0, overflow: "hidden" }}>
        {section === "integrazioni" && <IntegrationsSection />}
        {section === "ente" && <EnteProfileSection />}
        {section === "tariffe" && <TariffeSection />}
        {section === "utenti" && <UsersSection />}
        {section === "stati" && <StateMachineSection />}
        {section === "sicurezza" && <SecuritySection />}
        {section === "sla" && <PlaceholderSection title="SLA & promemoria"
            subtitle="Configura tempi standard e soglie di alert."
            chips={["30 gg ordinario", "7 gg urgente", "48h sollecito"]} />}
        {section === "modelli" && <PlaceholderSection title="Modelli atti"
            subtitle="Template di autorizzazione, decreto, diniego."
            chips={["Autorizzazione singola", "Autorizzazione periodica", "Decreto di diniego"]} />}
        {section === "territorio" && <PlaceholderSection title="Territorio & strade"
            subtitle="Catasto strade provinciale e ponti AINOP."
            chips={["812 strade", "248 ponti", "94 cantieri attivi"]} />}
        {section === "deleghe" && <PlaceholderSection title="Deleghe & SPID"
            subtitle="Gestione deleghe cittadino → azienda via SPID."
            chips={["318 deleghe attive", "12 in attesa"]} />}
        {section === "enti" && <PlaceholderSection title="Enti terzi federati"
            subtitle="Comuni, gestori e concessionari connessi al sistema."
            chips={["47 comuni", "3 gestori", "ANAS · CAS · Strada dei Parchi"]} />}
        {section === "notifiche" && <PlaceholderSection title="Notifiche & PEC"
            subtitle="Canali di comunicazione istituzionale."
            chips={["PEC", "AppIO", "Email", "SMS"]} />}
        {section === "branding" && <PlaceholderSection title="Identità visiva"
            subtitle="Logo, palette e intestazioni atti."
            chips={["Logo Provincia", "Stemma", "Header PEC"]} />}
      </div>
    </div>
  );
}

// ===========================================================================
// SECTION: Integrazioni (default visible)
// ===========================================================================
function IntegrationsSection() {
  const integrations = [
    {
      key: "pagopa",
      name: "PagoPA",
      org: "AgID · pagoPA S.p.A.",
      desc: "Avvisi pagamento, riconciliazione automatica, IUV.",
      status: "ok",
      lastSync: "appena adesso",
      env: "Produzione",
      meta: [
        ["Codice fiscale ente", "p_pe_001"],
        ["Codice IBAN tesoreria", "IT 60 X 05428 04010 ··· 9237"],
        ["IUV emessi (mese)",   "342"],
        ["Riconciliazioni KO",  "1"],
      ],
    },
    {
      key: "ainop",
      name: "AINOP",
      org: "MIT · Archivio nazionale opere pubbliche",
      desc: "Schede tecniche ponti, gallerie, viadotti per pre-verifica strutturale.",
      status: "ok",
      lastSync: "12 min fa",
      env: "Produzione",
      meta: [
        ["Ponti monitorati",     "248"],
        ["Schede aggiornate",    "242"],
        ["Modello firma",        "X.509 · CIE"],
        ["Endpoint",             "ainop.mit.gov.it/v3"],
      ],
    },
    {
      key: "pdnd",
      name: "PDND",
      org: "Dipartimento Trasformazione Digitale",
      desc: "Piattaforma Digitale Nazionale Dati. E-service di interscambio enti.",
      status: "warn",
      lastSync: "2 ore fa",
      env: "Produzione",
      warning: "1 e-service \"anpr-residenze\" in scadenza fra 14 giorni — rinnovare voucher.",
      meta: [
        ["E-service consumati",  "12"],
        ["E-service erogati",    "3"],
        ["Voucher attivi",       "9 / 10"],
        ["Tracciato",            "EBSI v2"],
      ],
    },
    {
      key: "ipa",
      name: "IPA",
      org: "AgID · Indice Pubblica Amministrazione",
      desc: "Allineamento anagrafica enti destinatari di nulla osta.",
      status: "ok",
      lastSync: "stamane 06:00",
      env: "Produzione",
      meta: [
        ["Enti sincronizzati", "8.124"],
        ["Cron",               "ogni notte 03:00 CET"],
      ],
    },
    {
      key: "siope",
      name: "SIOPE+",
      org: "MEF · Banca d'Italia",
      desc: "Trasmissione ordinativi di incasso e pagamento.",
      status: "off",
      lastSync: "non collegato",
      env: "—",
      cta: "Configura",
      meta: [
        ["Stato",            "Configurazione richiesta"],
        ["Tracciato",        "OPI 1.9"],
      ],
    },
    {
      key: "osrm",
      name: "OSRM (routing)",
      org: "Self-hosted · Linux/Aruba CSP",
      desc: "Calcolo percorsi e snap-to-road per il WebGIS.",
      status: "ok",
      lastSync: "real-time",
      env: "Produzione",
      meta: [
        ["Tile OSM",          "Italia · 2026.03"],
        ["Veicolo profile",   "truck-heavy.lua"],
        ["Latenza p95",       "184 ms"],
      ],
    },
  ];

  return (
    <div style={{ padding: "20px 24px", overflow: "auto", flex: 1 }}>
      <SectionHeader
        title="Integrazioni"
        subtitle="Sistemi nazionali e regionali collegati al gestionale. Le credenziali sono custodite in HSM."
        right={<>
          <button className="btn"><Ics d={IIs.refresh} size={12} /> Sincronizza tutto</button>
          <button className="btn btn-primary"><Ics d={IIs.plus} size={12} /> Aggiungi connettore</button>
        </>}
      />

      {/* status overview */}
      <div style={{ display: "grid", gridTemplateColumns: "repeat(4, 1fr)", gap: 12, marginTop: 16 }}>
        {[
          { l: "Connettori attivi", v: "5 / 6" },
          { l: "Chiamate (24h)",    v: "12.408" },
          { l: "Errori (24h)",      v: "3", tone: "amber" },
          { l: "SLA disponibilità", v: "99,94%", tone: "success" },
        ].map(s => (
          <div key={s.l} className="card" style={{ padding: 14 }}>
            <div style={{ fontSize: 11, color: "var(--ink-3)", letterSpacing: 0.8,
                          textTransform: "uppercase", fontWeight: 500 }}>{s.l}</div>
            <div className="num" style={{
              fontSize: 22, fontWeight: 600, marginTop: 4,
              color: s.tone === "amber" ? "var(--accent-ink)"
                   : s.tone === "success" ? "var(--success)" : "var(--ink)",
            }}>{s.v}</div>
          </div>
        ))}
      </div>

      {/* connectors */}
      <div style={{ marginTop: 18, display: "flex", flexDirection: "column", gap: 10 }}>
        {integrations.map(it => <IntegrationCard key={it.key} it={it} />)}
      </div>

      {/* changelog */}
      <SectionDivider title="Registro modifiche · ultime 5" />
      <div className="card" style={{ overflow: "hidden" }}>
        {[
          ["14 mag · 14:32", "Marta Cipriani", "ha aggiornato endpoint AINOP a v3.4"],
          ["14 mag · 11:08", "Sistema",        "rinnovo automatico voucher PDND e-service \"infocamere-imprese\""],
          ["13 mag · 17:51", "Davide Ranieri", "rotazione chiave HSM per PagoPA"],
          ["12 mag · 09:20", "Marta Cipriani", "abilitata MFA obbligatoria per super-admin"],
          ["11 mag · 22:14", "Sistema",        "tentativo di accesso bloccato (IP fuori CIDR consentito)"],
        ].map(([when, who, what], i, arr) => (
          <div key={i} style={{
            display: "grid", gridTemplateColumns: "140px 160px 1fr 24px",
            padding: "10px 14px", gap: 12,
            borderBottom: i < arr.length - 1 ? "1px solid var(--line)" : "none",
            alignItems: "center", fontSize: 12.5,
          }}>
            <span className="mono" style={{ color: "var(--ink-3)" }}>{when}</span>
            <span style={{ fontWeight: 500 }}>{who}</span>
            <span style={{ color: "var(--ink-2)" }}>{what}</span>
            <button className="btn btn-ghost btn-sm" style={{ width: 22, padding: 0 }}>
              <Ics d={IIs.more} size={12} />
            </button>
          </div>
        ))}
      </div>
    </div>
  );
}

function IntegrationCard({ it }) {
  const tones = {
    ok:   { tone: "success", label: "Operativo" },
    warn: { tone: "amber",   label: "Attenzione" },
    off:  { tone: "default", label: "Non configurato" },
    err:  { tone: "danger",  label: "Errore" },
  };
  const s = tones[it.status];
  return (
    <div className="card" style={{ padding: 16, display: "flex",
                                   flexDirection: "column", gap: 12 }}>
      <div style={{ display: "flex", alignItems: "flex-start", gap: 14 }}>
        <div style={{
          width: 44, height: 44, borderRadius: 10,
          background: it.status === "off" ? "var(--surface-2)" : "var(--accent-bg)",
          color: it.status === "off" ? "var(--ink-3)" : "var(--accent-ink)",
          border: "1px solid var(--line)",
          display: "flex", alignItems: "center", justifyContent: "center",
          fontSize: 11, fontWeight: 700, letterSpacing: 0.4,
        }}>
          {it.name.split(/[\s+]/)[0].slice(0, 4).toUpperCase()}
        </div>
        <div style={{ flex: 1, minWidth: 0 }}>
          <div style={{ display: "flex", alignItems: "center", gap: 8 }}>
            <h3 style={{ margin: 0, fontSize: 14.5, fontWeight: 600 }}>{it.name}</h3>
            <Cs tone={s.tone}>{s.label}</Cs>
            {it.env !== "—" && (
              <span className="chip" style={{ fontSize: 10.5 }}>{it.env}</span>
            )}
          </div>
          <div style={{ fontSize: 11.5, color: "var(--ink-3)", marginTop: 2 }}>
            {it.org}
          </div>
          <div style={{ fontSize: 12.5, color: "var(--ink-2)", marginTop: 6, lineHeight: 1.45 }}>
            {it.desc}
          </div>
        </div>
        <div style={{ display: "flex", flexDirection: "column", alignItems: "flex-end", gap: 6 }}>
          <span style={{ fontSize: 11, color: "var(--ink-3)" }}>
            ult. sync · {it.lastSync}
          </span>
          <div style={{ display: "flex", gap: 6 }}>
            {it.cta && <button className="btn btn-sm btn-primary">{it.cta}</button>}
            {!it.cta && <button className="btn btn-sm">Configura</button>}
            <button className="btn btn-sm btn-ghost" style={{ width: 26, padding: 0 }}>
              <Ics d={IIs.more} size={12} />
            </button>
          </div>
        </div>
      </div>

      {it.warning && (
        <div style={{
          display: "flex", alignItems: "flex-start", gap: 8,
          padding: "8px 12px", borderRadius: 8,
          background: "var(--accent-bg)", color: "var(--accent-ink)",
          border: "1px solid color-mix(in oklch, var(--accent), white 60%)",
          fontSize: 12,
        }}>
          <Ics d={IIs.alert} size={12} />
          <div style={{ flex: 1 }}>{it.warning}</div>
          <button className="btn btn-sm" style={{ background: "transparent", border: "none",
            color: "var(--accent-ink)", padding: 0, fontWeight: 600 }}>Rinnova ora</button>
        </div>
      )}

      <div style={{
        display: "grid", gridTemplateColumns: "repeat(4, 1fr)", gap: 0,
        border: "1px solid var(--line)", borderRadius: 8, overflow: "hidden",
      }}>
        {it.meta.map(([l, v], i) => (
          <div key={l} style={{
            padding: "8px 12px",
            borderRight: i < it.meta.length - 1 ? "1px solid var(--line)" : "none",
            background: i % 2 === 0 ? "var(--surface)" : "var(--surface-2)",
          }}>
            <div style={{ fontSize: 10, color: "var(--ink-3)", letterSpacing: 1,
                          textTransform: "uppercase", fontWeight: 500 }}>{l}</div>
            <div className="mono" style={{ fontSize: 12, fontWeight: 500, marginTop: 2,
                                            whiteSpace: "nowrap", overflow: "hidden",
                                            textOverflow: "ellipsis" }}>{v}</div>
          </div>
        ))}
      </div>
    </div>
  );
}

// ===========================================================================
// SECTION: Profilo ente
// ===========================================================================
function EnteProfileSection() {
  return (
    <div style={{ padding: "20px 24px", overflow: "auto", flex: 1 }}>
      <SectionHeader title="Profilo ente"
        subtitle="Dati istituzionali della Provincia di Pescara. Sincronizzati da IPA ogni 24h."
        right={<button className="btn btn-primary">Salva modifiche</button>} />

      <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 14, marginTop: 16 }}>
        <FieldGroup title="Anagrafica">
          <Field label="Denominazione" value="Provincia di Pescara" mono={false} locked />
          <Field label="Codice IPA" value="c_g482" locked />
          <Field label="Codice fiscale" value="80003010688" locked />
          <Field label="P. IVA" value="80003010688" />
          <Field label="Cod. Catasto" value="G482" />
        </FieldGroup>

        <FieldGroup title="Contatti & PEC">
          <Field label="PEC istituzionale" value="provincia.pescara@legalmail.it" />
          <Field label="Email URP" value="urp@provincia.pe.it" />
          <Field label="Telefono" value="+39 085 3724 1" />
          <Field label="PEC trasporti ecc." value="trasportiecc.pe@legalmail.it" />
          <Field label="Sito web" value="https://www.provincia.pe.it" />
        </FieldGroup>
      </div>

      <SectionDivider title="Sede legale" />
      <FieldGroup>
        <div style={{ display: "grid", gridTemplateColumns: "2fr 1fr 1fr 1fr", gap: 10 }}>
          <Field label="Indirizzo" value="Piazza Italia 30" />
          <Field label="CAP" value="65121" />
          <Field label="Comune" value="Pescara" />
          <Field label="Prov." value="PE" />
        </div>
      </FieldGroup>

      <SectionDivider title="Responsabile del procedimento" />
      <div style={{ display: "flex", alignItems: "center", gap: 12,
                    padding: "12px 14px", border: "1px solid var(--line)",
                    borderRadius: 10, background: "var(--surface)" }}>
        <Avs name="Davide Ranieri" tone="info" />
        <div style={{ flex: 1 }}>
          <div style={{ fontSize: 13, fontWeight: 600 }}>Ing. Davide Ranieri</div>
          <div style={{ fontSize: 11.5, color: "var(--ink-3)" }}>
            Dirigente · Settore Viabilità · davide.ranieri@provincia.pe.it
          </div>
        </div>
        <button className="btn btn-sm">Cambia</button>
      </div>
    </div>
  );
}

// ===========================================================================
// SECTION: Tariffario
// ===========================================================================
function TariffeSection() {
  const tariffe = [
    { code: "T-01", desc: "Indennizzo usura · veicolo singolo eccezionale", unit: "€/km", base: "1,80", coeff: "× cat.veicolo" },
    { code: "T-02", desc: "Indennizzo usura · convoglio (motrice + rimorchio)", unit: "€/km", base: "4,89", coeff: "× cat.veicolo · cat.strada" },
    { code: "T-03", desc: "Diritti istruttoria · domanda singola",          unit: "€",    base: "62,00",  coeff: "fissa" },
    { code: "T-04", desc: "Diritti istruttoria · domanda periodica",         unit: "€",    base: "248,00", coeff: "validità 12 mesi" },
    { code: "T-05", desc: "Diritti istruttoria · variazione",                unit: "€",    base: "31,00",  coeff: "fissa" },
    { code: "T-06", desc: "Maggiorazione · scorta tecnica obbligatoria",     unit: "€",    base: "120,00", coeff: "per uscita" },
    { code: "T-07", desc: "Maggiorazione · transito notturno (22:00–06:00)", unit: "%",    base: "+ 30%", coeff: "su T-01/T-02" },
  ];

  return (
    <div style={{ padding: "20px 24px", overflow: "auto", flex: 1 }}>
      <SectionHeader title="Tariffario"
        subtitle={<>
          Tariffe applicate alle pratiche di trasporto eccezionale.{" "}
          <strong>Versione 2026.04</strong> · pubblicata il 12 mag 2026 · in vigore dal 1° giu 2026.
        </>}
        right={<>
          <button className="btn"><Ics d={IIs.download} size={11} /> Esporta PDF</button>
          <button className="btn btn-primary"><Ics d={IIs.plus} size={11} /> Nuova versione</button>
        </>}
      />

      <div className="card" style={{ marginTop: 16, overflow: "hidden" }}>
        <div style={{
          display: "grid", gridTemplateColumns: "70px 1fr 88px 110px 1fr 70px",
          padding: "10px 14px", fontSize: 10.5, color: "var(--ink-3)",
          letterSpacing: 0.8, textTransform: "uppercase", fontWeight: 500,
          background: "var(--surface-2)", borderBottom: "1px solid var(--line)",
        }}>
          <div>Codice</div>
          <div>Descrizione</div>
          <div>Unità</div>
          <div>Importo base</div>
          <div>Coefficienti</div>
          <div></div>
        </div>
        {tariffe.map((t, i) => (
          <div key={t.code} className="row-hover" style={{
            display: "grid", gridTemplateColumns: "70px 1fr 88px 110px 1fr 70px",
            padding: "12px 14px", alignItems: "center", fontSize: 12.5,
            borderBottom: i < tariffe.length - 1 ? "1px solid var(--line)" : "none",
          }}>
            <div className="mono" style={{ color: "var(--ink-3)" }}>{t.code}</div>
            <div>{t.desc}</div>
            <div className="mono" style={{ color: "var(--ink-3)" }}>{t.unit}</div>
            <div className="num" style={{ fontWeight: 600 }}>{t.base}</div>
            <div style={{ color: "var(--ink-3)", fontSize: 11.5 }}>{t.coeff}</div>
            <div style={{ display: "flex", gap: 4, justifyContent: "flex-end" }}>
              <button className="btn btn-sm btn-ghost" style={{ width: 24, padding: 0 }}>
                <Ics d={IIs.doc} size={12} />
              </button>
              <button className="btn btn-sm btn-ghost" style={{ width: 24, padding: 0 }}>
                <Ics d={IIs.more} size={12} />
              </button>
            </div>
          </div>
        ))}
      </div>

      <SectionDivider title="Riparto entrate fra enti" />
      <div className="card" style={{ padding: 16 }}>
        <div style={{ fontSize: 12.5, color: "var(--ink-2)", marginBottom: 12 }}>
          Le entrate da indennizzo sono ripartite proporzionalmente ai km di
          competenza di ciascun ente attraversato dal percorso autorizzato.
        </div>
        <div style={{ display: "grid", gridTemplateColumns: "repeat(4, 1fr)", gap: 10 }}>
          {[
            ["Provincia (capofila)", "55%", "trattenuta tecnica + km propri"],
            ["Comuni attraversati",  "30%", "in proporzione ai km"],
            ["Gestori (ANAS, conc.)","12%", "tratti di competenza"],
            ["Fondo manutenzione",   " 3%", "Provincia di Pescara"],
          ].map(([who, pct, sub]) => (
            <div key={who} style={{
              padding: "10px 12px", border: "1px solid var(--line)",
              borderRadius: 8, background: "var(--surface)",
            }}>
              <div className="num" style={{ fontSize: 22, fontWeight: 600 }}>{pct}</div>
              <div style={{ fontSize: 12, fontWeight: 500, marginTop: 2 }}>{who}</div>
              <div style={{ fontSize: 11, color: "var(--ink-3)", marginTop: 1 }}>{sub}</div>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}

// ===========================================================================
// SECTION: Utenti & ruoli
// ===========================================================================
function UsersSection() {
  const roles = [
    { key: "super",     label: "Super-admin",         count: 4,
      perms: ["Configurazione sistema", "Gestione utenti", "Audit log", "Tutto il flow pratiche"] },
    { key: "istruttore",label: "Istruttore",          count: 9,
      perms: ["Apri pratiche", "Richiedi nulla osta", "Emetti autorizzazione"] },
    { key: "ragio",     label: "Ragioneria",          count: 3,
      perms: ["Vedi pagamenti", "Riconcilia IUV", "Esporta SIOPE+"] },
    { key: "viewer",    label: "Vista in sola lettura", count: 7,
      perms: ["Consulta pratiche", "Esporta CSV"] },
  ];

  const users = [
    { name: "Marta Cipriani",   email: "marta.cipriani@provincia.pe.it",  role: "Super-admin",  mfa: true,  last: "ora" },
    { name: "Davide Ranieri",   email: "davide.ranieri@provincia.pe.it",  role: "Super-admin",  mfa: true,  last: "12 min" },
    { name: "Elisa Marrone",    email: "elisa.marrone@provincia.pe.it",   role: "Istruttore",   mfa: true,  last: "2 ore" },
    { name: "Giuseppe Costanzo",email: "g.costanzo@provincia.pe.it",      role: "Istruttore",   mfa: false, last: "ieri" },
    { name: "Valentina Russo",  email: "v.russo@provincia.pe.it",         role: "Ragioneria",   mfa: true,  last: "5 ore" },
    { name: "Andrea Petrini",   email: "a.petrini@provincia.pe.it",       role: "Vista",        mfa: true,  last: "3 gg" },
  ];

  return (
    <div style={{ padding: "20px 24px", overflow: "auto", flex: 1 }}>
      <SectionHeader title="Utenti & ruoli"
        subtitle="Accessi al gestionale via SPID + MFA. Le sessioni scadono dopo 90 minuti di inattività."
        right={<button className="btn btn-primary"><Ics d={IIs.plus} size={11} /> Invita utente</button>}
      />

      {/* roles row */}
      <SectionDivider title="Ruoli predefiniti" />
      <div style={{ display: "grid", gridTemplateColumns: "repeat(4, 1fr)", gap: 12 }}>
        {roles.map(r => (
          <div key={r.key} className="card" style={{ padding: 14 }}>
            <div style={{ display: "flex", alignItems: "center", gap: 6 }}>
              <div style={{ fontSize: 13, fontWeight: 600 }}>{r.label}</div>
              <Cs>{r.count}</Cs>
            </div>
            <div style={{ display: "flex", flexDirection: "column", gap: 4, marginTop: 8 }}>
              {r.perms.map(p => (
                <div key={p} style={{ display: "flex", alignItems: "center", gap: 6,
                                      fontSize: 11.5, color: "var(--ink-2)" }}>
                  <span style={{ width: 4, height: 4, borderRadius: 999,
                                 background: "var(--ink-3)" }} />
                  {p}
                </div>
              ))}
            </div>
          </div>
        ))}
      </div>

      {/* user list */}
      <SectionDivider title="Utenti attivi · 23" />
      <div className="card" style={{ overflow: "hidden" }}>
        <div style={{
          display: "grid", gridTemplateColumns: "32px 1fr 1.2fr 130px 70px 90px 30px",
          padding: "10px 14px", fontSize: 10.5, color: "var(--ink-3)",
          letterSpacing: 0.8, textTransform: "uppercase", fontWeight: 500,
          background: "var(--surface-2)", borderBottom: "1px solid var(--line)",
          alignItems: "center",
        }}>
          <div></div><div>Nome</div><div>Email</div><div>Ruolo</div>
          <div>MFA</div><div>Ult. accesso</div><div></div>
        </div>
        {users.map((u, i) => (
          <div key={u.email} className="row-hover" style={{
            display: "grid", gridTemplateColumns: "32px 1fr 1.2fr 130px 70px 90px 30px",
            padding: "10px 14px", alignItems: "center", fontSize: 12.5,
            borderBottom: i < users.length - 1 ? "1px solid var(--line)" : "none",
          }}>
            <Avs name={u.name} tone={u.role === "Super-admin" ? "amber" : "info"} />
            <div style={{ fontWeight: 500 }}>{u.name}</div>
            <div className="mono" style={{ color: "var(--ink-3)", fontSize: 11.5 }}>{u.email}</div>
            <div><Cs tone={u.role === "Super-admin" ? "amber" : "default"}>{u.role}</Cs></div>
            <div>
              {u.mfa
                ? <Cs tone="success">on</Cs>
                : <Cs tone="danger">off</Cs>}
            </div>
            <div style={{ color: "var(--ink-3)" }}>{u.last}</div>
            <button className="btn btn-ghost btn-sm" style={{ width: 22, padding: 0 }}>
              <Ics d={IIs.more} size={12} />
            </button>
          </div>
        ))}
      </div>
    </div>
  );
}

// ===========================================================================
// SECTION: Stati pratica · macchina a stati
// ===========================================================================
function StateMachineSection() {
  const states = [
    { k: "draft",       label: "Bozza",                 type: "init"  },
    { k: "submitted",   label: "Inviata",               type: "step"  },
    { k: "instruct",    label: "Istruttoria",           type: "step"  },
    { k: "wait_clear",  label: "Attesa nulla osta",     type: "wait"  },
    { k: "wait_pay",    label: "Attesa pagamento",      type: "wait"  },
    { k: "approved",    label: "Autorizzata",           type: "final" },
    { k: "rejected",    label: "Respinta",              type: "final-bad" },
  ];
  const transitions = [
    ["draft","submitted","Invio"],
    ["submitted","instruct","Presa in carico"],
    ["instruct","wait_clear","Richiesta n.o."],
    ["wait_clear","wait_pay","Tutti i n.o. ricevuti"],
    ["wait_pay","approved","Pagamento confermato"],
    ["instruct","rejected","Diniego"],
    ["wait_clear","rejected","Diniego ente terzo"],
  ];

  return (
    <div style={{ padding: "20px 24px", overflow: "auto", flex: 1 }}>
      <SectionHeader title="Stati pratica"
        subtitle="Flusso ufficiale di una pratica di autorizzazione · stato salvato ad ogni transizione."
        right={<>
          <button className="btn"><Ics d={IIs.download} size={11} /> Esporta XSD</button>
          <button className="btn btn-primary">Modifica flusso</button>
        </>}
      />

      <div className="card" style={{ padding: 22, marginTop: 16, overflow: "auto" }}>
        {/* horizontal flow visualization */}
        <div style={{ display: "flex", alignItems: "center", gap: 8,
                      minWidth: 900, padding: "8px 0" }}>
          {states.slice(0, 6).map((s, i, arr) => (
            <React.Fragment key={s.k}>
              <StateNode state={s} />
              {i < arr.length - 1 && (
                <div style={{ flex: 1, height: 1, background: "var(--line-2)",
                              position: "relative", minWidth: 30 }}>
                  <div style={{ position: "absolute", right: -4, top: -4,
                                width: 0, height: 0,
                                borderLeft: "5px solid var(--line-2)",
                                borderTop: "4px solid transparent",
                                borderBottom: "4px solid transparent" }} />
                </div>
              )}
            </React.Fragment>
          ))}
        </div>
        <div style={{ display: "flex", justifyContent: "flex-end",
                      marginTop: 8, paddingRight: 4 }}>
          <div style={{ display: "flex", alignItems: "center", gap: 8 }}>
            <span style={{ fontSize: 11, color: "var(--ink-3)" }}>fork ↗</span>
            <StateNode state={states[6]} />
          </div>
        </div>
      </div>

      <SectionDivider title="Transizioni · regole" />
      <div className="card" style={{ overflow: "hidden" }}>
        <div style={{
          display: "grid", gridTemplateColumns: "1fr 1fr 1fr 100px",
          padding: "10px 14px", fontSize: 10.5, color: "var(--ink-3)",
          letterSpacing: 0.8, textTransform: "uppercase", fontWeight: 500,
          background: "var(--surface-2)", borderBottom: "1px solid var(--line)",
        }}>
          <div>Da</div><div>A</div><div>Trigger</div><div></div>
        </div>
        {transitions.map(([from, to, trig], i) => (
          <div key={i} style={{
            display: "grid", gridTemplateColumns: "1fr 1fr 1fr 100px",
            padding: "10px 14px", alignItems: "center", fontSize: 12.5,
            borderBottom: i < transitions.length - 1 ? "1px solid var(--line)" : "none",
          }}>
            <div className="mono">{from}</div>
            <div className="mono">→ {to}</div>
            <div style={{ color: "var(--ink-2)" }}>{trig}</div>
            <button className="btn btn-sm btn-ghost">Modifica</button>
          </div>
        ))}
      </div>
    </div>
  );
}

function StateNode({ state }) {
  const colors = {
    init:       { bg: "var(--surface-2)",  fg: "var(--ink-2)",   border: "var(--line-2)" },
    step:       { bg: "var(--surface)",    fg: "var(--ink)",     border: "var(--ink-2)" },
    wait:       { bg: "var(--accent-bg)",  fg: "var(--accent-ink)", border: "color-mix(in oklch, var(--accent), white 50%)" },
    final:      { bg: "var(--success-bg)", fg: "var(--success)", border: "color-mix(in oklch, var(--success), white 50%)" },
    "final-bad":{ bg: "var(--danger-bg)",  fg: "var(--danger)",  border: "color-mix(in oklch, var(--danger), white 50%)" },
  };
  const c = colors[state.type];
  return (
    <div style={{
      padding: "8px 14px", borderRadius: 8,
      background: c.bg, color: c.fg,
      border: `1px solid ${c.border}`,
      fontSize: 12, fontWeight: 600, whiteSpace: "nowrap",
    }}>{state.label}</div>
  );
}

// ===========================================================================
// SECTION: Sicurezza & audit
// ===========================================================================
function SecuritySection() {
  return (
    <div style={{ padding: "20px 24px", overflow: "auto", flex: 1 }}>
      <SectionHeader title="Sicurezza & audit"
        subtitle="Politiche di accesso, conservazione dei log e crittografia."
        right={<button className="btn btn-primary">Salva modifiche</button>}
      />

      <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 14, marginTop: 16 }}>
        <div className="card" style={{ padding: 16 }}>
          <h3 style={{ margin: 0, fontSize: 13.5, fontWeight: 600 }}>Accessi</h3>
          <div style={{ display: "flex", flexDirection: "column", gap: 10, marginTop: 12 }}>
            <SwitchRow label="MFA obbligatoria · super-admin" desc="OTP · CIE · SPID livello L3" on />
            <SwitchRow label="MFA obbligatoria · tutti" desc="Forza il secondo fattore per ogni accesso" on />
            <SwitchRow label="SSO via SPID/CIE" desc="Disabilita username/password" on />
            <SwitchRow label="Whitelist CIDR · /24" desc="Limita gli IP per super-admin" on />
            <FieldInline label="Scadenza sessione" value="90 min" />
            <FieldInline label="Rotazione password" value="90 gg" />
          </div>
        </div>

        <div className="card" style={{ padding: 16 }}>
          <h3 style={{ margin: 0, fontSize: 13.5, fontWeight: 600 }}>Conservazione & crittografia</h3>
          <div style={{ display: "flex", flexDirection: "column", gap: 10, marginTop: 12 }}>
            <FieldInline label="Conservazione audit log" value="10 anni · ParER" />
            <FieldInline label="Conservazione pratiche" value="40 anni · ParER" />
            <FieldInline label="At-rest" value="AES-256 · HSM AgID" />
            <FieldInline label="In-transit" value="TLS 1.3 · CSP qual." />
            <SwitchRow label="Anonimizzazione DPIA" desc="Maschera IBAN nei log dopo 30 gg" on />
            <SwitchRow label="Backup geo-ridondato" desc="3 copie · 2 datacenter Italia" on />
          </div>
        </div>
      </div>

      <SectionDivider title="Eventi di sicurezza · ultimi 10" />
      <div className="card" style={{ overflow: "hidden" }}>
        {[
          { sev: "high",   when: "11 mag · 22:14", what: "Accesso bloccato · IP fuori CIDR consentito",
            who: "g.costanzo@…", ip: "151.42.118.4" },
          { sev: "medium", when: "11 mag · 19:02", what: "MFA fallita 3 volte consecutive",
            who: "v.russo@…",    ip: "78.211.4.119" },
          { sev: "low",    when: "11 mag · 09:14", what: "Rotazione chiave HSM (PagoPA)",
            who: "Sistema",      ip: "—" },
          { sev: "low",    when: "10 mag · 16:48", what: "Esportazione massiva CSV (54 pratiche)",
            who: "marta.cipriani@…", ip: "192.168.10.3" },
          { sev: "medium", when: "10 mag · 11:02", what: "Concessione ruolo super-admin a nuovo utente",
            who: "marta.cipriani@…", ip: "192.168.10.3" },
        ].map((e, i, arr) => (
          <div key={i} style={{
            display: "grid", gridTemplateColumns: "70px 130px 1fr 180px 130px",
            padding: "10px 14px", gap: 10, alignItems: "center", fontSize: 12.5,
            borderBottom: i < arr.length - 1 ? "1px solid var(--line)" : "none",
          }}>
            <Cs tone={e.sev === "high" ? "danger" : e.sev === "medium" ? "amber" : "default"}>
              {e.sev}
            </Cs>
            <span className="mono" style={{ color: "var(--ink-3)" }}>{e.when}</span>
            <span>{e.what}</span>
            <span className="mono" style={{ fontSize: 11.5, color: "var(--ink-3)" }}>{e.who}</span>
            <span className="mono" style={{ fontSize: 11.5, color: "var(--ink-3)" }}>{e.ip}</span>
          </div>
        ))}
      </div>
    </div>
  );
}

// ===========================================================================
// Generic placeholder
// ===========================================================================
function PlaceholderSection({ title, subtitle, chips = [] }) {
  return (
    <div style={{ padding: "20px 24px", overflow: "auto", flex: 1 }}>
      <SectionHeader title={title} subtitle={subtitle} />
      <div className="card" style={{
        marginTop: 16, padding: "32px 24px", textAlign: "center",
        background: "var(--surface-2)", borderStyle: "dashed",
      }}>
        <div style={{ display: "flex", justifyContent: "center", gap: 8, flexWrap: "wrap" }}>
          {chips.map(c => <Cs key={c}>{c}</Cs>)}
        </div>
        <div style={{ fontSize: 12.5, color: "var(--ink-3)", marginTop: 14 }}>
          Sezione di design in corso · gli elementi qui sopra sono i blocchi previsti.
        </div>
      </div>
    </div>
  );
}

// ===========================================================================
// Reusable bits
// ===========================================================================
function SectionHeader({ title, subtitle, right }) {
  return (
    <div style={{ display: "flex", alignItems: "flex-end", gap: 14 }}>
      <div style={{ flex: 1 }}>
        <h2 style={{ margin: 0, fontSize: 20, fontWeight: 600, letterSpacing: "-0.01em" }}>
          {title}
        </h2>
        {subtitle && (
          <div style={{ fontSize: 12.5, color: "var(--ink-3)", marginTop: 4, maxWidth: 720,
                        lineHeight: 1.5 }}>{subtitle}</div>
        )}
      </div>
      <div style={{ display: "flex", gap: 8 }}>{right}</div>
    </div>
  );
}

function SectionDivider({ title }) {
  return (
    <div style={{ display: "flex", alignItems: "center", gap: 12,
                  margin: "22px 0 10px" }}>
      <div style={{ fontSize: 11, color: "var(--ink-3)", letterSpacing: 1.2,
                    textTransform: "uppercase", fontWeight: 600 }}>{title}</div>
      <div style={{ flex: 1, height: 1, background: "var(--line)" }} />
    </div>
  );
}

function FieldGroup({ title, children }) {
  return (
    <div className="card" style={{ padding: 14 }}>
      {title && (
        <div style={{ fontSize: 11, color: "var(--ink-3)", letterSpacing: 1,
                      textTransform: "uppercase", fontWeight: 600, marginBottom: 10 }}>
          {title}
        </div>
      )}
      <div style={{ display: "flex", flexDirection: "column", gap: 10 }}>{children}</div>
    </div>
  );
}

function Field({ label, value, mono = true, locked }) {
  return (
    <label style={{ display: "flex", flexDirection: "column", gap: 4 }}>
      <span style={{ fontSize: 11, color: "var(--ink-3)", letterSpacing: 0.4,
                     textTransform: "uppercase", fontWeight: 500,
                     display: "flex", alignItems: "center", gap: 6 }}>
        {label}
        {locked && (
          <span style={{ fontSize: 9.5, padding: "1px 4px", borderRadius: 3,
                         background: "var(--surface-2)", color: "var(--ink-3)",
                         border: "1px solid var(--line)", letterSpacing: 0.5 }}>
            IPA
          </span>
        )}
      </span>
      <input defaultValue={value} disabled={locked}
        className={mono ? "mono" : ""} style={{
          height: 32, padding: "0 10px",
          border: "1px solid var(--line-2)", borderRadius: 7,
          background: locked ? "var(--surface-2)" : "var(--surface)",
          color: locked ? "var(--ink-3)" : "var(--ink)",
          fontSize: 13, fontWeight: 500,
          fontFamily: mono ? "DM Mono, ui-monospace, monospace" : "inherit",
          outline: "none",
        }} />
    </label>
  );
}

function FieldInline({ label, value }) {
  return (
    <div style={{ display: "flex", alignItems: "center", gap: 12,
                  padding: "8px 0", borderBottom: "1px solid var(--line)" }}>
      <span style={{ fontSize: 12.5, color: "var(--ink-2)", flex: 1 }}>{label}</span>
      <span className="mono" style={{ fontSize: 12, fontWeight: 500 }}>{value}</span>
      <button className="btn btn-sm btn-ghost" style={{ fontSize: 11.5 }}>Modifica</button>
    </div>
  );
}

function SwitchRow({ label, desc, on }) {
  return (
    <div style={{ display: "flex", alignItems: "flex-start", gap: 10,
                  padding: "8px 0", borderBottom: "1px solid var(--line)" }}>
      <div style={{ flex: 1 }}>
        <div style={{ fontSize: 12.5, fontWeight: 500 }}>{label}</div>
        {desc && <div style={{ fontSize: 11, color: "var(--ink-3)", marginTop: 1 }}>{desc}</div>}
      </div>
      <div style={{
        width: 32, height: 18, borderRadius: 999, marginTop: 2,
        background: on ? "var(--ink)" : "var(--surface-3)",
        border: "1px solid var(--line-2)", position: "relative", flexShrink: 0,
      }}>
        <div style={{
          position: "absolute", top: 1, left: on ? 15 : 1,
          width: 14, height: 14, borderRadius: 999,
          background: "var(--surface)",
          boxShadow: "0 1px 2px rgba(0,0,0,0.15)",
        }} />
      </div>
    </div>
  );
}

window.GTEScreens = { ...(window.GTEScreens || {}), OperatorSettingsScreen };

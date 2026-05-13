/* global React, PNTE, PNTEMap, PNTEShell, PNTEScreens */
const { Icon: Icm, I: IIm } = window.PNTE;
const { TopBar, SideNav } = window.PNTEShell;
const { WizardScreen, OperatorScreen, ThirdPartyScreen, LawScreen, CitizenScreen } = window.PNTEScreens;

// Wraps a screen in a fake "app" frame: top bar + side nav.
function AppFrame({ palette = "civic", role, roleLabel, navItems, navActive, children }) {
  return (
    <div data-palette={palette} data-density="cozy" style={{
      width: "100%", height: "100%",
      background: "var(--bg)", color: "var(--ink)",
      display: "flex", flexDirection: "column",
      overflow: "hidden",
      borderRadius: 6,
    }}>
      <TopBar role={role} roleLabel={roleLabel} />
      <div style={{ display: "flex", flex: 1, minHeight: 0 }}>
        <SideNav items={navItems} active={navActive} />
        <div style={{ flex: 1, minWidth: 0, background: "var(--bg)" }}>
          {children}
        </div>
      </div>
    </div>
  );
}

// nav presets per role
const NAV = {
  citizen: [
    { key: "home", icon: "doc", label: "Le mie pratiche", badge: "3", badgeTone: "amber" },
    { key: "new", icon: "plus", label: "Nuova domanda" },
    { key: "garage", icon: "truck", label: "Garage Virtuale" },
    { key: "deleghe", icon: "user", label: "Deleghe" },
    { key: "fatturazione", icon: "euro", label: "Fatturazione" },
    { key: "guida", icon: "doc", label: "Guida & FAQ" },
  ],
  operator: [
    { key: "home", icon: "doc", label: "Pratiche", badge: "47", badgeTone: "amber" },
    { key: "new", icon: "plus", label: "Apri pratica" },
    { key: "enti", icon: "flag", label: "Enti territoriali" },
    { key: "tariffe", icon: "euro", label: "Tariffario" },
    { key: "ainop", icon: "bridge", label: "AINOP / PDND" },
    { key: "rendi", icon: "doc", label: "Ragioneria" },
    { key: "audit", icon: "clock", label: "Audit log" },
  ],
  third: [
    { key: "home", icon: "doc", label: "Pareri", badge: "3", badgeTone: "amber" },
    { key: "cantieri", icon: "cone", label: "Cantieri", badge: "3" },
    { key: "storico", icon: "clock", label: "Storico" },
    { key: "tariffe", icon: "euro", label: "Tariffario" },
    { key: "mappa", icon: "map", label: "Mappa territorio" },
  ],
  law: [
    { key: "verifica", icon: "qr", label: "Verifica" },
    { key: "transiti", icon: "truck", label: "Transiti oggi", badge: "12" },
    { key: "cantieri", icon: "cone", label: "Cantieri attivi" },
    { key: "mappa", icon: "map", label: "Mappa real-time" },
  ],
};

// ===========================================================================
// Smaller artboards: palette variations on a single hero (wizard map header)
// ===========================================================================
function PaletteVariation({ palette, label }) {
  return (
    <div data-palette={palette} data-density="cozy" style={{
      width: "100%", height: "100%",
      background: "var(--bg)", color: "var(--ink)",
      display: "flex", flexDirection: "column",
      padding: 20, gap: 14, overflow: "hidden",
      borderRadius: 6,
    }}>
      <div style={{ display: "flex", alignItems: "center", gap: 10 }}>
        <div style={{ width: 28, height: 28, borderRadius: 6, background: "var(--ink)",
                      color: "var(--bg)", display: "flex", alignItems: "center",
                      justifyContent: "center", fontWeight: 700, fontSize: 12 }}>
          PNTE
        </div>
        <div style={{ flex: 1 }}>
          <div style={{ fontWeight: 600, fontSize: 14 }}>PNTE</div>
          <div style={{ fontSize: 11, color: "var(--ink-3)" }}>Direzione visiva: {label}</div>
        </div>
        <span className="chip chip-amber"><Icm d={IIm.bolt} size={11} /> Hero feature</span>
      </div>

      <div className="card" style={{ padding: 12, flex: 1, display: "flex",
                                     flexDirection: "column", gap: 10, minHeight: 0 }}>
        <div style={{ display: "flex", gap: 8, alignItems: "center" }}>
          <strong style={{ fontSize: 13 }}>Pescara → Sulmona · 84,2 km</strong>
          <span className="chip">6 enti</span>
          <span className="chip chip-success">2/2 ponti AINOP</span>
          <div style={{ flex: 1 }} />
          <span className="chip chip-amber">1 cantiere</span>
        </div>
        <div style={{ flex: 1, minHeight: 0 }}>
          <PNTEMap height="100%" />
        </div>
        <div style={{ display: "grid", gridTemplateColumns: "repeat(4, 1fr)", gap: 0,
                      border: "1px solid var(--line)", borderRadius: 8, overflow: "hidden" }}>
          {[
            ["Lunghezza", "84,2 km"],
            ["Tempo", "1h 48m"],
            ["Massa", "76,4 t"],
            ["Indennizzo", "€ 412,80"],
          ].map(([l, v], i) => (
            <div key={l} style={{
              padding: "8px 12px",
              borderRight: i < 3 ? "1px solid var(--line)" : "none",
            }}>
              <div style={{ fontSize: 10, color: "var(--ink-3)", letterSpacing: 1,
                            textTransform: "uppercase", fontWeight: 500 }}>{l}</div>
              <div className="num" style={{ fontSize: 16, fontWeight: 600 }}>{v}</div>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}

// ===========================================================================
// Mount onto design canvas
// ===========================================================================

function App() {
  const [palette, setPalette] = React.useState("istituzionale");
  const [density, setDensity] = React.useState("cozy");

  // Tweaks panel
  const TweaksPanel = window.TweaksPanel;
  const TweakRadio = window.TweakRadio;
  const TweakSection = window.TweakSection;

  return (
    <>
      <DesignCanvas
        title="PNTE · Esplorazione design"
        subtitle="Gestionale Trasporti Eccezionali · Provincia di Pescara · Riuso EUPL-1.2"
      >
        <DCSection id="visual" title="Direzioni visive · Hero feature (WebGIS)"
                   subtitle="Stesso schermo in tre tonalità — il tracciamento percorso con auto-detection enti">
          <DCArtboard id="vis-a" label="A · Civic Warm (default)" width={760} height={520}>
            <PaletteVariation palette="civic" label="Civic Warm" />
          </DCArtboard>
          <DCArtboard id="vis-b" label="B · Istituzionale" width={760} height={520}>
            <PaletteVariation palette="istituzionale" label="Istituzionale" />
          </DCArtboard>
          <DCArtboard id="vis-c" label="C · Territoriale" width={760} height={520}>
            <PaletteVariation palette="territoriale" label="Territoriale Abruzzo" />
          </DCArtboard>
        </DCSection>

        <DCSection id="hero" title="Hero · Wizard tracciamento percorso"
                   subtitle="Cittadino/Azienda · step 3 di 5 · WebGIS + auto-detection enti + cantieri">
          <DCArtboard id="wizard" label="Wizard percorso · Cittadino" width={1440} height={920} key={`wizard-${palette}`}>
            <AppFrame palette={palette} role="citizen / azienda"
                      roleLabel="Marco Ferraris" navItems={NAV.citizen} navActive="new">
              <WizardScreen />
            </AppFrame>
          </DCArtboard>
        </DCSection>

        <DCSection id="multi" title="Vista multi-ruolo"
                   subtitle="Stesso prodotto, ruoli diversi · super-admin / third-party / law-enforcement / citizen">
          <DCArtboard id="operator" label="Scrivania Operatore Provincia" width={1440} height={900}>
            <AppFrame palette={palette} role="super-admin · Provincia di Pescara"
                      roleLabel="Marta Cipriani" navItems={NAV.operator} navActive="home">
              <OperatorScreen />
            </AppFrame>
          </DCArtboard>

          <DCArtboard id="third" label="Scrivania Ente Terzo (Comune)" width={1440} height={900}>
            <AppFrame palette={palette} role="third-party · Comune di Sulmona"
                      roleLabel="Luca Di Marco" navItems={NAV.third} navActive="home">
              <ThirdPartyScreen />
            </AppFrame>
          </DCArtboard>

          <DCArtboard id="law" label="Forze dell'Ordine · Verifica trasporti" width={1440} height={900}>
            <AppFrame palette={palette} role="law-enforcement · Polizia Stradale"
                      roleLabel="App. S. Riva" navItems={NAV.law} navActive="verifica">
              <LawScreen />
            </AppFrame>
          </DCArtboard>

          <DCArtboard id="citizen" label="Cittadino / Azienda · Le mie pratiche + Garage" width={1440} height={900}>
            <AppFrame palette={palette} role="citizen / azienda"
                      roleLabel="Marco Ferraris" navItems={NAV.citizen} navActive="home">
              <CitizenScreen />
            </AppFrame>
          </DCArtboard>
        </DCSection>
      </DesignCanvas>

      {/* Tweaks */}
      <TweaksPanel title="Tweaks" defaults={{ palette: "civic" }}>
        {({ tweaks, setTweak }) => (
          <>
            <TweakSection title="Direzione visiva">
              <TweakRadio
                value={palette}
                onChange={(v) => { setPalette(v); setTweak("palette", v); }}
                options={[
                  { value: "civic", label: "Civic Warm" },
                  { value: "istituzionale", label: "Istituzionale" },
                  { value: "territoriale", label: "Territoriale" },
                ]}
              />
            </TweakSection>
            <TweakSection title="Densità UI">
              <TweakRadio
                value={density}
                onChange={setDensity}
                options={[
                  { value: "compact", label: "Compatta" },
                  { value: "cozy", label: "Comoda" },
                ]}
              />
            </TweakSection>
            <div style={{ fontSize: 11.5, color: "var(--ink-3)", padding: "4px 0",
                          lineHeight: 1.5 }}>
              I cambi di palette si applicano alle artboard "Vista multi-ruolo" e all'Hero.
            </div>
          </>
        )}
      </TweaksPanel>
    </>
  );
}

ReactDOM.createRoot(document.getElementById("root")).render(<App />);

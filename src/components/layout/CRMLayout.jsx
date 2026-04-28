/* global React, ReactDOM */
const { useState, useEffect } = React;

// КРОК 6 — Theme + routing з localStorage persistence
function App() {
  const [theme, setTheme] = useState(
    () => localStorage.getItem("crm_theme") || "dark"
  );
  const [screen, setScreen] = useState(
    () => localStorage.getItem("crm_screen") || "dashboard"
  );

  useEffect(() => {
    localStorage.setItem("crm_theme", theme);
    document.body.style.background = "var(--bg)";
  }, [theme]);

  useEffect(() => {
    localStorage.setItem("crm_screen", screen);
  }, [screen]);

  // Routing
  const SidebarComp = window.Sidebar;
  const TopbarComp  = window.Topbar;

  let ScreenComp;
  if (screen.startsWith("site:")) {
    const siteId = screen.split(":")[1];
    ScreenComp = () => React.createElement(window.Screen_SitePage, { id: siteId, goto: setScreen });
  } else {
    const map = {
      dashboard: window.Screen_Dashboard,
      sites:     window.Screen_Sites,
      groups:    window.Screen_Groups,
      activity:  window.Screen_Activity
    };
    const Comp = map[screen] || window.Screen_Dashboard;
    ScreenComp = () => React.createElement(Comp, { goto: setScreen });
  }

  return (
    <div className={`vibe vibeB${theme === "dark" ? " dark" : ""}`}>
      <div style={{ display:"flex", minHeight:"100vh" }}>
        <SidebarComp screen={screen} setScreen={setScreen}/>
        <div style={{ flex:1, minWidth:0, display:"flex", flexDirection:"column" }}>
          <TopbarComp/>
          <main style={{ padding:"28px 32px 60px", flex:1 }}>
            <ScreenComp/>
          </main>
        </div>
      </div>

      {/* Theme toggle */}
      <button
        onClick={()=>setTheme(t=>t==="dark"?"light":"dark")}
        title="Toggle theme"
        style={{
          position:"fixed", right:16, bottom:16, zIndex:100,
          width:36, height:36, borderRadius:99,
          background:"var(--panel)", border:"1px solid var(--border)",
          color:"var(--text-2)", fontSize:16, cursor:"pointer",
          display:"flex", alignItems:"center", justifyContent:"center",
          boxShadow:"var(--shadow)"
        }}
      >{theme==="dark" ? "☀" : "☾"}</button>
    </div>
  );
}

ReactDOM.createRoot(document.getElementById("root")).render(React.createElement(App));

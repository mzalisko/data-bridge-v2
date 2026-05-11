/* global React, I, UI */
const { Avatar } = UI;

// КРОК 3 — Sites nav item, isActive враховує "site:ID"
function Sidebar({ screen, setScreen }) {
  const { SITES } = window.CRM_DATA;

  const items = [
    { id:"dashboard", label:"Dashboard",   icon: <I.dashboard/> },
    { id:"sites",     label:"Sites",       icon: <I.contacts/>, count: SITES.length },
    { id:"groups",    label:"Site groups", icon: <I.dashboard/> },
    { id:"activity",  label:"Activity log",icon: <I.sync/> }
  ];

  const isActive = (id) => screen === id || (id==="sites" && screen.startsWith("site:"));

  return (
    <aside style={{
      width:240, background:"var(--panel)", borderRight:"1px solid var(--border)",
      display:"flex", flexDirection:"column", padding:"18px 14px", gap:14, flexShrink:0,
      position:"sticky", top:0, height:"100vh", overflowY:"auto"
    }}>
      {/* Logo */}
      <div style={{ display:"flex", alignItems:"center", gap:10, padding:"4px 8px 12px" }}>
        <span style={{
          width:30, height:30, borderRadius:9, background:"var(--accent)", color:"#fff",
          display:"inline-flex", alignItems:"center", justifyContent:"center"
        }}>
          <I.logo s={18}/>
        </span>
        <div>
          <div style={{ fontSize:14, fontWeight:600 }}>DataBridge</div>
          <div style={{ fontSize:10.5, color:"var(--text-3)", fontFamily:"var(--font-mono)" }}>CRM · workspace</div>
        </div>
      </div>

      {/* Nav */}
      <nav style={{ display:"flex", flexDirection:"column", gap:2 }}>
        {items.map(it => {
          const active = isActive(it.id);
          return (
            <button key={it.id} onClick={()=>setScreen(it.id)} style={{
              display:"flex", alignItems:"center", gap:10, padding:"8px 10px",
              border:0, borderRadius:8, cursor:"pointer", textAlign:"left",
              background: active ? "var(--accent-2)" : "transparent",
              color: active ? "var(--accent-text)" : "var(--text-2)",
              fontSize:13, fontWeight: active ? 600 : 500, fontFamily:"inherit"
            }}>
              {it.icon}
              <span style={{ flex:1 }}>{it.label}</span>
              {it.count != null && (
                <span style={{ fontSize:11, color:"var(--text-3)", fontFamily:"var(--font-mono)" }}>{it.count}</span>
              )}
            </button>
          );
        })}
      </nav>

      <div style={{ flex:1 }}/>

      {/* Workspace block */}
      <div style={{ padding:"12px", borderRadius:10, background:"var(--panel-2)", border:"1px solid var(--border)" }}>
        <div style={{ fontSize:11, color:"var(--text-3)", textTransform:"uppercase", letterSpacing:".06em", fontWeight:600 }}>Workspace</div>
        <div style={{ fontSize:13, fontWeight:600, marginTop:4 }}>Beacon HQ</div>
        <div style={{ display:"flex", alignItems:"center", marginTop:8, gap:2 }}>
          {["Anna Cole","Sasha Kim","David Park","Kim Lee"].map((n,i) => (
            <span key={i} style={{ marginLeft: i===0 ? 0 : -6 }}>
              <Avatar name={n} size={22}/>
            </span>
          ))}
          <button style={{
            width:22, height:22, borderRadius:99, border:"1px dashed var(--border)",
            background:"transparent", color:"var(--text-3)", fontSize:14,
            display:"inline-flex", alignItems:"center", justifyContent:"center",
            cursor:"pointer", marginLeft:2
          }}>+</button>
        </div>
      </div>

      {/* User row */}
      <div style={{ display:"flex", alignItems:"center", gap:10, padding:"8px" }}>
        <Avatar name="Anna Cole" size={28}/>
        <div style={{ minWidth:0, flex:1 }}>
          <div style={{ fontSize:12, fontWeight:500 }}>Anna Cole</div>
          <div style={{ fontSize:10.5, color:"var(--text-3)" }}>Owner</div>
        </div>
      </div>
    </aside>
  );
}

window.Sidebar = Sidebar;

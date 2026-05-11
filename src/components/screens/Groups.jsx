/* global React, I, UI */
const { Btn, Card } = UI;
const { PageHead, SiteFavicon } = window.SHARED;

function Groups({ goto }) {
  const { GROUPS, SITES } = window.CRM_DATA;
  const iconBtn = { background:"transparent", border:0, color:"var(--text-3)", cursor:"pointer", padding:6, borderRadius:6 };

  return (
    <div style={{ display:"flex", flexDirection:"column", gap:20 }}>
      <PageHead title="Site groups" subtitle="Organize sites by agency, client, or purpose."
        actions={<Btn kind="primary" size="md" icon={<I.plus/>}>New group</Btn>}/>

      <div style={{ display:"grid", gridTemplateColumns:"repeat(2, 1fr)", gap:16 }}>
        {GROUPS.map(g => {
          const sites = SITES.filter(s=>s.group===g.name);
          return (
            <Card key={g.id} pad={false}>
              {/* Group header */}
              <div style={{ padding:"18px 20px", display:"flex", alignItems:"flex-start", justifyContent:"space-between", gap:12 }}>
                <div style={{ display:"flex", gap:12, alignItems:"flex-start" }}>
                  <span style={{
                    width:38, height:38, borderRadius:8, flexShrink:0,
                    background:g.color+"22", color:g.color,
                    display:"inline-flex", alignItems:"center", justifyContent:"center"
                  }}>
                    <I.dashboard s={18}/>
                  </span>
                  <div>
                    <div style={{ fontSize:15, fontWeight:600 }}>{g.name}</div>
                    <div style={{ fontSize:12, color:"var(--text-3)", marginTop:2 }}>{g.desc}</div>
                  </div>
                </div>
                <button style={iconBtn}><I.more/></button>
              </div>

              {/* Stats row */}
              <div style={{ display:"grid", gridTemplateColumns:"repeat(3,1fr)", borderTop:"1px solid var(--border-2)" }}>
                <GroupStat k="Sites" v={g.sites}/>
                <GroupStat k="Contacts" v={g.contacts.toLocaleString()} border/>
                <GroupStat k="Owner" v={g.owner} small border/>
              </div>

              {/* Site chips */}
              <div style={{ borderTop:"1px solid var(--border-2)", padding:"10px 16px" }}>
                <div style={{ display:"flex", flexWrap:"wrap", gap:6 }}>
                  {sites.slice(0,4).map(s => (
                    <button key={s.id} onClick={()=>goto("site:"+s.id)} style={{
                      display:"inline-flex", alignItems:"center", gap:6, padding:"4px 8px",
                      background:"var(--panel-2)", border:"1px solid var(--border)", borderRadius:99,
                      fontSize:11, color:"var(--text-2)", cursor:"pointer", fontFamily:"inherit"
                    }}>
                      <SiteFavicon name={s.title} size={14}/>{s.name}
                    </button>
                  ))}
                  {sites.length > 4 && (
                    <span style={{ fontSize:11, color:"var(--text-3)", padding:"4px 8px" }}>+{sites.length-4} more</span>
                  )}
                </div>
              </div>
            </Card>
          );
        })}
      </div>
    </div>
  );
}

function GroupStat({ k, v, border, small }) {
  return (
    <div style={{ padding:"12px 16px", borderLeft: border ? "1px solid var(--border-2)" : "none" }}>
      <div style={{ fontSize:11, color:"var(--text-3)", textTransform:"uppercase", letterSpacing:".05em" }}>{k}</div>
      <div style={{ fontSize: small ? 13 : 18, fontWeight: small ? 500 : 600, marginTop:4, whiteSpace:"nowrap", overflow:"hidden", textOverflow:"ellipsis" }}>{v}</div>
    </div>
  );
}

window.Screen_Groups = Groups;

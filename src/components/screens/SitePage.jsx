/* global React, I, UI */
const { useState } = React;
const { Btn, Card, Pill } = UI;
const { PageHead, StatusPill, PlanPill, GroupChip, SiteFavicon, ActivityRow, Select, SiteData } = window.SHARED;

function SitePage({ id, goto }) {
  const { SITES, SITE_DATA, ACTIVITY } = window.CRM_DATA;
  const s    = SITES.find(x=>x.id===id) || SITES[5];
  const data = SITE_DATA[s.id] || SITE_DATA.s_006;
  const [tab, setTab] = useState("overview");

  return (
    <div style={{ display:"flex", flexDirection:"column", gap:20 }}>
      {/* Breadcrumb + PageHead */}
      <PageHead
        breadcrumb={
          <span>
            <a onClick={()=>goto("sites")} style={{ cursor:"pointer", color:"inherit" }}>Sites</a>
            {" "}/{" "}
            <span style={{ color:"var(--text)" }}>{s.title}</span>
          </span>
        }
        title={
          <span style={{ display:"inline-flex", alignItems:"center", gap:12 }}>
            <SiteFavicon name={s.title} size={28}/>{s.title}
          </span>
        }
        subtitle={<span style={{ fontFamily:"var(--font-mono)" }}>{s.name} · plugin {s.plugin}</span>}
        actions={<>
          <Btn kind="secondary" size="md" icon={<I.external/>}>Open site</Btn>
          <Btn kind="secondary" size="md" icon={<I.sync s={13}/>}>Resync</Btn>
          <Btn kind="primary" size="md">Push update</Btn>
        </>}/>

      {/* Stats row — 5 cards */}
      <div style={{ display:"grid", gridTemplateColumns:"repeat(5, 1fr)", gap:12 }}>
        <StatMini k="Status"    v={<StatusPill s={s.status}/>}/>
        <StatMini k="Plan"      v={<PlanPill plan={s.plan}/>}/>
        <StatMini k="Contacts"  v={s.contacts}/>
        <StatMini k="Phones"    v={s.phones}/>
        <StatMini k="Last seen" v={s.lastSeen}/>
      </div>

      {/* Tab card */}
      <Card pad={false}>
        {/* Tab bar */}
        <div style={{ display:"flex", borderBottom:"1px solid var(--border-2)", padding:"0 16px" }}>
          {[["overview","Overview"],["data","Data"],["activity","Activity"],["settings","Settings"]].map(([k,l])=>(
            <button key={k} onClick={()=>setTab(k)} style={{
              background:"transparent", border:0, padding:"14px 14px", fontSize:13, cursor:"pointer",
              color: tab===k ? "var(--text)" : "var(--text-3)",
              borderBottom: tab===k ? "2px solid var(--accent)" : "2px solid transparent",
              marginBottom:-1, fontWeight:500, fontFamily:"inherit"
            }}>{l}</button>
          ))}
        </div>

        {/* Overview tab */}
        {tab==="overview" && (
          <div style={{ display:"grid", gridTemplateColumns:"1fr 1fr", gap:1, background:"var(--border-2)" }}>
            <div style={{ background:"var(--panel)", padding:20 }}>
              <h4 style={{ margin:"0 0 12px", fontSize:12, color:"var(--text-3)", textTransform:"uppercase", letterSpacing:".06em" }}>Site info</h4>
              <KV k="Domain"         v={s.name} mono/>
              <KV k="Group"          v={<GroupChip name={s.group}/>}/>
              <KV k="Region"         v={s.region}/>
              <KV k="Added by"       v={s.addedBy}/>
              <KV k="Plugin version" v={s.plugin} mono/>
              <KV k="API key"        v="dbk_••••••••••••••••a1b2" mono/>
            </div>
            <div style={{ background:"var(--panel)", padding:20 }}>
              <h4 style={{ margin:"0 0 12px", fontSize:12, color:"var(--text-3)", textTransform:"uppercase", letterSpacing:".06em" }}>Sync health</h4>
              <KV k="Last successful sync" v={s.lastSeen}/>
              <KV k="Sync frequency"       v="Every 5 min"/>
              <KV k="Webhook"              v={<Pill kind="success">Active</Pill>}/>
              <KV k="Conflicts"            v={s.conflicts===0 ? <Pill kind="success">None</Pill> : <Pill kind="warning">{s.conflicts} pending</Pill>}/>
              <KV k="Errors (24h)"         v="0"/>
            </div>
          </div>
        )}

        {/* Data tab */}
        {tab==="data" && <SiteData data={data}/>}

        {/* Activity tab */}
        {tab==="activity" && (
          <div>
            {ACTIVITY.filter(a=>a.target.includes(s.name)).concat(ACTIVITY.slice(0,4)).slice(0,6).map((a,i)=>(
              <ActivityRow key={i} {...a}/>
            ))}
          </div>
        )}

        {/* Settings tab */}
        {tab==="settings" && (
          <div style={{ padding:20, display:"flex", flexDirection:"column", gap:0 }}>
            <SettingRow label="Auto-sync" desc="Pull updates from this site automatically">
              <UI.Toggle on={true} onChange={()=>{}}/>
            </SettingRow>
            <SettingRow label="Sync frequency" desc="How often to pull updates">
              <Select value="Every 5 min" onChange={()=>{}} options={["Every 5 min","Every 15 min","Hourly","Manual only"]}/>
            </SettingRow>
            <SettingRow label="Allow plugin to push" desc="Let the WP plugin write back changes">
              <UI.Toggle on={false} onChange={()=>{}}/>
            </SettingRow>
            <SettingRow label="Notify on errors" desc="Email the team if sync fails">
              <UI.Toggle on={true} onChange={()=>{}}/>
            </SettingRow>
            <div style={{ borderTop:"1px solid var(--border-2)", marginTop:6, paddingTop:14, display:"flex", justifyContent:"space-between" }}>
              <Btn kind="danger" size="md" icon={<I.trash/>}>Remove site</Btn>
              <Btn kind="secondary" size="md">Rotate API key</Btn>
            </div>
          </div>
        )}
      </Card>
    </div>
  );
}

function StatMini({ k, v }) {
  return (
    <UI.Card style={{ padding:"14px 16px" }}>
      <div style={{ fontSize:11, color:"var(--text-3)", textTransform:"uppercase", letterSpacing:".05em", marginBottom:6 }}>{k}</div>
      <div style={{ fontSize:18, fontWeight:600 }}>{v}</div>
    </UI.Card>
  );
}
function KV({ k, v, mono }) {
  return (
    <div style={{ display:"grid", gridTemplateColumns:"160px 1fr", gap:10, padding:"8px 0", fontSize:13, alignItems:"center" }}>
      <span style={{ color:"var(--text-3)" }}>{k}</span>
      <span style={{ color:"var(--text-2)", fontFamily: mono ? "var(--font-mono)" : "inherit", fontSize: mono ? 12 : 13 }}>{v}</span>
    </div>
  );
}
function SettingRow({ label, desc, children }) {
  return (
    <div style={{ display:"flex", alignItems:"center", justifyContent:"space-between", gap:14, padding:"12px 0", borderTop:"1px solid var(--border-2)" }}>
      <div>
        <div style={{ fontSize:13, fontWeight:500 }}>{label}</div>
        <div style={{ fontSize:12, color:"var(--text-3)", marginTop:2 }}>{desc}</div>
      </div>
      {children}
    </div>
  );
}

window.Screen_SitePage = SitePage;

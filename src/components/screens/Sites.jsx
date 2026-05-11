/* global React, I, UI */
const { useState } = React;
const { Btn, Card, Input } = UI;
const { PageHead, StatusPill, PlanPill, GroupChip, SiteFavicon, Select, iconBtn } = window.SHARED;

// КРОК 5 — Add Site inline form
function AddSiteForm({ onAdd }) {
  const [domain, setDomain] = React.useState("");
  const [group, setGroup] = React.useState(window.CRM_DATA.GROUPS[0].name);
  return (
    <div style={{ display:"flex", gap:8, alignItems:"center", padding:"12px 16px", borderBottom:"1px solid var(--border-2)", background:"var(--panel-2)" }}>
      <Input value={domain} onChange={setDomain} placeholder="domain.com" icon={<I.external/>} style={{ flex:1, maxWidth:280 }}/>
      <Select value={group} onChange={setGroup} options={window.CRM_DATA.GROUPS.map(g=>g.name)}/>
      <Btn kind="primary" size="sm" icon={<I.plus s={13}/>} onClick={()=>{
        if (domain) { console.log("addSite", { group, domain }); onAdd(); }
      }}>Add site</Btn>
      <Btn kind="ghost" size="sm" onClick={onAdd}>Cancel</Btn>
    </div>
  );
}

function Sites({ goto }) {
  const { SITES, GROUPS } = window.CRM_DATA;
  const [q, setQ]           = useState("");
  const [group, setGroup]   = useState("All groups");
  const [status, setStatus] = useState("All");
  const [sel, setSel]       = useState(new Set());
  const [showAdd, setShowAdd] = useState(false);

  // КРОК 4 — Region tabs
  const REGIONS = ["All", "EU", "NA", "AS", "ME"];
  const [region, setRegion] = useState("All");

  const rows = SITES.filter(s =>
    (group==="All groups" || s.group===group) &&
    (status==="All" || s.status===status) &&
    (region==="All" || s.region===region) &&
    (q==="" || s.title.toLowerCase().includes(q.toLowerCase()) || s.name.toLowerCase().includes(q.toLowerCase()))
  );

  const allChecked = rows.length > 0 && rows.every(r=>sel.has(r.id));
  const th = (w) => ({ textAlign:"left", padding:"10px 14px", fontWeight:500, width:w });
  const td = () => ({ padding:"12px 14px", verticalAlign:"middle" });

  return (
    <div style={{ display:"flex", flexDirection:"column", gap:20 }}>
      <PageHead
        title="Sites"
        subtitle={`${SITES.length} sites across ${GROUPS.length} groups`}
        actions={<>
          <Btn kind="secondary" size="md" icon={<I.download/>}>Export</Btn>
          <Btn kind="primary" size="md" icon={<I.plus/>} onClick={()=>setShowAdd(v=>!v)}>Add site</Btn>
        </>}/>

      <Card pad={false}>
        {/* Toolbar */}
        <div style={{ display:"flex", alignItems:"center", gap:10, padding:"12px 16px", borderBottom:"1px solid var(--border-2)" }}>
          <Input value={q} onChange={setQ} placeholder="Search sites by name or domain…" icon={<I.search/>} style={{ flex:1, maxWidth:380 }}/>
          <Select value={group} onChange={setGroup} options={["All groups", ...GROUPS.map(g=>g.name)]}/>
          <Select value={status} onChange={setStatus} options={["All","Online","Offline","Pending"]}/>
          <div style={{ flex:1 }}/>
          {sel.size > 0 ? (
            <>
              <span style={{ fontSize:12, color:"var(--text-3)" }}>{sel.size} selected</span>
              <Btn kind="secondary" size="sm" icon={<I.sync s={13}/>}>Resync</Btn>
              <Btn kind="secondary" size="sm">Move to group</Btn>
              <Btn kind="danger" size="sm" icon={<I.trash/>}>Remove</Btn>
            </>
          ) : (
            <span style={{ fontSize:12, color:"var(--text-3)" }}>{rows.length} of {SITES.length}</span>
          )}
        </div>

        {/* Region tabs — КРОК 4 */}
        <div style={{ display:"flex", gap:2, padding:"8px 16px", borderBottom:"1px solid var(--border-2)", background:"var(--panel-2)" }}>
          {REGIONS.map(r => (
            <button key={r} onClick={()=>setRegion(r)} style={{
              background: region===r ? "var(--panel)" : "transparent",
              border: region===r ? "1px solid var(--border)" : "1px solid transparent",
              color: region===r ? "var(--text)" : "var(--text-3)",
              padding:"4px 12px", borderRadius:"var(--radius)", fontSize:12,
              fontWeight: region===r ? 600 : 500, cursor:"pointer", fontFamily:"inherit"
            }}>{r}</button>
          ))}
        </div>

        {/* Add site inline form — КРОК 5 */}
        {showAdd && <AddSiteForm onAdd={()=>setShowAdd(false)}/>}

        {/* Table */}
        <div style={{ overflow:"auto" }}>
          <table style={{ width:"100%", borderCollapse:"collapse", fontSize:13 }}>
            <thead>
              <tr style={{ color:"var(--text-3)", fontSize:11, textTransform:"uppercase", letterSpacing:".06em" }}>
                <th style={th(36)}>
                  <input type="checkbox" checked={allChecked}
                    onChange={e=>setSel(new Set(e.target.checked ? rows.map(r=>r.id) : []))}/>
                </th>
                <th style={th()}>Site</th>
                <th style={th()}>Group</th>
                <th style={th()}>Plan</th>
                <th style={th()}>Status</th>
                <th style={th()}>Plugin</th>
                <th style={th()}>Contacts</th>
                <th style={th()}>Last seen</th>
                <th style={th(40)}></th>
              </tr>
            </thead>
            <tbody>
              {rows.map(s => (
                <tr key={s.id} onClick={()=>goto("site:"+s.id)}
                  style={{ borderTop:"1px solid var(--border-2)", cursor:"pointer" }}>
                  <td style={td()} onClick={e=>e.stopPropagation()}>
                    <input type="checkbox" checked={sel.has(s.id)}
                      onChange={()=>{ const n=new Set(sel); n.has(s.id)?n.delete(s.id):n.add(s.id); setSel(n); }}/>
                  </td>
                  <td style={td()}>
                    <div style={{ display:"flex", alignItems:"center", gap:10 }}>
                      <SiteFavicon name={s.title}/>
                      <div>
                        <div style={{ fontWeight:500 }}>{s.title}</div>
                        <div style={{ color:"var(--text-3)", fontSize:11, fontFamily:"var(--font-mono)" }}>{s.name}</div>
                      </div>
                    </div>
                  </td>
                  <td style={td()}><GroupChip name={s.group}/></td>
                  <td style={td()}><PlanPill plan={s.plan}/></td>
                  <td style={td()}><StatusPill s={s.status}/></td>
                  <td style={{...td(), fontFamily:"var(--font-mono)", fontSize:12, color:"var(--text-2)"}}>{s.plugin}</td>
                  <td style={{...td(), fontFamily:"var(--font-mono)", fontSize:12}}>{s.contacts}</td>
                  <td style={{...td(), color:"var(--text-3)", fontSize:12}}>{s.lastSeen}</td>
                  <td style={td()} onClick={e=>e.stopPropagation()}>
                    <button style={iconBtn}><I.more/></button>
                  </td>
                </tr>
              ))}
              {rows.length === 0 && (
                <tr><td colSpan={9} style={{ padding:"32px 20px", textAlign:"center", color:"var(--text-3)", fontSize:13 }}>No sites match the current filters</td></tr>
              )}
            </tbody>
          </table>
        </div>
      </Card>
    </div>
  );
}

window.Screen_Sites = Sites;

/* global React, I, UI */
const { useState } = React;
const { Btn, Card, Input } = UI;
const { PageHead, ActivityRow, Select } = window.SHARED;

function Activity() {
  const { ACTIVITY } = window.CRM_DATA;
  const [q, setQ]     = useState("");
  const [who, setWho] = useState("Everyone");
  const [kind, setKind] = useState("All");

  const rows = ACTIVITY.filter(a =>
    (who==="Everyone" || a.who===who.toLowerCase()) &&
    (kind==="All"
      || (kind==="Errors"   && a.kind==="err")
      || (kind==="Warnings" && a.kind==="warn")
      || (kind==="Info"     && a.kind==="info")
      || (kind==="Success"  && a.kind==="ok")) &&
    (q==="" || a.target.toLowerCase().includes(q.toLowerCase()) || a.action.toLowerCase().includes(q.toLowerCase()))
  );

  return (
    <div style={{ display:"flex", flexDirection:"column", gap:20 }}>
      <PageHead title="Activity log" subtitle="Everything that happened across your sites and team."
        actions={<Btn kind="secondary" size="md" icon={<I.download/>}>Export log</Btn>}/>

      <Card pad={false}>
        <div style={{ display:"flex", alignItems:"center", gap:10, padding:"12px 16px", borderBottom:"1px solid var(--border-2)" }}>
          <Input value={q} onChange={setQ} placeholder="Search log…" icon={<I.search/>} style={{ flex:1, maxWidth:380 }}/>
          <Select value={who} onChange={setWho} options={["Everyone","Anna","Sasha","David","System"]}/>
          <Select value={kind} onChange={setKind} options={["All","Success","Info","Warnings","Errors"]}/>
          <div style={{ flex:1 }}/>
          <span style={{ fontSize:12, color:"var(--text-3)" }}>{rows.length} events</span>
        </div>
        <div>
          {rows.map((a,i) => <ActivityRow key={i} {...a}/>)}
          {rows.length===0 && (
            <div style={{ padding:"32px 20px", textAlign:"center", color:"var(--text-3)", fontSize:13 }}>No events match the current filters</div>
          )}
        </div>
      </Card>
    </div>
  );
}

window.Screen_Activity = Activity;

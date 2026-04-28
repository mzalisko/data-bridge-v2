/* global React, I, UI */
const { useState } = React;
const { Btn, Card, Pill, Avatar, Input, Toggle, MiniSpark } = UI;

// ===== PageHead =====
function PageHead({ title, subtitle, actions, breadcrumb }) {
  return (
    <div style={{ display:"flex", alignItems:"flex-end", justifyContent:"space-between", gap:16 }}>
      <div>
        {breadcrumb && <div style={{ fontSize:12, color:"var(--text-3)", marginBottom:6 }}>{breadcrumb}</div>}
        <h1 style={{ margin:0, fontSize:22, fontWeight:600, letterSpacing:"-0.01em" }}>{title}</h1>
        {subtitle && <p style={{ margin:"4px 0 0", color:"var(--text-3)", fontSize:13 }}>{subtitle}</p>}
      </div>
      {actions && <div style={{ display:"flex", gap:8 }}>{actions}</div>}
    </div>
  );
}

// ===== SectionHead =====
function SectionHead({ title, right }) {
  return (
    <div style={{ display:"flex", alignItems:"center", justifyContent:"space-between", padding:"14px 20px" }}>
      <h3 style={{ margin:0, fontSize:13, fontWeight:600 }}>{title}</h3>
      {right}
    </div>
  );
}

// ===== StatusDot / StatusPill =====
function StatusDot({ s }) {
  const c = s==="Online" ? "var(--success)" : s==="Pending" ? "var(--warning)" : s==="Offline" ? "var(--danger)" : "var(--muted)";
  return <span style={{ display:"inline-block", width:7, height:7, borderRadius:99, background:c, flexShrink:0 }}/>;
}
function StatusPill({ s }) {
  const k = s==="Online" ? "success" : s==="Pending" ? "warning" : s==="Offline" ? "danger" : "neutral";
  return <Pill kind={k}><StatusDot s={s}/> {s}</Pill>;
}

// ===== PlanPill =====
function PlanPill({ plan }) {
  const map = { Enterprise:"accent", Pro:"neutral", Starter:"neutral", Trial:"warning" };
  return <Pill kind={map[plan]||"neutral"}>{plan}</Pill>;
}

// ===== GroupChip =====
function GroupChip({ name }) {
  const g = window.CRM_DATA.GROUPS.find(x=>x.name===name);
  const c = g?.color || "var(--text-3)";
  return (
    <span style={{ display:"inline-flex", alignItems:"center", gap:6, fontSize:12, color:"var(--text-2)" }}>
      <span style={{ width:8, height:8, background:c, borderRadius:2, flexShrink:0 }}/>
      {name}
    </span>
  );
}

// ===== SiteFavicon: hash → oklch color =====
function SiteFavicon({ name, size=22 }) {
  let h=0; for (let i=0;i<name.length;i++) h=(h*31+name.charCodeAt(i))%360;
  return (
    <span style={{
      width:size, height:size, borderRadius:5,
      background:`oklch(0.94 0.04 ${h})`, color:`oklch(0.4 0.1 ${h})`,
      display:"inline-flex", alignItems:"center", justifyContent:"center",
      fontWeight:700, fontSize:size*0.5, fontFamily:"var(--font-mono)", flexShrink:0,
      border:"1px solid var(--border)"
    }}>{name[0].toUpperCase()}</span>
  );
}

// ===== ActivityRow =====
function ActivityRow({ when, who, action, target, kind }) {
  const dotColor = kind==="ok"?"var(--success)" : kind==="warn"?"var(--warning)" : kind==="err"?"var(--danger)" : "var(--accent)";
  return (
    <div style={{ display:"grid", gridTemplateColumns:"110px 1fr auto", alignItems:"center", gap:12, padding:"12px 20px", borderTop:"1px solid var(--border-2)" }}>
      <span style={{ color:"var(--text-3)", fontSize:12 }}>{when}</span>
      <div style={{ display:"flex", alignItems:"center", gap:8, fontSize:13 }}>
        <span style={{ width:6, height:6, borderRadius:99, background:dotColor, flexShrink:0 }}/>
        {who==="system"
          ? <span style={{ color:"var(--text-3)", fontFamily:"var(--font-mono)", fontSize:12 }}>system</span>
          : <Avatar name={who} size={20}/>}
        <span style={{ color:"var(--text-2)" }}>
          {who!=="system" && <b style={{ color:"var(--text)", fontWeight:500 }}>{who} </b>}
          {action}
        </span>
        <span style={{ fontWeight:500 }}>{target}</span>
      </div>
      <span style={{ color:"var(--text-3)", fontSize:11 }}>
        {kind==="err"?"error":kind==="warn"?"warning":kind==="ok"?"ok":"info"}
      </span>
    </div>
  );
}

// ===== Select =====
function Select({ value, onChange, options }) {
  return (
    <div style={{
      display:"inline-flex", alignItems:"center", gap:4, height:34, padding:"0 10px",
      background:"var(--panel)", border:"1px solid var(--border)", borderRadius:"var(--radius)",
      fontSize:13, boxShadow:"var(--shadow-sm)"
    }}>
      <select value={value} onChange={e=>onChange(e.target.value)} style={{
        border:0, background:"transparent", color:"var(--text)", fontSize:13, outline:"none",
        appearance:"none", paddingRight:14, cursor:"pointer"
      }}>
        {options.map(o => <option key={o}>{o}</option>)}
      </select>
      <I.chevron s={12} style={{ pointerEvents:"none", marginLeft:-14 }}/>
    </div>
  );
}

// ===== StatCard (dashboard) =====
function StatCard({ label, value, delta, kind="neutral", trend }) {
  const col = kind==="success"?"var(--success)" : kind==="warning"?"var(--warning)" : kind==="danger"?"var(--danger)" : "var(--accent)";
  return (
    <Card style={{ padding:18 }}>
      <div style={{ color:"var(--text-3)", fontSize:11, fontWeight:500, textTransform:"uppercase", letterSpacing:".06em" }}>{label}</div>
      <div style={{ display:"flex", alignItems:"flex-end", justifyContent:"space-between", marginTop:8, gap:8 }}>
        <span style={{ fontSize:26, fontWeight:600, letterSpacing:"-0.02em" }}>{value}</span>
        {trend && <MiniSpark data={trend} color={col}/>}
      </div>
      <div style={{ color:"var(--text-3)", fontSize:12, marginTop:6 }}>{delta}</div>
    </Card>
  );
}

// ===== SiteData (contact list + detail panel) =====
const iconBtn = { background:"transparent", border:0, color:"var(--text-3)", cursor:"pointer", padding:6, borderRadius:6 };

function SiteData({ data }) {
  const [openId, setOpenId] = useState(data.contacts[0]?.id);
  const sel = data.contacts.find(c=>c.id===openId) || data.contacts[0];
  return (
    <div style={{ display:"grid", gridTemplateColumns:"260px 1fr" }}>
      <div style={{ borderRight:"1px solid var(--border-2)", padding:"12px" }}>
        <div style={{ display:"flex", alignItems:"center", justifyContent:"space-between", padding:"4px 6px 8px" }}>
          <span style={{ fontSize:11, color:"var(--text-3)", textTransform:"uppercase", letterSpacing:".06em", fontWeight:600 }}>Contacts</span>
          <button style={iconBtn}><I.plus/></button>
        </div>
        {data.contacts.map(c => (
          <button key={c.id} onClick={()=>setOpenId(c.id)} style={{
            width:"100%", textAlign:"left",
            background: openId===c.id ? "var(--accent-2)" : "transparent",
            color: openId===c.id ? "var(--accent-text)" : "var(--text-2)",
            border:0, borderRadius:8, padding:"10px 10px", fontSize:13, cursor:"pointer", fontFamily:"inherit",
            display:"flex", flexDirection:"column", gap:2, marginBottom:2
          }}>
            <span style={{ fontWeight:500 }}>{c.label}</span>
            <span style={{ fontSize:11, opacity:0.7, fontFamily:"var(--font-mono)" }}>{c.phones[0]}</span>
          </button>
        ))}
      </div>

      <div style={{ padding:20, display:"flex", flexDirection:"column", gap:18 }}>
        <div style={{ display:"flex", alignItems:"center", justifyContent:"space-between" }}>
          <h3 style={{ margin:0, fontSize:15, fontWeight:600 }}>{sel.label}</h3>
          <div style={{ display:"flex", gap:6 }}>
            <Btn kind="secondary" size="sm" icon={<I.copy/>}>Copy snippet</Btn>
            <Btn kind="danger" size="sm" icon={<I.trash/>}>Delete</Btn>
          </div>
        </div>
        <FieldGroup label="Phones">
          {sel.phones.map((p,i)=>(
            <RowField key={i} value={p} mono badge={i===0?<Pill kind="accent">Primary</Pill>:null}/>
          ))}
          <AddBtn>Add phone</AddBtn>
        </FieldGroup>
        <Field label="Email" value={sel.email}/>
        <Field label="Address" value={sel.address}/>
        <FieldGroup label="Social media">
          {Object.entries(sel.socials).map(([k,v])=>(
            <RowField key={k} icon={<ChannelChip kind={k}/>} value={v}/>
          ))}
          <AddBtn>Add social link</AddBtn>
        </FieldGroup>
        <FieldGroup label="Messengers">
          {Object.entries(sel.messengers).map(([k,v])=>(
            <RowField key={k} icon={<ChannelChip kind={k}/>} value={v}/>
          ))}
          {Object.keys(sel.messengers).length===0 && <div style={{ color:"var(--text-3)", fontSize:12 }}>No messengers configured.</div>}
          <AddBtn>Add messenger</AddBtn>
        </FieldGroup>

        <div style={{ display:"flex", justifyContent:"space-between", alignItems:"center", paddingTop:12, borderTop:"1px solid var(--border-2)", marginTop:6 }}>
          <span style={{ fontSize:12, color:"var(--text-3)" }}>Last updated {sel.updated} · auto-pushed to site</span>
          <div style={{ display:"flex", gap:8 }}>
            <Btn kind="secondary" size="md">Cancel</Btn>
            <Btn kind="primary" size="md">Save & push</Btn>
          </div>
        </div>
      </div>
    </div>
  );
}

function Field({ label, value }) {
  return (
    <label style={{ display:"flex", flexDirection:"column", gap:6 }}>
      <span style={{ fontSize:11, color:"var(--text-3)", fontWeight:500, textTransform:"uppercase", letterSpacing:".05em" }}>{label}</span>
      <Input value={value} onChange={()=>{}}/>
    </label>
  );
}
function FieldGroup({ label, children }) {
  return (
    <div style={{ display:"flex", flexDirection:"column", gap:8 }}>
      <span style={{ fontSize:11, color:"var(--text-3)", fontWeight:500, textTransform:"uppercase", letterSpacing:".05em" }}>{label}</span>
      {children}
    </div>
  );
}
function RowField({ value, icon, mono, badge }) {
  return (
    <div style={{ display:"flex", gap:8, alignItems:"center" }}>
      <Input value={value} onChange={()=>{}} icon={icon} mono={mono} style={{ flex:1 }}/>
      {badge}
      <button style={iconBtn}><I.copy/></button>
      <button style={iconBtn}><I.trash/></button>
    </div>
  );
}
function AddBtn({ children }) {
  return (
    <button style={{
      background:"transparent", border:"1px dashed var(--border)", color:"var(--text-3)",
      padding:"7px 10px", borderRadius:"var(--radius)", fontSize:12, cursor:"pointer",
      display:"inline-flex", alignItems:"center", gap:6, alignSelf:"flex-start", fontFamily:"inherit"
    }}><I.plus s={12}/> {children}</button>
  );
}
function ChannelChip({ kind }) {
  const map = {
    facebook:  { Ico: I.fb, c:"#1877f2" }, instagram: { Ico: I.ig, c:"#c2185b" },
    telegram:  { Ico: I.tg, c:"#229ed9" }, linkedin:  { Ico: I.li, c:"#0a66c2" },
    x:         { Ico: I.x,  c:"var(--text)" }, whatsapp: { Ico: I.wa, c:"#25d366" },
    viber:     { Ico: I.vb, c:"#7360f2" }
  }[kind];
  if (!map) return null;
  return (
    <span title={kind} style={{
      width:22, height:22, borderRadius:6, display:"inline-flex", alignItems:"center", justifyContent:"center",
      background:"var(--panel-2)", color:map.c, border:"1px solid var(--border)"
    }}><map.Ico s={12}/></span>
  );
}

window.SHARED = { PageHead, SectionHead, StatusDot, StatusPill, PlanPill, GroupChip, SiteFavicon, ActivityRow, Select, StatCard, SiteData, iconBtn };

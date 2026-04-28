/* global React, I, UI */
const { Btn } = UI;

function Topbar() {
  return (
    <div style={{
      display:"flex", alignItems:"center", gap:12,
      padding:"12px 32px", borderBottom:"1px solid var(--border)",
      background:"var(--panel)", position:"sticky", top:0, zIndex:10
    }}>
      <div style={{ display:"flex", alignItems:"center", gap:8, color:"var(--text-3)", fontSize:12 }}>
        <span style={{ display:"inline-block", width:7, height:7, borderRadius:99, background:"var(--success)" }}/>
        Workspace healthy · 8 of 10 sites online
      </div>
      <div style={{ flex:1 }}/>
      <Btn kind="ghost" size="sm" icon={<I.bell s={15}/>}>4</Btn>
      <Btn kind="secondary" size="sm" icon={<I.external/>}>Docs</Btn>
    </div>
  );
}

window.Topbar = Topbar;

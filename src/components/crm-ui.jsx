/* global React */
const { useState, useEffect, useRef, useMemo } = React;

// ---------- Icons (stroke-based, minimal) ----------
const I = {
  logo: (p) => (
    <svg viewBox="0 0 24 24" width={p.s||20} height={p.s||20} fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" {...p}>
      <path d="M4 7h7a5 5 0 0 1 5 5v0a5 5 0 0 1-5 5H4" />
      <path d="M20 17h-7a5 5 0 0 1-5-5v0a5 5 0 0 1 5-5h7" />
    </svg>
  ),
  dashboard: (p) => <svg viewBox="0 0 24 24" width={p.s||16} height={p.s||16} fill="none" stroke="currentColor" strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round" {...p}><rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/></svg>,
  contacts: (p) => <svg viewBox="0 0 24 24" width={p.s||16} height={p.s||16} fill="none" stroke="currentColor" strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round" {...p}><circle cx="12" cy="8" r="3.5"/><path d="M5 20c1.2-3.3 4-5 7-5s5.8 1.7 7 5"/></svg>,
  sync: (p) => <svg viewBox="0 0 24 24" width={p.s||16} height={p.s||16} fill="none" stroke="currentColor" strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round" {...p}><path d="M3 12a9 9 0 0 1 15.5-6.3L21 8"/><path d="M21 4v4h-4"/><path d="M21 12a9 9 0 0 1-15.5 6.3L3 16"/><path d="M3 20v-4h4"/></svg>,
  code: (p) => <svg viewBox="0 0 24 24" width={p.s||16} height={p.s||16} fill="none" stroke="currentColor" strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round" {...p}><path d="m9 8-5 4 5 4"/><path d="m15 8 5 4-5 4"/><path d="m13 5-2 14"/></svg>,
  search: (p) => <svg viewBox="0 0 24 24" width={p.s||16} height={p.s||16} fill="none" stroke="currentColor" strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round" {...p}><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>,
  plus: (p) => <svg viewBox="0 0 24 24" width={p.s||16} height={p.s||16} fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" {...p}><path d="M12 5v14M5 12h14"/></svg>,
  copy: (p) => <svg viewBox="0 0 24 24" width={p.s||14} height={p.s||14} fill="none" stroke="currentColor" strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round" {...p}><rect x="8" y="8" width="12" height="12" rx="2"/><path d="M16 8V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h2"/></svg>,
  check: (p) => <svg viewBox="0 0 24 24" width={p.s||14} height={p.s||14} fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" {...p}><path d="m4 12 5 5 11-12"/></svg>,
  edit: (p) => <svg viewBox="0 0 24 24" width={p.s||14} height={p.s||14} fill="none" stroke="currentColor" strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round" {...p}><path d="M4 20h4l11-11-4-4L4 16v4z"/><path d="m13.5 6.5 4 4"/></svg>,
  trash: (p) => <svg viewBox="0 0 24 24" width={p.s||14} height={p.s||14} fill="none" stroke="currentColor" strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round" {...p}><path d="M4 7h16"/><path d="M9 7V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/><path d="M6 7v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V7"/><path d="M10 11v6M14 11v6"/></svg>,
  more: (p) => <svg viewBox="0 0 24 24" width={p.s||16} height={p.s||16} fill="currentColor" {...p}><circle cx="5" cy="12" r="1.6"/><circle cx="12" cy="12" r="1.6"/><circle cx="19" cy="12" r="1.6"/></svg>,
  filter: (p) => <svg viewBox="0 0 24 24" width={p.s||14} height={p.s||14} fill="none" stroke="currentColor" strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round" {...p}><path d="M4 5h16l-6 8v5l-4 2v-7L4 5z"/></svg>,
  download: (p) => <svg viewBox="0 0 24 24" width={p.s||14} height={p.s||14} fill="none" stroke="currentColor" strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round" {...p}><path d="M12 4v11"/><path d="m7 11 5 5 5-5"/><path d="M5 20h14"/></svg>,
  chevron: (p) => <svg viewBox="0 0 24 24" width={p.s||14} height={p.s||14} fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" {...p}><path d="m6 9 6 6 6-6"/></svg>,
  close: (p) => <svg viewBox="0 0 24 24" width={p.s||16} height={p.s||16} fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" {...p}><path d="m6 6 12 12M18 6 6 18"/></svg>,
  mail: (p) => <svg viewBox="0 0 24 24" width={p.s||14} height={p.s||14} fill="none" stroke="currentColor" strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round" {...p}><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>,
  phone: (p) => <svg viewBox="0 0 24 24" width={p.s||14} height={p.s||14} fill="none" stroke="currentColor" strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round" {...p}><path d="M5 4h3l2 5-2 1a12 12 0 0 0 6 6l1-2 5 2v3a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2z"/></svg>,
  pin: (p) => <svg viewBox="0 0 24 24" width={p.s||14} height={p.s||14} fill="none" stroke="currentColor" strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round" {...p}><path d="M12 22s7-7.5 7-13a7 7 0 1 0-14 0c0 5.5 7 13 7 13z"/><circle cx="12" cy="9" r="2.5"/></svg>,
  external: (p) => <svg viewBox="0 0 24 24" width={p.s||12} height={p.s||12} fill="none" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round" {...p}><path d="M14 4h6v6"/><path d="M20 4 10 14"/><path d="M20 14v4a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h4"/></svg>,
  warning: (p) => <svg viewBox="0 0 24 24" width={p.s||14} height={p.s||14} fill="none" stroke="currentColor" strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round" {...p}><path d="M12 4 2 20h20L12 4z"/><path d="M12 10v5"/><circle cx="12" cy="18" r=".8" fill="currentColor"/></svg>,
  bell: (p) => <svg viewBox="0 0 24 24" width={p.s||16} height={p.s||16} fill="none" stroke="currentColor" strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round" {...p}><path d="M6 16V11a6 6 0 0 1 12 0v5l1.5 2H4.5L6 16z"/><path d="M10 20a2 2 0 0 0 4 0"/></svg>,
  fb: (p) => <svg viewBox="0 0 24 24" width={p.s||12} height={p.s||12} fill="currentColor" {...p}><path d="M13.5 21v-7.5h2.5l.4-3h-2.9V8.6c0-.9.3-1.5 1.6-1.5h1.5V4.4c-.3 0-1.2-.1-2.3-.1-2.3 0-3.8 1.4-3.8 3.9v2.2H8v3h2.5V21h3z"/></svg>,
  ig: (p) => <svg viewBox="0 0 24 24" width={p.s||12} height={p.s||12} fill="none" stroke="currentColor" strokeWidth="1.6" {...p}><rect x="3.5" y="3.5" width="17" height="17" rx="4.5"/><circle cx="12" cy="12" r="4"/><circle cx="17" cy="7" r="1" fill="currentColor"/></svg>,
  tg: (p) => <svg viewBox="0 0 24 24" width={p.s||12} height={p.s||12} fill="currentColor" {...p}><path d="M21 4 2.5 11.5c-.7.3-.7 1.3 0 1.5l4.5 1.4 1.7 5.4c.2.6 1 .8 1.4.3l2.5-2.7 4.7 3.4c.5.4 1.3.1 1.5-.5L22 5c.2-.7-.5-1.3-1-1zM9.7 14.7l-.4 4 1.7-2.4 4.6-5.5-5.9 3.9z"/></svg>,
  li: (p) => <svg viewBox="0 0 24 24" width={p.s||12} height={p.s||12} fill="currentColor" {...p}><path d="M5 4.5A1.7 1.7 0 1 1 5 8a1.7 1.7 0 0 1 0-3.5zM3.5 9.5h3v11h-3v-11zM9 9.5h2.9v1.6c.4-.8 1.5-1.8 3.2-1.8 3.4 0 4 2.2 4 5.1v6.1h-3v-5.4c0-1.3 0-3-1.8-3s-2.1 1.4-2.1 2.9v5.5H9v-11z"/></svg>,
  x:  (p) => <svg viewBox="0 0 24 24" width={p.s||12} height={p.s||12} fill="currentColor" {...p}><path d="M17.5 3h3l-6.6 7.6L21.5 21h-6l-4.4-5.8L6 21H3l7-8.1L2.5 3h6.1l4 5.4L17.5 3z"/></svg>,
  wa: (p) => <svg viewBox="0 0 24 24" width={p.s||12} height={p.s||12} fill="none" stroke="currentColor" strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round" {...p}><path d="M3.5 20.5 4.8 16A8 8 0 1 1 8 19.4l-4.5 1.1z"/><path d="M9 9c.4 1.5 1.4 2.7 2.6 3.5l1.1-1c.3-.3.7-.4 1-.2l1.6.8c.4.2.5.6.4 1l-.4 1c-.2.5-.7.8-1.3.7-2.2-.3-4.4-1.7-5.8-3.7C6.8 9.2 6.6 7.5 7 6.7c.2-.5.7-.8 1.2-.7l1.1.2c.4.1.6.4.6.8L10 8.6c0 .3-.3.5-.6.6L9 9z"/></svg>,
  vb: (p) => <svg viewBox="0 0 24 24" width={p.s||12} height={p.s||12} fill="none" stroke="currentColor" strokeWidth="1.6" {...p}><path d="M5 4h11a3 3 0 0 1 3 3v6a3 3 0 0 1-3 3h-2l-3 3v-3H7a2 2 0 0 1-2-2V4z"/><path d="M9 8h2M9 11h4"/></svg>,
};
window.I = I;

// ---------- Building blocks ----------

function Btn({ kind="primary", size="md", icon, children, onClick, style }) {
  const styles = {
    primary: { background: "var(--accent)", color: "#fff", border: "1px solid var(--accent)" },
    secondary: { background: "var(--panel)", color: "var(--text)", border: "1px solid var(--border)" },
    ghost: { background: "transparent", color: "var(--text-2)", border: "1px solid transparent" },
    danger: { background: "var(--panel)", color: "var(--danger)", border: "1px solid var(--border)" }
  }[kind];
  const sizes = {
    sm: { padding: "5px 10px", fontSize: 12, height: 28, gap: 6, borderRadius: "var(--radius)" },
    md: { padding: "7px 14px", fontSize: 13, height: 34, gap: 8, borderRadius: "var(--radius)" },
    lg: { padding: "10px 18px", fontSize: 14, height: 40, gap: 8, borderRadius: "var(--radius)" }
  }[size];
  return (
    <button onClick={onClick} style={{ display:"inline-flex", alignItems:"center", justifyContent:"center", fontWeight: 500, cursor:"pointer", whiteSpace:"nowrap", boxShadow: kind==="primary"?"none":"var(--shadow-sm)", transition:"transform .05s", ...styles, ...sizes, ...style }}>
      {icon}{children}
    </button>
  );
}

function Card({ children, style, pad=true }) {
  return (
    <div style={{
      background: "var(--panel)",
      border: "1px solid var(--border)",
      borderRadius: "var(--radius-lg)",
      boxShadow: "var(--shadow-sm)",
      padding: pad ? 20 : 0,
      ...style
    }}>{children}</div>
  );
}

function Pill({ kind="neutral", children, style }) {
  const map = {
    neutral: { bg: "var(--panel-2)", fg: "var(--text-2)", bd: "var(--border)" },
    accent:  { bg: "var(--accent-2)", fg: "var(--accent-text)", bd: "transparent" },
    success: { bg: "var(--success-bg)", fg: "var(--success)", bd: "transparent" },
    warning: { bg: "var(--warning-bg)", fg: "var(--warning)", bd: "transparent" },
    danger:  { bg: "var(--danger-bg)", fg: "var(--danger)", bd: "transparent" }
  }[kind];
  return (
    <span style={{
      display:"inline-flex", alignItems:"center", gap:6,
      background: map.bg, color: map.fg, border:`1px solid ${map.bd}`,
      borderRadius: 999, padding:"2px 8px", fontSize:11, fontWeight:500, lineHeight:1.5,
      ...style
    }}>{children}</span>
  );
}

function StatusDot({ status }) {
  const c = status === "Synced" ? "var(--success)" :
            status === "Pending" ? "var(--warning)" :
            status === "Conflict" ? "var(--warning)" :
            status === "Error" ? "var(--danger)" : "var(--muted)";
  return <span style={{ display:"inline-block", width:7, height:7, borderRadius:99, background:c }} />;
}

function StatusPill({ status }) {
  const kind = status === "Synced" ? "success" :
               status === "Pending" ? "warning" :
               status === "Conflict" ? "warning" :
               status === "Error" ? "danger" : "neutral";
  return <Pill kind={kind}><StatusDot status={status}/>{status}</Pill>;
}

function CrmBadge({ name }) {
  const map = {
    HubSpot:  { bg:"#fff1ee", fg:"#c2410c", dark:{bg:"#3a1a10", fg:"#fdba74"} },
    Bitrix24: { bg:"#e8f0ff", fg:"#1e40af", dark:{bg:"#101a3a", fg:"#93c5fd"} },
    KeyCRM:   { bg:"#eaf7ee", fg:"#15803d", dark:{bg:"#0c2a18", fg:"#86efac"} }
  }[name] || { bg:"var(--panel-2)", fg:"var(--text-2)", dark:{bg:"var(--panel-2)",fg:"var(--text-2)"} };
  // Detect dark mode by reading body class — approximate
  const isDark = document.querySelector('.vibe.dark');
  const c = isDark ? map.dark : map;
  return (
    <span style={{
      display:"inline-flex", alignItems:"center", gap:6,
      background: c.bg, color: c.fg, borderRadius: 999, padding:"2px 8px",
      fontSize:11, fontWeight:600, letterSpacing:".01em"
    }}>
      <span style={{ width:5, height:5, background:"currentColor", borderRadius:99, opacity:.85 }}/>
      {name}
    </span>
  );
}

function Avatar({ name, size=32 }) {
  const initials = name.split(/\s+/).slice(0,2).map(w=>w[0]).join("").toUpperCase();
  // Stable color from name
  let h = 0; for (let i=0;i<name.length;i++) h = (h*31 + name.charCodeAt(i)) % 360;
  return (
    <span style={{
      width:size, height:size, borderRadius:99,
      background:`oklch(0.92 0.04 ${h})`, color:`oklch(0.35 0.08 ${h})`,
      display:"inline-flex", alignItems:"center", justifyContent:"center",
      fontWeight:600, fontSize: size*0.38, flexShrink:0,
      border:"1px solid var(--border)"
    }}>{initials}</span>
  );
}

function Toggle({ on, onChange }) {
  return (
    <button onClick={()=>onChange(!on)} style={{
      width:34, height:20, borderRadius:99, padding:2, border:0, cursor:"pointer",
      background: on ? "var(--accent)" : "var(--border)",
      transition:"background .15s", display:"inline-flex"
    }}>
      <span style={{
        width:16, height:16, borderRadius:99, background:"#fff",
        transform: on ? "translateX(14px)" : "translateX(0)",
        transition:"transform .15s", boxShadow:"0 1px 2px rgba(0,0,0,.2)"
      }}/>
    </button>
  );
}

function Input({ value, onChange, placeholder, icon, style, mono, type="text" }) {
  return (
    <div style={{
      display:"flex", alignItems:"center", gap:8,
      background:"var(--panel)", border:"1px solid var(--border)",
      borderRadius:"var(--radius)", padding:"0 10px", height:34,
      ...style
    }}>
      {icon && <span style={{ color:"var(--text-3)", display:"inline-flex" }}>{icon}</span>}
      <input
        type={type}
        value={value} onChange={e=>onChange?.(e.target.value)} placeholder={placeholder}
        style={{
          flex:1, border:0, background:"transparent", color:"var(--text)",
          fontSize:13, outline:"none", height:"100%",
          fontFamily: mono ? "var(--font-mono)" : "inherit"
        }}
      />
    </div>
  );
}

function CopyBtn({ text, label="Copy", size="sm" }) {
  const [done, setDone] = useState(false);
  return (
    <Btn kind="secondary" size={size} icon={done ? <I.check/> : <I.copy/>} onClick={()=>{
      navigator.clipboard?.writeText(text); setDone(true); setTimeout(()=>setDone(false),1200);
    }}>{done ? "Copied" : label}</Btn>
  );
}

function CodeBlock({ code, lang="php" }) {
  // Very light tokeniser — just colorize strings, comments, keywords for PHP
  const html = useMemo(() => {
    let s = code
      .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    s = s.replace(/(\/\/[^\n]*|#[^\n]*)/g, '<span class="c-com">$1</span>');
    s = s.replace(/('([^'\\]|\\.)*'|"([^"\\]|\\.)*")/g, '<span class="c-str">$1</span>');
    s = s.replace(/\b(echo|return|if|else|foreach|function|use|new|null|true|false|class|public|private|static)\b/g, '<span class="c-kw">$1</span>');
    s = s.replace(/(\$[A-Za-z_][\w]*)/g, '<span class="c-var">$1</span>');
    s = s.replace(/(&lt;\?php|\?&gt;)/g, '<span class="c-tag">$1</span>');
    s = s.replace(/(\[databridge[^\]]*\])/g, '<span class="c-sc">$1</span>');
    return s;
  }, [code]);
  return (
    <pre className="db-scroll" style={{
      margin:0, padding:"14px 16px", background:"var(--panel-2)",
      border:"1px solid var(--border)", borderRadius:"var(--radius)",
      overflow:"auto", fontFamily:"var(--font-mono)", fontSize:12.5, lineHeight:1.65,
      color:"var(--text)"
    }}>
      <style>{`
        .c-com { color: var(--text-3); font-style: italic; }
        .c-str { color: oklch(0.55 0.14 145); }
        .vibe.dark .c-str { color: oklch(0.78 0.14 145); }
        .c-kw  { color: oklch(0.5 0.18 290); font-weight:600; }
        .vibe.dark .c-kw { color: oklch(0.78 0.16 290); }
        .c-var { color: oklch(0.55 0.16 30); }
        .vibe.dark .c-var { color: oklch(0.8 0.13 30); }
        .c-tag { color: var(--text-3); }
        .c-sc  { color: var(--accent); font-weight: 600; }
      `}</style>
      <code dangerouslySetInnerHTML={{__html: html}}/>
    </pre>
  );
}

function MiniSpark({ data, w=120, h=32, color="var(--accent)" }) {
  const max = Math.max(...data), min = Math.min(...data);
  const pts = data.map((v,i) => {
    const x = (i / (data.length-1)) * w;
    const y = h - ((v-min)/(max-min || 1)) * h;
    return `${x},${y}`;
  }).join(" ");
  return (
    <svg width={w} height={h} viewBox={`0 0 ${w} ${h}`}>
      <polyline points={pts} fill="none" stroke={color} strokeWidth="1.5" strokeLinejoin="round" strokeLinecap="round"/>
    </svg>
  );
}

window.UI = { Btn, Card, Pill, StatusDot, StatusPill, CrmBadge, Avatar, Toggle, Input, CopyBtn, CodeBlock, MiniSpark };

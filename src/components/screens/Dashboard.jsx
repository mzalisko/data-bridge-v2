/* global React, I, UI */
const { Btn, Card } = UI;
const { PageHead, SectionHead, StatCard, ActivityRow, SiteFavicon } = window.SHARED;

function Dashboard({ goto }) {
  const { STATS, ACTIVITY, SITES } = window.CRM_DATA;
  const trend = [60,72,68,80,74,88,92,98,104,110,118,124,130,128];

  return (
    <div style={{ display:"flex", flexDirection:"column", gap:24 }}>
      <PageHead title="Overview" subtitle="All sites, groups, and team activity in one place."
        actions={<>
          <Btn kind="secondary" size="md" icon={<I.download/>}>Export</Btn>
          <Btn kind="primary" size="md" icon={<I.plus/>}>Add site</Btn>
        </>}/>

      <div style={{ display:"grid", gridTemplateColumns:"repeat(4, 1fr)", gap:16 }}>
        <StatCard label="Sites" value={STATS.sites} delta={`${STATS.online} online`} kind="success" trend={trend}/>
        <StatCard label="Total contacts" value={STATS.contacts.toLocaleString()} delta="+62 this week" trend={[200,240,260,310,380,420,500,600,720,840,950,1100,1280,1333]}/>
        <StatCard label="Conflicts" value={STATS.conflicts} delta="needs review" kind="warning" trend={[1,2,3,2,3,3,3]}/>
        <StatCard label="Team members" value={STATS.members} delta="2 invited"/>
      </div>

      <div style={{ display:"grid", gridTemplateColumns:"1.7fr 1fr", gap:20 }}>
        <Card pad={false}>
          <SectionHead title="Recent activity" right={
            <a style={{ color:"var(--accent)", fontSize:12, fontWeight:500, cursor:"pointer" }} onClick={()=>goto("activity")}>View all</a>
          }/>
          <div>
            {ACTIVITY.slice(0,6).map((a,i) => <ActivityRow key={i} {...a}/>)}
          </div>
        </Card>

        <div style={{ display:"flex", flexDirection:"column", gap:20 }}>
          <Card pad={false}>
            <SectionHead title="Plan mix"/>
            <div style={{ padding:"4px 20px 20px", display:"flex", flexDirection:"column", gap:10 }}>
              {Object.entries(STATS.plansBreakdown).map(([plan, n]) => {
                const pct = Math.round(n / STATS.sites * 100);
                return (
                  <div key={plan}>
                    <div style={{ display:"flex", justifyContent:"space-between", fontSize:12, marginBottom:4 }}>
                      <span style={{ color:"var(--text-2)" }}>{plan}</span>
                      <span style={{ color:"var(--text-3)", fontFamily:"var(--font-mono)" }}>{n} · {pct}%</span>
                    </div>
                    <div style={{ height:6, borderRadius:99, background:"var(--panel-2)", overflow:"hidden" }}>
                      <div style={{
                        width:`${pct}%`, height:"100%",
                        background: plan==="Enterprise" ? "var(--accent)"
                                  : plan==="Pro" ? "oklch(0.65 0.14 264)"
                                  : plan==="Starter" ? "oklch(0.7 0.05 264)"
                                  : "var(--warning)"
                      }}/>
                    </div>
                  </div>
                );
              })}
            </div>
          </Card>

          <Card pad={false}>
            <SectionHead title="Top sites" right={
              <a style={{ color:"var(--accent)", fontSize:12, fontWeight:500, cursor:"pointer" }} onClick={()=>goto("sites")}>All sites</a>
            }/>
            <div>
              {SITES.slice().sort((a,b)=>b.contacts-a.contacts).slice(0,4).map(s => (
                <div key={s.id} style={{ display:"flex", alignItems:"center", gap:12, padding:"12px 20px", borderTop:"1px solid var(--border-2)", cursor:"pointer" }}
                  onClick={()=>goto("site:"+s.id)}>
                  <SiteFavicon name={s.title}/>
                  <div style={{ flex:1, minWidth:0 }}>
                    <div style={{ fontSize:13, fontWeight:500, whiteSpace:"nowrap", overflow:"hidden", textOverflow:"ellipsis" }}>{s.title}</div>
                    <div style={{ fontSize:11, color:"var(--text-3)", fontFamily:"var(--font-mono)" }}>{s.name}</div>
                  </div>
                  <span style={{ fontSize:12, color:"var(--text-2)", fontFamily:"var(--font-mono)" }}>{s.contacts}</span>
                </div>
              ))}
            </div>
          </Card>
        </div>
      </div>
    </div>
  );
}

window.Screen_Dashboard = Dashboard;

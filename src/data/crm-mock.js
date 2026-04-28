/* global React */
// CRM-side mock data
const SITES = [
  { id:"s_001", name:"northwind.co", title:"Northwind Co.", group:"Agency: Beacon", plan:"Pro", status:"Online", lastSeen:"2 min ago", plugin:"v2.4.1", contacts: 124, phones: 18, conflicts:0, addedBy:"anna", region:"EU" },
  { id:"s_002", name:"palomastudio.com", title:"Paloma Studio", group:"Agency: Beacon", plan:"Pro", status:"Online", lastSeen:"5 min ago", plugin:"v2.4.1", contacts: 88, phones: 12, conflicts:1, addedBy:"anna", region:"EU" },
  { id:"s_003", name:"sundown.shop", title:"Sundown Ceramics", group:"Direct clients", plan:"Starter", status:"Online", lastSeen:"12 min ago", plugin:"v2.4.0", contacts: 41, phones: 6, conflicts:0, addedBy:"david", region:"AS" },
  { id:"s_004", name:"farosupplies.es", title:"Faro Supplies", group:"Agency: Cobalt", plan:"Pro", status:"Online", lastSeen:"22 min ago", plugin:"v2.4.1", contacts: 210, phones: 34, conflicts:0, addedBy:"david", region:"EU" },
  { id:"s_005", name:"levant.digital", title:"Levant Digital", group:"Direct clients", plan:"Starter", status:"Offline", lastSeen:"3 d ago", plugin:"v2.3.7", contacts: 19, phones: 3, conflicts:0, addedBy:"anna", region:"ME" },
  { id:"s_006", name:"maisonclair.fr", title:"Maison Clair", group:"Agency: Cobalt", plan:"Enterprise", status:"Online", lastSeen:"1 min ago", plugin:"v2.4.1", contacts: 612, phones: 91, conflicts:2, addedBy:"sasha", region:"EU" },
  { id:"s_007", name:"kumotype.jp", title:"Kumo Type", group:"Direct clients", plan:"Pro", status:"Online", lastSeen:"40 min ago", plugin:"v2.4.1", contacts: 73, phones: 9, conflicts:0, addedBy:"sasha", region:"AS" },
  { id:"s_008", name:"atlasco.io", title:"Atlas & Co.", group:"Agency: Beacon", plan:"Pro", status:"Online", lastSeen:"7 min ago", plugin:"v2.4.1", contacts: 158, phones: 22, conflicts:0, addedBy:"anna", region:"NA" },
  { id:"s_009", name:"helio.market", title:"Helio Market", group:"Trial", plan:"Trial", status:"Online", lastSeen:"31 min ago", plugin:"v2.4.1", contacts: 8, phones: 2, conflicts:0, addedBy:"david", region:"NA" },
  { id:"s_010", name:"verde.tea", title:"Verde Tea", group:"Trial", plan:"Trial", status:"Pending", lastSeen:"never", plugin:"—", contacts: 0, phones: 0, conflicts:0, addedBy:"sasha", region:"NA" }
];

const GROUPS = [
  { id:"g_beacon", name:"Agency: Beacon", color:"#4f46e5", sites: 3, contacts: 370, owner:"Beacon Agency", desc:"All client sites we manage on retainer." },
  { id:"g_cobalt", name:"Agency: Cobalt", color:"#0ea5e9", sites: 2, contacts: 822, owner:"Cobalt Studio", desc:"Cobalt's monthly-managed sites." },
  { id:"g_direct", name:"Direct clients", color:"#10b981", sites: 3, contacts: 133, owner:"In-house", desc:"Sites we sold the plugin to directly." },
  { id:"g_trial",  name:"Trial",          color:"#f59e0b", sites: 2, contacts: 8,   owner:"In-house", desc:"Free trial accounts \u2014 expire in 14 days." }
];

const SITE_DATA = {
  s_006: {
    contacts: [
      { id:"c_1", label:"Main reception", phones:["+33 1 42 86 82 00","+33 6 12 34 56 78"], email:"hello@maisonclair.fr",
        socials:{ instagram:"@maison.clair", facebook:"/maisonclair", linkedin:"/company/maisonclair" },
        messengers:{ whatsapp:"+33 6 12 34 56 78" }, address:"24 rue de Rivoli, Paris", updated:"2 min ago" },
      { id:"c_2", label:"Press inquiries", phones:["+33 1 42 86 82 11"], email:"press@maisonclair.fr",
        socials:{ instagram:"@maison.clair.press" }, messengers:{}, address:"24 rue de Rivoli, Paris", updated:"3 d ago" },
      { id:"c_3", label:"Boutique Marais", phones:["+33 1 48 87 19 02"], email:"marais@maisonclair.fr",
        socials:{ instagram:"@maisonclair.marais" }, messengers:{ whatsapp:"+33 6 88 19 02 11" }, address:"15 rue Vieille du Temple, Paris", updated:"yesterday" }
    ]
  }
};

const ACTIVITY = [
  { when:"2 min ago", who:"anna", action:"updated phone on", target:"maisonclair.fr · Main reception", kind:"info" },
  { when:"14 min ago", who:"sasha", action:"resolved conflict on", target:"palomastudio.com", kind:"ok" },
  { when:"40 min ago", who:"system", action:"plugin updated to v2.4.1 on", target:"kumotype.jp", kind:"info" },
  { when:"1 h ago", who:"david", action:"added site", target:"helio.market", kind:"ok" },
  { when:"2 h ago", who:"system", action:"sync failed on", target:"levant.digital", kind:"err" },
  { when:"3 h ago", who:"anna", action:"invited member", target:"sasha@databridge.dev", kind:"info" },
  { when:"yesterday", who:"sasha", action:"created group", target:"Trial", kind:"info" },
  { when:"yesterday", who:"david", action:"deleted contact on", target:"farosupplies.es · Old reception", kind:"warn" },
  { when:"2 d ago", who:"anna", action:"rotated API key for", target:"northwind.co", kind:"info" },
  { when:"2 d ago", who:"system", action:"weekly digest sent to", target:"3 members", kind:"info" }
];

const STATS = {
  sites: 10,
  online: 8,
  contacts: 1333,
  conflicts: 3,
  members: 4,
  plansBreakdown: { Enterprise: 1, Pro: 5, Starter: 2, Trial: 2 }
};

window.CRM_DATA = { SITES, GROUPS, SITE_DATA, ACTIVITY, STATS };

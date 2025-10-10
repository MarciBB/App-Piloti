import { createClient } from "@/lib/supabase/server";

export default async function TeamPage() {
  const supabase = createClient();
  const { data: users } = await supabase.from("users").select("id, full_name, email, role");
  return (
    <div className="container">
      <h1 style={{fontSize:"1.5rem", fontWeight:700, marginBottom:"1rem"}}>Team Bertoldi Boats</h1>
      <div style={{display:"grid", gap:12, gridTemplateColumns:"repeat(auto-fill, minmax(220px, 1fr))"}}>
        {(users ?? []).map((u:any)=>(
          <div key={u.id} className="card">
            <div style={{fontWeight:600}}>{u.full_name}</div>
            <div style={{color:"#475569", fontSize:14}}>{u.email}</div>
            <span style={{fontSize:12, background:"#dbeafe", color:"#1d4ed8", padding:"2px 6px", borderRadius:6}}>{u.role}</span>
          </div>
        ))}
        {!users?.length && <p>Nessun membro registrato.</p>}
      </div>
    </div>
  );
}

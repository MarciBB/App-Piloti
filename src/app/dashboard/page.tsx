import { createClient } from "@/lib/supabase/server";

export default async function DashboardPage() {
  const supabase = createClient();
  const { data: { user } } = await supabase.auth.getUser();
  const { data: shifts } = await supabase.from("shifts").select("*").limit(5);
  return (
    <div className="container">
      <h1 style={{fontSize:"1.5rem", fontWeight:700}}>Benvenuto {user?.email}</h1>
      <h2 style={{fontWeight:600, marginTop:"1rem"}}>Prossimi turni</h2>
      <ul>
        {(shifts ?? []).map((s:any)=>(
          <li key={s.id} className="card" style={{marginTop:8}}>
            <div><b>Data:</b> {s.date}</div>
            <div><b>Orario:</b> {s.start_time} - {s.end_time}</div>
          </li>
        ))}
        {!shifts?.length && <p>Nessun turno assegnato.</p>}
      </ul>
    </div>
  );
}

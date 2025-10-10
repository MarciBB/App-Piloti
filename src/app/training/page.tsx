import { createClient } from "@/lib/supabase/server";

export default async function TrainingPage() {
  const supabase = createClient();
  const { data: trainings } = await supabase.from("training").select("*");
  return (
    <div className="container">
      <h1 style={{fontSize:"1.5rem", fontWeight:700, marginBottom:"1rem"}}>Formazione</h1>
      {(trainings ?? []).length ? (
        <div style={{display:"grid", gap:12, gridTemplateColumns:"repeat(auto-fill, minmax(220px, 1fr))"}}>
          {trainings!.map((t:any)=>(
            <div key={t.id} className="card">
              <div style={{fontWeight:600}}>{t.title}</div>
              <div style={{color:"#475569", fontSize:14}}>{t.description}</div>
            </div>
          ))}
        </div>
      ) : <p>Nessun corso disponibile.</p>}
    </div>
  );
}

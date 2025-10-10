import { createClient } from "@/lib/supabase/server";

export default async function MyBoatPage() {
  const supabase = createClient();
  const { data: boats } = await supabase.from("boats").select("*");
  return (
    <div className="container">
      <h1 style={{fontSize:"1.5rem", fontWeight:700}}>La tua barca</h1>
      {(boats ?? []).length ? (
        <ul>
          {boats!.map((b:any)=>(
            <li key={b.id} className="card" style={{marginTop:8}}>
              <div><b>Nome:</b> {b.name}</div>
              <div><b>Codice:</b> {b.code}</div>
            </li>
          ))}
        </ul>
      ) : <p>Nessuna barca registrata.</p>}
    </div>
  );
}

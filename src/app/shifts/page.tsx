import { createClient } from "@/lib/supabase/server";

export default async function ShiftsPage() {
  const supabase = createClient();
  const { data: shifts } = await supabase.from("shifts").select("date, start_time, end_time, notes").order("date");
  return (
    <div className="container">
      <h1 style={{fontSize:"1.5rem", fontWeight:700}}>Turni e Orari</h1>
      {(shifts ?? []).length ? (
        <table>
          <thead>
            <tr><th>Data</th><th>Inizio</th><th>Fine</th><th>Note</th></tr>
          </thead>
          <tbody>
            {shifts!.map((s:any, i:number)=>(
              <tr key={i}>
                <td>{s.date}</td><td>{s.start_time}</td><td>{s.end_time}</td><td>{s.notes}</td>
              </tr>
            ))}
          </tbody>
        </table>
      ) : <p>Nessun turno disponibile.</p>}
    </div>
  );
}

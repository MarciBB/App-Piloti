"use client";
import { useState } from "react";
import { supabase } from "@/lib/supabase/client";

export default function LoginPage() {
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [error, setError] = useState("");

  async function handleLogin(e: React.FormEvent) {
    e.preventDefault();
    const { error } = await supabase.auth.signInWithPassword({ email, password });
    if (error) setError(error.message);
    else window.location.href = "/dashboard";
  }

  return (
    <div className="container" style={{display:"flex",alignItems:"center",justifyContent:"center",minHeight:"100vh"}}>
      <form onSubmit={handleLogin} className="card" style={{width:320}}>
        <h1 style={{fontWeight:700,fontSize:"1.25rem",marginBottom:"1rem",textAlign:"center"}}>Login PilotHub</h1>
        <input
          type="email"
          placeholder="Email aziendale"
          className="card"
          style={{padding:".5rem", width:"100%", marginBottom:".5rem"}}
          value={email}
          onChange={(e)=>setEmail(e.target.value)}
        />
        <input
          type="password"
          placeholder="Password"
          className="card"
          style={{padding:".5rem", width:"100%", marginBottom:".5rem"}}
          value={password}
          onChange={(e)=>setPassword(e.target.value)}
        />
        {error && <p style={{color:"#dc2626", fontSize:12, marginBottom:8}}>{error}</p>}
        <button type="submit" className="btn" style={{width:"100%"}}>Accedi</button>
      </form>
    </div>
  );
}

// app/layout.tsx
import "@/styles/globals.css"; // oppure: import "./globals.css"
import Sidebar from "@/components/Sidebar";
import { createClient } from "@/lib/supabase/server";
import type { ReactNode } from "react";

export const metadata = {
  title: "PilotHub",
  description: "Gestione operativa Bertoldi Boats",
};

export default async function RootLayout({ children }: { children: ReactNode }) {
  const supabase = createClient();
  const { data: { user } } = await supabase.auth.getUser();

  return (
    <html lang="it">
      <body className="min-h-screen flex bg-gradient-to-br from-slate-50 to-blue-50">
        <Sidebar user={user as any} />
        <main className="flex-1 overflow-auto p-6">{children}</main>
      </body>
    </html>
  );
}
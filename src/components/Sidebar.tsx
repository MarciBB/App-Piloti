"use client";
import Link from "next/link";
import { usePathname } from "next/navigation";
import { LayoutDashboard, Ship, GraduationCap, UserCheck, Award, Calendar, Settings, Anchor, Users } from "lucide-react";
import Badge from "@/components/ui/Badge";

type Role = "pilota" | "responsabile" | "ufficio" | "admin";
type NavItem = { title: string; href: string; icon: React.ComponentType<any>; roles: Role[]; };

export default function Sidebar({ user }: { user: any }) {
  const pathname = usePathname();
  const role: Role = (user?.role as Role) || "pilota";

  const all: NavItem[] = [
    { title: "Dashboard", href: "/dashboard", icon: LayoutDashboard, roles: ["pilota","responsabile","ufficio","admin"] },
    { title: "La Tua Barca", href: "/my-boat", icon: Ship, roles: ["pilota","responsabile","admin"] },
    { title: "Formazione", href: "/training", icon: GraduationCap, roles: ["pilota","responsabile","ufficio","admin"] },
    { title: "Onboarding", href: "/onboarding", icon: UserCheck, roles: ["pilota","responsabile","ufficio","admin"] },
    { title: "Il Team", href: "/team", icon: Users, roles: ["pilota","responsabile","ufficio","admin"] },
    { title: "Il Tuo Livello", href: "/your-level", icon: Award, roles: ["pilota","responsabile","admin"] },
    { title: "Turni & Orari", href: "/shifts", icon: Calendar, roles: ["pilota","responsabile","ufficio","admin"] },
    { title: "Scadenzario", href: "/scadenzario", icon: Calendar, roles: ["ufficio","admin"] },
  ];
  const items = user ? all.filter(i => i.roles.includes(role)) : all.filter(i => i.roles.includes("pilota"));
  const adminItems = role === "admin" ? [{ title: "Impostazioni", href: "/settings", icon: Settings }] : [];

  const NavLink = ({ item }: { item: NavItem }) => {
    const active = pathname === item.href || pathname.startsWith(item.href + "/");
    return (
      <Link
        href={item.href}
        className={`flex items-center gap-3 px-4 py-3 mb-2 rounded-xl transition-all
          ${active ? "bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-md" : "hover:bg-blue-50 text-slate-700"}`}
      >
        <item.icon className="w-5 h-5" />
        <span className="font-medium">{item.title}</span>
      </Link>
    );
  };

  return (
    <aside className="hidden md:flex w-72 shrink-0 border-r border-slate-200 bg-white/80 backdrop-blur-md">
      <div className="flex flex-col w-full">
        <div className="border-b border-slate-200 p-6">
          <div className="flex items-center gap-3">
            <div className="w-12 h-12 bg-gradient-to-br from-blue-600 to-blue-800 rounded-xl flex items-center justify-center shadow-lg">
              <Anchor className="w-7 h-7 text-white" />
            </div>
            <div>
              <h2 className="font-bold text-xl text-slate-900">PilotHub</h2>
              <p className="text-xs text-slate-500">Gestione Operativa</p>
            </div>
          </div>
        </div>

        <div className="p-4">
          {items.map(i => <NavLink key={i.title} item={i} />)}
          {adminItems.map(i => (
            <Link key={i.title} href={i.href}
              className="flex items-center gap-3 px-4 py-3 mb-2 rounded-xl transition-all hover:bg-orange-50 text-slate-700">
              <i.icon className="w-5 h-5" />
              <span className="font-medium">{i.title}</span>
              <Badge className="ml-auto" variant="admin">Admin</Badge>
            </Link>
          ))}
        </div>

        {user && (
          <div className="mt-auto border-t border-slate-200 p-4">
            <div className="flex items-center gap-3 p-3 rounded-xl bg-gradient-to-r from-slate-50 to-blue-50">
              <div className="w-10 h-10 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center">
                <span className="text-white font-semibold text-sm">
                  {(user.full_name?.[0] || user.email?.[0] || "U").toUpperCase()}
                </span>
              </div>
              <div className="flex-1 min-w-0">
                <p className="font-semibold text-slate-900 text-sm truncate">{user.full_name || "Utente"}</p>
                <p className="text-xs text-slate-500 truncate">{user.email}</p>
              </div>
            </div>
          </div>
        )}
      </div>
    </aside>
  );
}
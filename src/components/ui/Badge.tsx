import { cn } from "@/lib/utils";
export default function Badge({ className, variant, children }: any){
  const base = "text-xs px-2 py-1 rounded";
  const styles = variant === "admin" ? "bg-orange-100 text-orange-700" : "bg-slate-100 text-slate-700";
  return <span className={cn(base, styles, className)}>{children}</span>;
}

import { cn } from "@/lib/utils";
export default function Button({ className, ...props }: any){
  return <button className={cn("px-4 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700 transition", className)} {...props} />;
}

export type AppRole = "admin" | "responsabile" | "ufficio" | "pilota";

export interface Profile {
  id: string;           // uuid
  user_id: string;      // auth.users.id
  full_name?: string;
  email: string;
  role: AppRole;
  created_at: string;   // ISO
}
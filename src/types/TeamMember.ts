import type { AppRole } from "./User";

export interface TeamMember {
  id: string;
  user_id: string;
  display_name: string;
  email: string;
  role: AppRole;
  created_at: string;
  created_by: string;
}
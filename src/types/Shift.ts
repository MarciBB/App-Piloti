export interface Shift {
  id: string;
  boat_id?: string;
  start_at: string;   // ISO
  end_at: string;     // ISO
  assignee_id?: string; // user id
  created_at: string;
  created_by: string;
}
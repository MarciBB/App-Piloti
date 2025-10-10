export interface Boat {
  id: string;
  name: string;
  code?: string;
  notes?: string;
  created_at: string;
  created_by: string; // user id
}
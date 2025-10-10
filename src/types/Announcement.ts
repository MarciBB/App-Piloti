export interface Announcement {
  id: string;
  title: string;
  body: string;
  published_at: string | null;
  created_at: string;
  created_by: string;
}
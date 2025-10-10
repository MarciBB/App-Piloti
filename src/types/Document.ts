export interface Document {
  id: string;
  title: string;
  category?: string;
  storage_path: string; // Supabase Storage path
  created_at: string;
  created_by: string;
}

export interface DocumentSignature {
  id: string;
  document_id: string;
  signer_id: string; // user id
  signed_at: string | null;
  created_at: string;
  created_by: string;
}
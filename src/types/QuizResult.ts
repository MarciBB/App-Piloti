export interface QuizResult {
  id: string;
  quiz_id: string;
  user_id: string;
  score: number;
  passed: boolean;
  created_at: string;
  created_by: string;
}
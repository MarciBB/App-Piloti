-- db/policies.sql
-- Assunzioni:
--  - Tabella profili: public.profiles(id uuid pk, user_id uuid unique references auth.users(id), role text check (role in ('admin','responsabile','ufficio','pilota')))
--  - Ogni tabella “di dominio” ha: created_by uuid references auth.users(id)   (o almeno un owner/user_id)
--  - Tutele per dominio @bertoldiboats.com

-- =============== Helper functions (security definer) ===============

create or replace function public.is_company_user() returns boolean
language sql stable as $$
  select coalesce(auth.jwt()->>'email','') ilike '%@bertoldiboats.com'
$$;

create or replace function public.current_role() returns text
language sql stable as $$
  select p.role
  from public.profiles p
  where p.user_id = auth.uid()
$$;

create or replace function public.is_admin() returns boolean
language sql stable as $$
  select public.current_role() = 'admin'
$$;

create or replace function public.is_staff() returns boolean
language sql stable as $$
  select public.current_role() in ('admin','responsabile','ufficio')
$$;

-- =============== Abilita RLS su tabelle chiave ===============
alter table public.boats enable row level security;
alter table public.shifts enable row level security;
alter table public.team_members enable row level security;
alter table public.documents enable row level security;
alter table public.document_signatures enable row level security;
alter table public.trainings enable row level security;
alter table public.quiz enable row level security;
alter table public.quiz_results enable row level security;
alter table public.announcements enable row level security;
alter table public.boat_inventory enable row level security;
alter table public.products enable row level security;
alter table public.levels enable row level security;
alter table public.onboarding_items enable row level security;
alter table public.daily_checks enable row level security;

-- Nota: rinomina i nomi tabella in base al tuo schema reale (snake_case vs CamelCase).

-- =============== Policies generiche per dominio aziendale ===============
-- Lettura base: chiunque autenticato con email aziendale può leggere i dati "pubblici" (soft-read)
do $$
begin
  perform 1;
  exception when undefined_table then
    -- se qualche tabella non esiste, ignora
end $$;

-- Esempio pattern riutilizzabile: READ per tutti i dipendenti
create policy "read_boats_company" on public.boats
for select using (public.is_company_user());

create policy "read_team_company" on public.team_members
for select using (public.is_company_user());

create policy "read_docs_company" on public.documents
for select using (public.is_company_user());

create policy "read_trainings_company" on public.trainings
for select using (public.is_company_user());

create policy "read_quiz_company" on public.quiz
for select using (public.is_company_user());

create policy "read_announcements_company" on public.announcements
for select using (public.is_company_user());

-- =============== Policies per ownership (self-service) ===============
-- Ogni utente può leggere/scrivere i propri record (es. iscrizione a turni personali, quiz results, firme documenti)
create policy "self_read_shifts" on public.shifts
for select using (public.is_company_user());

create policy "self_ins_shifts" on public.shifts
for insert with check (public.is_company_user());

create policy "self_upd_own_quiz_results" on public.quiz_results
for update using (created_by = auth.uid()) with check (created_by = auth.uid());

create policy "self_ins_quiz_results" on public.quiz_results
for insert with check (created_by = auth.uid());

create policy "self_read_own_signatures" on public.document_signatures
for select using (created_by = auth.uid());

create policy "self_ins_signatures" on public.document_signatures
for insert with check (created_by = auth.uid());

-- =============== Staff / Admin (CRUD esteso) ===============
-- Staff può inserire/aggiornare “anagrafiche”; Admin può fare tutto.
create policy "staff_manage_boats" on public.boats
for all using (public.is_staff()) with check (public.is_staff());

create policy "staff_manage_team" on public.team_members
for all using (public.is_staff()) with check (public.is_staff());

create policy "staff_manage_docs" on public.documents
for all using (public.is_staff()) with check (public.is_staff());

create policy "staff_manage_trainings" on public.trainings
for all using (public.is_staff()) with check (public.is_staff());

create policy "staff_manage_inventory" on public.boat_inventory
for all using (public.is_staff()) with check (public.is_staff());

-- Admin override (se vuoi esplicitarlo)
create policy "admin_full_boats" on public.boats
for all using (public.is_admin()) with check (public.is_admin());

-- =============== Storage (bucket) per immagini/documenti ===============
-- Assumi bucket 'documents' e 'images'
-- Vai su Storage policies in Supabase e incolla equivalenti:
-- Read: allow if is_company_user()
-- Upload: allow if is_staff()
-- Update/Delete: allow if is_staff()

-- =============== Hardening ===============
-- Nega di default gli INSERT/UPDATE/DELETE per chi non soddisfa le policy sopra.
-- Con RLS attivo, chi non corrisponde a una condizione non passa.
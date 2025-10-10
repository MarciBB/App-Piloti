// File: db/schema.sql
/*
  Estratto dallo schema ricostruito dalle Entities di Base44.
  Puoi incollare questo SQL nella sezione SQL Editor di Supabase.
*/
create table users (
  id uuid primary key default gen_random_uuid(),
  full_name text,
  email text unique not null,
  role text check (role in ('pilota','responsabile','ufficio','admin')) default 'pilota',
  created_at timestamptz default now()
);

create table inventory (
  id uuid primary key default gen_random_uuid(),
  boat_name text not null,
  product_name text,
  quantity int default 0,
  updated_at timestamptz default now()
);
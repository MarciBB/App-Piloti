export interface Product {
  id: string;
  sku?: string;
  name: string;
  created_at: string;
  created_by: string;
}

export interface BoatInventory {
  id: string;
  boat_id: string;
  product_id: string;
  quantity: number;
  updated_at: string;
  created_by: string;
}
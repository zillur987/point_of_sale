import http from '@/api/http'
import type { Paginated } from '@/api/catalog'

type Params = Record<string, unknown>

export interface Warehouse {
  id: number
  name: string
  code: string
  phone: string | null
  address: string | null
  is_default: boolean
  is_active: boolean
}
export type WarehousePayload = Omit<Warehouse, 'id'>

export interface WarehouseBrief {
  id: number
  name: string
  code?: string
}

/** Variant + product + unit, as returned by every inventory endpoint. Money is in minor units. */
export interface StockVariant {
  id: number
  name: string
  sku: string
  barcode: string | null
  cost_price: number
  sale_price: number
  product: {
    id: number
    name: string
    track_stock: boolean
    alert_quantity: string
    unit: { id: number; name: string; short_name: string; allow_decimal: boolean } | null
  } | null
}

/** Quantities are fixed-scale strings ("12.500") so they never lose precision on the way. */
export interface StockLevel {
  id: number
  quantity: string
  is_low: boolean
  warehouse?: WarehouseBrief
  variant?: StockVariant
}

export interface StockSummary {
  lines: number
  total_quantity: string
  stock_value: number // minor units
  low_stock_count: number
}

export type MovementType =
  | 'opening'
  | 'adjustment_in'
  | 'adjustment_out'
  | 'transfer_in'
  | 'transfer_out'
  | 'purchase'
  | 'purchase_return'
  | 'sale'
  | 'sale_return'

export interface StockMovement {
  id: number
  type: MovementType
  type_label: string
  quantity: string // signed
  balance_after: string
  note: string | null
  reference_type: string | null
  reference_id: number | null
  warehouse?: WarehouseBrief
  variant?: StockVariant
  user?: { id: number; name: string } | null
  created_at: string
}

export type AdjustmentReason = 'opening_stock' | 'damaged' | 'expired' | 'lost' | 'found' | 'correction' | 'other'
export type AdjustmentDirection = 'in' | 'out'

export interface StockAdjustment {
  id: number
  reference_no: string
  reason: AdjustmentReason
  reason_label: string
  note: string | null
  adjusted_at: string
  items_count?: number
  warehouse?: WarehouseBrief
  user?: { id: number; name: string } | null
  items?: { id: number; direction: AdjustmentDirection; quantity: string; variant: StockVariant }[]
}
export interface AdjustmentPayload {
  warehouse_id: number
  reason: AdjustmentReason
  adjusted_at?: string | null
  note?: string | null
  items: { product_variant_id: number; direction: AdjustmentDirection; quantity: number }[]
}

export interface StockTransfer {
  id: number
  reference_no: string
  note: string | null
  transferred_at: string
  items_count?: number
  from_warehouse?: WarehouseBrief
  to_warehouse?: WarehouseBrief
  user?: { id: number; name: string } | null
  items?: { id: number; quantity: string; variant: StockVariant }[]
}
export interface TransferPayload {
  from_warehouse_id: number
  to_warehouse_id: number
  transferred_at?: string | null
  note?: string | null
  items: { product_variant_id: number; quantity: number }[]
}

export const warehouseApi = {
  list: (params?: Params) => http.get<Paginated<Warehouse>>('/v1/warehouses', { params }),
  options: () => http.get<{ data: Warehouse[] }>('/v1/warehouses/options'),
  show: (id: number) => http.get<{ data: Warehouse }>(`/v1/warehouses/${id}`),
  create: (payload: WarehousePayload) => http.post<{ data: Warehouse }>('/v1/warehouses', payload),
  update: (id: number, payload: WarehousePayload) => http.put<{ data: Warehouse }>(`/v1/warehouses/${id}`, payload),
  remove: (id: number) => http.delete(`/v1/warehouses/${id}`),
}

export const stockApi = {
  levels: (params?: Params) => http.get<Paginated<StockLevel>>('/v1/stock', { params }),
  summary: (params?: Params) => http.get<{ data: StockSummary }>('/v1/stock/summary', { params }),
  movements: (params?: Params) => http.get<Paginated<StockMovement>>('/v1/stock/movements', { params }),
}

export const adjustmentApi = {
  list: (params?: Params) => http.get<Paginated<StockAdjustment>>('/v1/stock/adjustments', { params }),
  show: (id: number) => http.get<{ data: StockAdjustment }>(`/v1/stock/adjustments/${id}`),
  create: (payload: AdjustmentPayload) => http.post<{ data: StockAdjustment }>('/v1/stock/adjustments', payload),
}

export const transferApi = {
  list: (params?: Params) => http.get<Paginated<StockTransfer>>('/v1/stock/transfers', { params }),
  show: (id: number) => http.get<{ data: StockTransfer }>(`/v1/stock/transfers/${id}`),
  create: (payload: TransferPayload) => http.post<{ data: StockTransfer }>('/v1/stock/transfers', payload),
}

export const ADJUSTMENT_REASONS: { value: AdjustmentReason; label: string }[] = [
  { value: 'opening_stock', label: 'Opening stock' },
  { value: 'correction', label: 'Stock count correction' },
  { value: 'damaged', label: 'Damaged' },
  { value: 'expired', label: 'Expired' },
  { value: 'lost', label: 'Lost / stolen' },
  { value: 'found', label: 'Found' },
  { value: 'other', label: 'Other' },
]

export const MOVEMENT_TYPES: { value: MovementType; label: string }[] = [
  { value: 'opening', label: 'Opening stock' },
  { value: 'adjustment_in', label: 'Adjustment (in)' },
  { value: 'adjustment_out', label: 'Adjustment (out)' },
  { value: 'transfer_in', label: 'Transfer in' },
  { value: 'transfer_out', label: 'Transfer out' },
  { value: 'purchase', label: 'Purchase' },
  { value: 'purchase_return', label: 'Purchase return' },
  { value: 'sale', label: 'Sale' },
  { value: 'sale_return', label: 'Sale return' },
]

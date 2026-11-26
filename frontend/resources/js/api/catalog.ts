import http from '@/api/http'

export interface Paginated<T> {
  data: T[]
  meta: { current_page: number; last_page: number; per_page: number; total: number }
}

export interface Category {
  id: number
  parent_id: number | null
  name: string
  slug: string
  description: string | null
  sort_order: number
  is_active: boolean
  parent?: Category | null
  children?: Category[]
}
export type CategoryPayload = Pick<Category, 'name' | 'parent_id' | 'description' | 'sort_order' | 'is_active'>

export interface Brand {
  id: number
  name: string
  slug: string
  is_active: boolean
}
export type BrandPayload = Pick<Brand, 'name' | 'is_active'>

export interface Unit {
  id: number
  name: string
  short_name: string
  allow_decimal: boolean
}
export type UnitPayload = Omit<Unit, 'id'>

export interface ProductVariant {
  id: number
  product_id: number
  name: string
  sku: string
  barcode: string | null
  cost_price: number // minor units
  sale_price: number // minor units
  attributes: Record<string, string> | null
  is_active: boolean
  product?: Product
}
export type VariantPayload = Partial<Pick<ProductVariant, 'id' | 'name' | 'barcode' | 'attributes' | 'is_active'>> &
  Pick<ProductVariant, 'sku' | 'cost_price' | 'sale_price'>

export type ProductType = 'single' | 'variable'

export interface Product {
  id: number
  name: string
  type: ProductType
  description: string | null
  track_stock: boolean
  alert_quantity: string
  tax_rate_bps: number
  tax_inclusive: boolean
  is_active: boolean
  category_id: number | null
  brand_id: number | null
  unit_id: number
  category?: Category
  brand?: Brand
  unit?: Unit
  variants?: ProductVariant[]
}
export interface ProductPayload {
  name: string
  type: ProductType
  category_id: number | null
  brand_id: number | null
  unit_id: number
  description?: string | null
  track_stock?: boolean
  alert_quantity?: number
  tax_rate_bps?: number
  tax_inclusive?: boolean
  is_active?: boolean
  variants: VariantPayload[]
}

type Params = Record<string, unknown>

const resource = <T, P>(path: string) => ({
  list: (params?: Params) => http.get<Paginated<T>>(`/v1/${path}`, { params }),
  show: (id: number) => http.get<{ data: T }>(`/v1/${path}/${id}`),
  create: (payload: P) => http.post<{ data: T }>(`/v1/${path}`, payload),
  update: (id: number, payload: P) => http.put<{ data: T }>(`/v1/${path}/${id}`, payload),
  remove: (id: number) => http.delete(`/v1/${path}/${id}`),
})

export const categoryApi = {
  ...resource<Category, CategoryPayload>('categories'),
  tree: () => http.get<{ data: Category[] }>('/v1/categories/tree'),
}
export const brandApi = resource<Brand, BrandPayload>('brands')
export const unitApi = resource<Unit, UnitPayload>('units')
export const productApi = resource<Product, ProductPayload>('products')

export const variantApi = {
  search: (q: string, limit = 20) =>
    http.get<{ data: ProductVariant[] }>('/v1/variants/search', { params: { q, limit } }),
  byBarcode: (barcode: string) => http.get<{ data: ProductVariant }>(`/v1/variants/barcode/${encodeURIComponent(barcode)}`),
}

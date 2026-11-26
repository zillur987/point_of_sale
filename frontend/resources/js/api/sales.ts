import http from '@/api/http'
import type { Paginated } from '@/api/catalog'
import type { Warehouse } from '@/api/inventory'

export interface PosProduct { id:number; name:string; variant_name:string|null; sku:string; barcode:string|null; sale_price:number; available_quantity:string; allow_decimal:boolean }
export interface SaleItemInput { product_variant_id:number; quantity:number; discount_amount?:number; tax_amount?:number }
export interface CheckoutPayload { warehouse_id:number; request_key:string; items:SaleItemInput[]; discount_amount:number; tax_amount:number; paid_amount:number; payment_method:'cash'|'card'|'mobile_banking'|'bank'; payment_reference?:string|null; note?:string|null }
export interface SaleRecord { id:number; invoice_no:string; status:string; subtotal:number; discount_amount:number; tax_amount:number; grand_total:number; paid_amount:number; change_amount:number; sold_at:string; items_count?:number; user?:{id:number;name:string}; warehouse?:{id:number;name:string}; items?:Array<{product_name:string;variant_name:string|null;sku:string;quantity:string;unit_price:number;line_total:number}>; payments?:Array<{method:string;amount:number}> }
export const posApi = {
 catalog:(warehouse_id:number,search='')=>http.get<{data:PosProduct[]}>('/v1/pos/catalog',{params:{warehouse_id,search}}),
 checkout:(payload:CheckoutPayload)=>http.post<{data:SaleRecord}>('/v1/pos/checkout',payload),
 sales:(params?:Record<string,unknown>)=>http.get<Paginated<SaleRecord>>('/v1/sales',{params}),
 sale:(id:number)=>http.get<{data:SaleRecord}>(`/v1/sales/${id}`),
}

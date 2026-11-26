import http from '@/api/http'
import type { Paginated } from '@/api/catalog'
export interface Supplier {id:number;name:string;company_name:string|null;email:string|null;phone:string|null;tax_number:string|null;address:string|null;city:string|null;country:string|null;notes:string|null;is_active:boolean}
export interface PurchaseProduct {id:number;product_name:string;variant_name:string|null;sku:string;purchase_price:number}
export interface PurchaseItemInput {product_variant_id:number;quantity:string;unit_cost:number}
export interface PurchaseRecord {id:number;purchase_no:string;status:'draft'|'received'|'cancelled';subtotal:number;discount_amount:number;tax_amount:number;grand_total:number;ordered_at:string|null;received_at:string|null;items_count?:number;supplier?:{id:number;name:string;company_name:string|null};warehouse?:{id:number;name:string};items?:Array<{id:number;product_variant_id:number;product_name:string;variant_name:string|null;sku:string;quantity:string;unit_cost:number;line_total:number}>}
export const purchaseApi={
 suppliers:(params?:Record<string,unknown>)=>http.get<Paginated<Supplier>>('/v1/suppliers',{params}),
 supplier:(id:number)=>http.get<{data:Supplier}>(`/v1/suppliers/${id}`),
 createSupplier:(data:Partial<Supplier>)=>http.post<{data:Supplier}>('/v1/suppliers',data),
 updateSupplier:(id:number,data:Partial<Supplier>)=>http.put<{data:Supplier}>(`/v1/suppliers/${id}`,data),
 catalog:(search='')=>http.get<{data:PurchaseProduct[]}>('/v1/purchases/catalog',{params:{search}}),
 purchases:(params?:Record<string,unknown>)=>http.get<Paginated<PurchaseRecord>>('/v1/purchases',{params}),
 purchase:(id:number)=>http.get<{data:PurchaseRecord}>(`/v1/purchases/${id}`),
 createPurchase:(data:{supplier_id:number;warehouse_id:number;items:PurchaseItemInput[];discount_amount:number;tax_amount:number;note?:string})=>http.post<{data:PurchaseRecord}>('/v1/purchases',data),
 receive:(id:number)=>http.post<{data:PurchaseRecord}>(`/v1/purchases/${id}/receive`),
}

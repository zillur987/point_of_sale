import http from '@/api/http'
import type { Paginated } from '@/api/catalog'

export interface CustomerGroup {
    id: number
    name: string
    description: string | null
    is_active: boolean
    customers_count?: number
}

export interface Customer {
    id: number
    customer_group_id: number | null
    name: string
    email: string | null
    phone: string | null
    address: string | null
    city: string | null
    country: string | null
    notes: string | null
    points_balance: number
    is_active: boolean
    loyalty_transactions_count?: number
    group?: CustomerGroup | null
    created_at?: string
}

export interface LoyaltyTransaction {
    id: number
    type: 'earn' | 'redeem' | 'adjustment'
    points_change: number
    balance_after: number
    note: string | null
    user?: { id: number; name: string } | null
    created_at: string
}

export type CustomerInput = Omit<Customer, 'id' | 'points_balance' | 'group' | 'loyalty_transactions_count' | 'created_at'>

export const customerApi = {
    customers: (params?: Record<string, unknown>) =>
        http.get<Paginated<Customer>>('/v1/customers', { params }),

    customer: (id: number) =>
        http.get<{ data: Customer }>(`/v1/customers/${id}`),

    createCustomer: (data: Partial<CustomerInput>) =>
        http.post<{ data: Customer }>('/v1/customers', data),

    updateCustomer: (id: number, data: Partial<CustomerInput>) =>
        http.put<{ data: Customer }>(`/v1/customers/${id}`, data),

    groups: () =>
        http.get<{ data: CustomerGroup[] }>('/v1/customer-groups'),

    createGroup: (data: Partial<CustomerGroup>) =>
        http.post<{ data: CustomerGroup }>('/v1/customer-groups', data),

    updateGroup: (id: number, data: Partial<CustomerGroup>) =>
        http.put<{ data: CustomerGroup }>(`/v1/customer-groups/${id}`, data),

    loyalty: (id: number, params?: Record<string, unknown>) =>
        http.get<Paginated<LoyaltyTransaction>>(`/v1/customers/${id}/loyalty`, { params }),

    addLoyalty: (id: number, data: {
        type: 'earn' | 'redeem' | 'adjustment'
        points: number
        direction?: 'add' | 'subtract'
        note?: string
    }) => http.post<{ data: LoyaltyTransaction }>(`/v1/customers/${id}/loyalty`, data),
}

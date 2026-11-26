import http from '@/api/http'

export interface Branch {
  id: number
  name: string
  code: string
  phone: string | null
  email: string | null
  address: string | null
  is_active: boolean
}

export type BranchPayload = Omit<Branch, 'id'>

export const branchApi = {
  list: (params?: Record<string, unknown>) => http.get('/v1/branches', { params }),
  show: (id: number) => http.get(`/v1/branches/${id}`),
  create: (payload: BranchPayload) => http.post('/v1/branches', payload),
  update: (id: number, payload: BranchPayload) => http.put(`/v1/branches/${id}`, payload),
  remove: (id: number) => http.delete(`/v1/branches/${id}`),
}

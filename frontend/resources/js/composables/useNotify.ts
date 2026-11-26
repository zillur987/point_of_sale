import { reactive } from 'vue'

export interface Toast {
  id: number
  type: 'success' | 'danger'
  message: string
}

const toasts = reactive<Toast[]>([])
let seq = 0

function push(type: Toast['type'], message: string, ms = 3500) {
  const id = ++seq
  toasts.push({ id, type, message })
  setTimeout(() => {
    const index = toasts.findIndex((t) => t.id === id)
    if (index > -1) toasts.splice(index, 1)
  }, ms)
}

/** Minimal toast store. If your admin already has toastr/SweetAlert, re-implement these two functions. */
export function useNotify() {
  return {
    toasts,
    success: (message: string) => push('success', message),
    error: (message: string) => push('danger', message, 5000),
  }
}

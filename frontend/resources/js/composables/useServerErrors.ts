import axios from 'axios'
import { ref } from 'vue'

/** Normalises Laravel 422 responses (validation errors and business-rule messages). */
export function useServerErrors() {
  const errors = ref<Record<string, string[]>>({})
  const message = ref('')

  function capture(error: unknown): void {
    if (axios.isAxiosError(error) && error.response?.status === 422) {
      errors.value = error.response.data?.errors ?? {}
      message.value = error.response.data?.message ?? ''
      return
    }
    message.value = 'Something went wrong. Please try again.'
  }

  function clear(): void {
    errors.value = {}
    message.value = ''
  }

  const first = (field: string): string | undefined => errors.value[field]?.[0]

  return { errors, message, capture, clear, first }
}

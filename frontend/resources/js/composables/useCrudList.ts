import { onBeforeUnmount, onMounted, reactive, ref, watch, type Ref } from 'vue'
import type { Paginated } from '@/api/catalog'

type Fetcher<T> = (params: Record<string, unknown>) => Promise<{ data: Paginated<T> }>

/**
 * Server-side paginated list with debounced filters.
 * Stale responses are ignored so fast typing never shows out-of-order results.
 */
export function useCrudList<T, F extends Record<string, unknown>>(fetcher: Fetcher<T>, initialFilters: F) {
  const items = ref([]) as Ref<T[]>
  const meta = ref<Paginated<T>['meta'] | null>(null)
  const loading = ref(false)
  const page = ref(1)
  const filters = reactive({ ...initialFilters }) as F

  let timer: ReturnType<typeof setTimeout> | undefined
  let requestId = 0

  const activeFilters = (): Record<string, unknown> =>
    Object.fromEntries(Object.entries(filters).filter(([, v]) => v !== '' && v !== null && v !== undefined))

  async function load(): Promise<void> {
    const id = ++requestId
    loading.value = true
    try {
      const { data } = await fetcher({ ...activeFilters(), page: page.value })
      if (id !== requestId) return
      items.value = data.data
      meta.value = data.meta
    } finally {
      if (id === requestId) loading.value = false
    }
  }

  watch(filters, () => {
    clearTimeout(timer)
    timer = setTimeout(() => {
      if (page.value !== 1) page.value = 1 // the page watcher reloads
      else void load()
    }, 300)
  })
  watch(page, () => void load())

  onMounted(load)
  onBeforeUnmount(() => clearTimeout(timer))

  return { items, meta, loading, page, filters, load }
}

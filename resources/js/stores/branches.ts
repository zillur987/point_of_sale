import { defineStore } from 'pinia'
import { ref } from 'vue'
import { branchApi, type Branch } from '@/api/branches'

export const useBranchStore = defineStore('branches', () => {
  const items = ref<Branch[]>([])
  const loading = ref(false)

  async function fetch(params: Record<string, unknown> = {}) {
    loading.value = true
    try {
      const { data } = await branchApi.list(params)
      items.value = data.data
    } finally {
      loading.value = false
    }
  }

  return { items, loading, fetch }
})

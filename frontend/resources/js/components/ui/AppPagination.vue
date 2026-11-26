<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{ meta: { current_page: number; last_page: number; total: number } | null }>()
const emit = defineEmits<{ change: [page: number] }>()

const pages = computed(() => {
  if (!props.meta) return []
  const { current_page: current, last_page: last } = props.meta
  const from = Math.max(1, current - 2)
  const to = Math.min(last, current + 2)
  return Array.from({ length: to - from + 1 }, (_, i) => from + i)
})
</script>

<template>
  <div v-if="meta && meta.last_page > 1" class="d-flex justify-content-between align-items-center mt-3">
    <small class="text-muted">{{ meta.total }} records</small>
    <ul class="pagination pagination-sm mb-0">
      <li class="page-item" :class="{ disabled: meta.current_page === 1 }">
        <button class="page-link" type="button" @click="emit('change', meta.current_page - 1)">&laquo;</button>
      </li>
      <li v-for="p in pages" :key="p" class="page-item" :class="{ active: p === meta.current_page }">
        <button class="page-link" type="button" @click="emit('change', p)">{{ p }}</button>
      </li>
      <li class="page-item" :class="{ disabled: meta.current_page === meta.last_page }">
        <button class="page-link" type="button" @click="emit('change', meta.current_page + 1)">&raquo;</button>
      </li>
    </ul>
  </div>
</template>

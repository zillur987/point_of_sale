<script setup lang="ts">
import { onBeforeUnmount, ref } from 'vue'
import { variantApi, type ProductVariant } from '@/api/catalog'
import { variantLabel } from '@/utils/inventory'

defineProps<{ placeholder?: string }>()
const emit = defineEmits<{ select: [variant: ProductVariant] }>()

const query = ref('')
const results = ref<ProductVariant[]>([])
const open = ref(false)
const searching = ref(false)

let timer: ReturnType<typeof setTimeout> | undefined
let requestId = 0

function onInput(): void {
  clearTimeout(timer)
  if (query.value.trim().length < 2) {
    results.value = []
    open.value = false
    return
  }
  timer = setTimeout(search, 250)
}

async function search(): Promise<void> {
  const id = ++requestId
  searching.value = true
  try {
    const { data } = await variantApi.search(query.value.trim(), 15)
    if (id !== requestId) return // a newer search superseded this one
    results.value = data.data
    open.value = true
  } finally {
    if (id === requestId) searching.value = false
  }
}

function pick(variant: ProductVariant): void {
  emit('select', variant)
  query.value = ''
  results.value = []
  open.value = false
}

// delay so a click on a result registers before the list closes
const onBlur = (): void => { setTimeout(() => (open.value = false), 150) }

onBeforeUnmount(() => clearTimeout(timer))
</script>

<template>
  <div class="position-relative">
    <input
      v-model="query"
      class="form-control"
      :placeholder="placeholder ?? 'Search product by name, SKU or barcode...'"
      autocomplete="off"
      @input="onInput"
      @focus="open = results.length > 0"
      @blur="onBlur"
    />
    <div v-if="open" class="list-group position-absolute w-100 shadow-sm" style="z-index: 1060; max-height: 280px; overflow-y: auto">
      <div v-if="!results.length" class="list-group-item text-muted small">{{ searching ? 'Searching...' : 'No products found' }}</div>
      <button
        v-for="v in results"
        :key="v.id"
        type="button"
        class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
        @mousedown.prevent="pick(v)"
      >
        <span>{{ variantLabel(v) }}</span>
        <small class="text-muted">{{ v.sku }}</small>
      </button>
    </div>
  </div>
</template>

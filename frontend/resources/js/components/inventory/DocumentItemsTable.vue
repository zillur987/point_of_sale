<script setup lang="ts">
import type { AdjustmentDirection, StockVariant } from '@/api/inventory'
import { formatQty, variantLabel } from '@/utils/inventory'

defineProps<{
  items: { id: number; quantity: string; direction?: AdjustmentDirection; variant: StockVariant }[]
}>()
</script>

<template>
  <table class="table table-sm align-middle mb-0">
    <thead class="table-light">
      <tr><th>Product</th><th>SKU</th><th v-if="items.some((i) => i.direction)">Change</th><th class="text-end">Quantity</th></tr>
    </thead>
    <tbody>
      <tr v-for="item in items" :key="item.id">
        <td>{{ variantLabel(item.variant) }}</td>
        <td><code>{{ item.variant.sku }}</code></td>
        <td v-if="item.direction">
          <span class="badge" :class="item.direction === 'in' ? 'bg-success' : 'bg-danger'">{{ item.direction === 'in' ? 'In' : 'Out' }}</span>
        </td>
        <td class="text-end">{{ formatQty(item.quantity) }} {{ item.variant.product?.unit?.short_name }}</td>
      </tr>
    </tbody>
  </table>
</template>

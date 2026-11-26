<script setup lang="ts">
import { onMounted } from 'vue'
import { MOVEMENT_TYPES, stockApi, type StockMovement } from '@/api/inventory'
import AppPagination from '@/components/ui/AppPagination.vue'
import { useCrudList } from '@/composables/useCrudList'
import { useInventoryLookups } from '@/stores/inventory'
import { formatDateTime, formatQty, formatSignedQty, variantLabel } from '@/utils/inventory'

const inventory = useInventoryLookups()
const { items, meta, loading, page, filters } = useCrudList<
  StockMovement,
  { search: string; warehouse_id: string; type: string; date_from: string; date_to: string }
>(stockApi.movements, { search: '', warehouse_id: '', type: '', date_from: '', date_to: '' })

onMounted(() => inventory.load())
</script>

<template>
  <div class="container-fluid py-3">
    <div class="mb-3">
      <h4 class="mb-0">Stock movements</h4>
      <small class="text-muted">Every change to stock, newest first. This ledger is read-only.</small>
    </div>

    <div class="card">
      <div class="card-body">
        <div class="row g-2 mb-3">
          <div class="col-12 col-md-3">
            <input v-model="filters.search" class="form-control" placeholder="Search name, SKU or barcode..." />
          </div>
          <div class="col-6 col-md-2">
            <select v-model="filters.warehouse_id" class="form-select">
              <option value="">All warehouses</option>
              <option v-for="w in inventory.warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
            </select>
          </div>
          <div class="col-6 col-md-2">
            <select v-model="filters.type" class="form-select">
              <option value="">All types</option>
              <option v-for="t in MOVEMENT_TYPES" :key="t.value" :value="t.value">{{ t.label }}</option>
            </select>
          </div>
          <div class="col-6 col-md-2"><input v-model="filters.date_from" type="date" class="form-control" title="From" /></div>
          <div class="col-6 col-md-2"><input v-model="filters.date_to" type="date" class="form-control" title="To" /></div>
        </div>

        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>Date</th><th>Product</th><th>Warehouse</th><th>Type</th>
                <th class="text-end">Change</th><th class="text-end">Balance</th><th>Note</th><th>By</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="loading && !items.length"><td colspan="8" class="text-center text-muted py-4">Loading...</td></tr>
              <tr v-else-if="!items.length"><td colspan="8" class="text-center text-muted py-4">No movements found</td></tr>
              <tr v-for="row in items" :key="row.id">
                <td class="text-nowrap">{{ formatDateTime(row.created_at) }}</td>
                <td>
                  <div class="fw-medium">{{ row.variant ? variantLabel(row.variant) : '—' }}</div>
                  <small class="text-muted">{{ row.variant?.sku }}</small>
                </td>
                <td>{{ row.warehouse?.name }}</td>
                <td><span class="badge bg-light text-dark border">{{ row.type_label }}</span></td>
                <td class="text-end fw-semibold" :class="Number(row.quantity) > 0 ? 'text-success' : 'text-danger'">
                  {{ formatSignedQty(row.quantity) }}
                </td>
                <td class="text-end">{{ formatQty(row.balance_after) }}</td>
                <td class="small text-muted">{{ row.note ?? '—' }}</td>
                <td>{{ row.user?.name ?? '—' }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <AppPagination :meta="meta" @change="page = $event" />
      </div>
    </div>
  </div>
</template>

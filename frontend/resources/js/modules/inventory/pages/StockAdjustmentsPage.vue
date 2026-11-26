<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import { ADJUSTMENT_REASONS, adjustmentApi, type StockAdjustment } from '@/api/inventory'
import AppDrawer from '@/components/ui/AppDrawer.vue'
import AppPagination from '@/components/ui/AppPagination.vue'
import DocumentItemsTable from '@/components/inventory/DocumentItemsTable.vue'
import { useCrudList } from '@/composables/useCrudList'
import { useNotify } from '@/composables/useNotify'
import { useInventoryLookups } from '@/stores/inventory'

const notify = useNotify()
const inventory = useInventoryLookups()
const { items, meta, loading, page, filters } = useCrudList<
  StockAdjustment,
  { search: string; warehouse_id: string; reason: string; date_from: string; date_to: string }
>(adjustmentApi.list, { search: '', warehouse_id: '', reason: '', date_from: '', date_to: '' })

const detailOpen = ref(false)
const detail = ref<StockAdjustment | null>(null)

onMounted(() => inventory.load())

async function view(row: StockAdjustment): Promise<void> {
  detail.value = null
  detailOpen.value = true
  try {
    const { data } = await adjustmentApi.show(row.id)
    detail.value = data.data
  } catch {
    detailOpen.value = false
    notify.error('Could not load this adjustment')
  }
}
</script>

<template>
  <div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h4 class="mb-0">Stock adjustments</h4>
      <RouterLink :to="{ name: 'inventory.adjustments.create' }" class="btn btn-primary">+ New adjustment</RouterLink>
    </div>

    <div class="card">
      <div class="card-body">
        <div class="row g-2 mb-3">
          <div class="col-12 col-md-3"><input v-model="filters.search" class="form-control" placeholder="Reference no..." /></div>
          <div class="col-6 col-md-2">
            <select v-model="filters.warehouse_id" class="form-select">
              <option value="">All warehouses</option>
              <option v-for="w in inventory.warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
            </select>
          </div>
          <div class="col-6 col-md-2">
            <select v-model="filters.reason" class="form-select">
              <option value="">All reasons</option>
              <option v-for="r in ADJUSTMENT_REASONS" :key="r.value" :value="r.value">{{ r.label }}</option>
            </select>
          </div>
          <div class="col-6 col-md-2"><input v-model="filters.date_from" type="date" class="form-control" title="From" /></div>
          <div class="col-6 col-md-2"><input v-model="filters.date_to" type="date" class="form-control" title="To" /></div>
        </div>

        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr><th>Reference</th><th>Date</th><th>Warehouse</th><th>Reason</th><th class="text-end">Items</th><th>By</th><th /></tr>
            </thead>
            <tbody>
              <tr v-if="loading && !items.length"><td colspan="7" class="text-center text-muted py-4">Loading...</td></tr>
              <tr v-else-if="!items.length"><td colspan="7" class="text-center text-muted py-4">No adjustments found</td></tr>
              <tr v-for="row in items" :key="row.id">
                <td class="fw-medium">{{ row.reference_no }}</td>
                <td>{{ row.adjusted_at }}</td>
                <td>{{ row.warehouse?.name }}</td>
                <td><span class="badge bg-light text-dark border">{{ row.reason_label }}</span></td>
                <td class="text-end">{{ row.items_count }}</td>
                <td>{{ row.user?.name ?? '—' }}</td>
                <td class="text-end"><button class="btn btn-sm btn-outline-primary" @click="view(row)">View</button></td>
              </tr>
            </tbody>
          </table>
        </div>

        <AppPagination :meta="meta" @change="page = $event" />
      </div>
    </div>

    <AppDrawer v-model="detailOpen" :title="detail?.reference_no ?? 'Adjustment'" width="620px">
      <div v-if="!detail" class="text-center text-muted py-5">Loading...</div>
      <template v-else>
        <dl class="row mb-3">
          <dt class="col-4">Warehouse</dt><dd class="col-8">{{ detail.warehouse?.name }}</dd>
          <dt class="col-4">Date</dt><dd class="col-8">{{ detail.adjusted_at }}</dd>
          <dt class="col-4">Reason</dt><dd class="col-8">{{ detail.reason_label }}</dd>
          <dt class="col-4">Note</dt><dd class="col-8">{{ detail.note ?? '—' }}</dd>
          <dt class="col-4">By</dt><dd class="col-8">{{ detail.user?.name ?? '—' }}</dd>
        </dl>
        <DocumentItemsTable :items="detail.items ?? []" />
      </template>
    </AppDrawer>
  </div>
</template>

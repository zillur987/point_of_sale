<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import { stockApi, type StockLevel, type StockSummary } from '@/api/inventory'
import type { Category } from '@/api/catalog'
import AppPagination from '@/components/ui/AppPagination.vue'
import { useCrudList } from '@/composables/useCrudList'
import { useCatalogLookups } from '@/stores/catalog'
import { useInventoryLookups } from '@/stores/inventory'
import { formatQty, variantLabel } from '@/utils/inventory'
import { formatMoney } from '@/utils/money'

const inventory = useInventoryLookups()
const catalog = useCatalogLookups()

const summary = ref<StockSummary | null>(null)

/** Summary cards follow the same filters as the table, so numbers always match what you see. */
async function fetchLevels(params: Record<string, unknown>) {
  const filterParams = Object.fromEntries(Object.entries(params).filter(([key]) => key !== 'page'))
  void stockApi.summary(filterParams).then(({ data }) => (summary.value = data.data))
  return stockApi.levels(params)
}

const { items, meta, loading, page, filters } = useCrudList<
  StockLevel,
  { search: string; warehouse_id: string; category_id: string; brand_id: string; low_stock: string; in_stock: string }
>(fetchLevels, { search: '', warehouse_id: '', category_id: '', brand_id: '', low_stock: '', in_stock: '' })

onMounted(() => Promise.all([inventory.load(), catalog.load(true)]))

function flatten(nodes: Category[], depth = 0): { id: number; label: string }[] {
  return nodes.flatMap((n) => [{ id: n.id, label: `${'— '.repeat(depth)}${n.name}` }, ...flatten(n.children ?? [], depth + 1)])
}
const categoryOptions = computed(() => flatten(catalog.categoryTree))
</script>

<template>
  <div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h4 class="mb-0">Stock</h4>
      <div class="d-flex gap-2">
        <RouterLink :to="{ name: 'inventory.transfers.create' }" class="btn btn-outline-primary">Transfer stock</RouterLink>
        <RouterLink :to="{ name: 'inventory.adjustments.create' }" class="btn btn-primary">+ Adjust stock</RouterLink>
      </div>
    </div>

    <div v-if="summary" class="row g-3 mb-3">
      <div class="col-6 col-lg-3"><div class="card"><div class="card-body py-3">
        <div class="text-muted small">Stocked items</div><div class="fs-4 fw-semibold">{{ summary.lines }}</div>
      </div></div></div>
      <div class="col-6 col-lg-3"><div class="card"><div class="card-body py-3">
        <div class="text-muted small">Total quantity</div><div class="fs-4 fw-semibold">{{ formatQty(summary.total_quantity) }}</div>
      </div></div></div>
      <div class="col-6 col-lg-3"><div class="card"><div class="card-body py-3">
        <div class="text-muted small">Stock value (at cost)</div><div class="fs-4 fw-semibold">{{ formatMoney(summary.stock_value) }}</div>
      </div></div></div>
      <div class="col-6 col-lg-3"><div class="card"><div class="card-body py-3">
        <div class="text-muted small">Low stock</div>
        <div class="fs-4 fw-semibold" :class="{ 'text-danger': summary.low_stock_count > 0 }">{{ summary.low_stock_count }}</div>
      </div></div></div>
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
            <select v-model="filters.category_id" class="form-select">
              <option value="">All categories</option>
              <option v-for="c in categoryOptions" :key="c.id" :value="c.id">{{ c.label }}</option>
            </select>
          </div>
          <div class="col-6 col-md-2">
            <select v-model="filters.brand_id" class="form-select">
              <option value="">All brands</option>
              <option v-for="b in catalog.brands" :key="b.id" :value="b.id">{{ b.name }}</option>
            </select>
          </div>
          <div class="col-6 col-md-3 d-flex gap-2">
            <select v-model="filters.low_stock" class="form-select">
              <option value="">Any level</option>
              <option value="1">Low stock only</option>
            </select>
            <select v-model="filters.in_stock" class="form-select">
              <option value="">Incl. zero</option>
              <option value="1">In stock only</option>
            </select>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr><th>Product</th><th>SKU</th><th>Warehouse</th><th class="text-end">On hand</th><th class="text-end">Alert at</th><th>Status</th></tr>
            </thead>
            <tbody>
              <tr v-if="loading && !items.length"><td colspan="6" class="text-center text-muted py-4">Loading...</td></tr>
              <tr v-else-if="!items.length"><td colspan="6" class="text-center text-muted py-4">No stock yet. Add opening stock with “Adjust stock”.</td></tr>
              <tr v-for="row in items" :key="row.id">
                <td class="fw-medium">{{ row.variant ? variantLabel(row.variant) : '—' }}</td>
                <td><code>{{ row.variant?.sku }}</code></td>
                <td>{{ row.warehouse?.name }}</td>
                <td class="text-end fw-semibold" :class="{ 'text-danger': row.is_low }">
                  {{ formatQty(row.quantity) }} {{ row.variant?.product?.unit?.short_name }}
                </td>
                <td class="text-end text-muted">{{ formatQty(row.variant?.product?.alert_quantity ?? 0) }}</td>
                <td>
                  <span class="badge" :class="Number(row.quantity) <= 0 ? 'bg-danger' : row.is_low ? 'bg-warning text-dark' : 'bg-success'">
                    {{ Number(row.quantity) <= 0 ? 'Out of stock' : row.is_low ? 'Low' : 'OK' }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <AppPagination :meta="meta" @change="page = $event" />
      </div>
    </div>
  </div>
</template>

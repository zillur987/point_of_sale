<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { productApi, type Category, type Product } from '@/api/catalog'
import AppPagination from '@/components/ui/AppPagination.vue'
import { useCrudList } from '@/composables/useCrudList'
import { useNotify } from '@/composables/useNotify'
import { useServerErrors } from '@/composables/useServerErrors'
import { useCatalogLookups } from '@/stores/catalog'
import { confirmDelete } from '@/utils/confirm'
import { formatMoney } from '@/utils/money'

const notify = useNotify()
const lookups = useCatalogLookups()
const { message, capture } = useServerErrors()

const { items, meta, loading, page, filters, load } = useCrudList<
  Product,
  { search: string; category_id: string; brand_id: string; type: string; is_active: string }
>(productApi.list, { search: '', category_id: '', brand_id: '', type: '', is_active: '' })

onMounted(() => lookups.load(true))

function flatten(nodes: Category[], depth = 0): { id: number; label: string }[] {
  return nodes.flatMap((n) => [{ id: n.id, label: `${'— '.repeat(depth)}${n.name}` }, ...flatten(n.children ?? [], depth + 1)])
}
const categoryOptions = computed(() => flatten(lookups.categoryTree))

function priceLabel(product: Product): string {
  const prices = (product.variants ?? []).map((v) => v.sale_price)
  if (!prices.length) return '—'
  const min = Math.min(...prices)
  const max = Math.max(...prices)
  return min === max ? formatMoney(min) : `${formatMoney(min)} – ${formatMoney(max)}`
}

const skuLabel = (product: Product): string =>
  product.type === 'single' ? (product.variants?.[0]?.sku ?? '—') : `${product.variants?.length ?? 0} variants`

const barcodeLabel = (product: Product): string =>
  product.type === 'single' ? (product.variants?.[0]?.barcode ?? '—') : '—'

async function remove(product: Product): Promise<void> {
  if (!confirmDelete(`Delete product "${product.name}"?`)) return
  try {
    await productApi.remove(product.id)
    notify.success('Product deleted')
    await load()
  } catch (e) {
    capture(e)
    notify.error(message.value)
  }
}
</script>

<template>
  <div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h4 class="mb-0">Products</h4>
      <RouterLink :to="{ name: 'catalog.products.create' }" class="btn btn-primary">+ Add product</RouterLink>
    </div>

    <div class="card">
      <div class="card-body">
        <div class="row g-2 mb-3">
          <div class="col-12 col-md-4">
            <input v-model="filters.search" class="form-control" placeholder="Search name, SKU or barcode..." />
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
              <option v-for="b in lookups.brands" :key="b.id" :value="b.id">{{ b.name }}</option>
            </select>
          </div>
          <div class="col-6 col-md-2">
            <select v-model="filters.type" class="form-select">
              <option value="">All types</option>
              <option value="single">Single</option>
              <option value="variable">Variable</option>
            </select>
          </div>
          <div class="col-6 col-md-2">
            <select v-model="filters.is_active" class="form-select">
              <option value="">Any status</option>
              <option value="1">Active</option>
              <option value="0">Inactive</option>
            </select>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>Product</th><th>SKU</th><th>Barcode</th><th>Category</th><th>Brand</th>
                <th>Sale price</th><th>Status</th><th class="text-end">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="loading && !items.length"><td colspan="8" class="text-center text-muted py-4">Loading...</td></tr>
              <tr v-else-if="!items.length"><td colspan="8" class="text-center text-muted py-4">No products found</td></tr>
              <tr v-for="row in items" :key="row.id">
                <td>
                  <div class="fw-medium">{{ row.name }}</div>
                  <span class="badge bg-light text-dark border">{{ row.type }}</span>
                </td>
                <td>{{ skuLabel(row) }}</td>
                <td><code>{{ barcodeLabel(row) }}</code></td>
                <td>{{ row.category?.name ?? '—' }}</td>
                <td>{{ row.brand?.name ?? '—' }}</td>
                <td>{{ priceLabel(row) }}</td>
                <td>
                  <span class="badge" :class="row.is_active ? 'bg-success' : 'bg-secondary'">
                    {{ row.is_active ? 'Active' : 'Inactive' }}
                  </span>
                </td>
                <td class="text-end text-nowrap">
                  <RouterLink :to="{ name: 'catalog.products.edit', params: { id: row.id } }" class="btn btn-sm btn-outline-primary me-1">Edit</RouterLink>
                  <button class="btn btn-sm btn-outline-danger" @click="remove(row)">Delete</button>
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

<script setup lang="ts">
import { computed, onMounted, reactive, ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import {
  productApi,
  type Category,
  type ProductPayload,
  type ProductType,
  type VariantPayload,
} from '@/api/catalog'
import FormField from '@/components/ui/FormField.vue'
import { useNotify } from '@/composables/useNotify'
import { useServerErrors } from '@/composables/useServerErrors'
import { useCatalogLookups } from '@/stores/catalog'
import { toMajor, toMinor } from '@/utils/money'

interface VariantRow {
  id?: number
  name: string
  sku: string
  barcode: string
  cost: string // major units as typed by the user; converted to minor units on save
  sale: string
  attributesText: string // "Color=Red, Size=M"
  is_active: boolean
}

const props = defineProps<{ id?: string }>()

const router = useRouter()
const notify = useNotify()
const lookups = useCatalogLookups()
const { message, capture, clear, first } = useServerErrors()

const isEdit = computed(() => props.id !== undefined)
const loading = ref(false)
const saving = ref(false)

const newRow = (name = 'Default'): VariantRow => ({
  name, sku: '', barcode: '', cost: '0', sale: '0', attributesText: '', is_active: true,
})

const form = reactive({
  name: '',
  type: 'single' as ProductType,
  category_id: null as number | null,
  brand_id: null as number | null,
  unit_id: null as number | null,
  description: '',
  track_stock: true,
  alert_quantity: 0,
  tax_percent: 0, // shown as %, stored as basis points
  tax_inclusive: false,
  is_active: true,
})
const variants = ref<VariantRow[]>([newRow()])

function flatten(nodes: Category[], depth = 0): { id: number; label: string }[] {
  return nodes.flatMap((n) => [{ id: n.id, label: `${'— '.repeat(depth)}${n.name}` }, ...flatten(n.children ?? [], depth + 1)])
}
const categoryOptions = computed(() => flatten(lookups.categoryTree))

const parseAttributes = (text: string): Record<string, string> | null => {
  const entries = text
    .split(',')
    .map((pair) => pair.split('=').map((s) => s.trim()))
    .filter(([key, value]) => key && value) as [string, string][]
  return entries.length ? Object.fromEntries(entries) : null
}

const stringifyAttributes = (attrs: Record<string, string> | null): string =>
  attrs ? Object.entries(attrs).map(([k, v]) => `${k}=${v}`).join(', ') : ''

function setType(type: ProductType): void {
  form.type = type
  if (type === 'single') variants.value = variants.value.slice(0, 1) // single = exactly one variant
}

const addVariant = (): void => { variants.value.push(newRow('')) }
const removeVariant = (index: number): void => {
  if (variants.value.length > 1) variants.value.splice(index, 1)
}

onMounted(async () => {
  await lookups.load(true)
  if (!isEdit.value) return

  loading.value = true
  try {
    const { data } = await productApi.show(Number(props.id))
    const p = data.data
    Object.assign(form, {
      name: p.name,
      type: p.type,
      category_id: p.category_id,
      brand_id: p.brand_id,
      unit_id: p.unit_id,
      description: p.description ?? '',
      track_stock: p.track_stock,
      alert_quantity: Number(p.alert_quantity),
      tax_percent: p.tax_rate_bps / 100,
      tax_inclusive: p.tax_inclusive,
      is_active: p.is_active,
    })
    variants.value = (p.variants ?? []).map((v) => ({
      id: v.id,
      name: v.name,
      sku: v.sku,
      barcode: v.barcode ?? '',
      cost: String(toMajor(v.cost_price)),
      sale: String(toMajor(v.sale_price)),
      attributesText: stringifyAttributes(v.attributes),
      is_active: v.is_active,
    }))
  } finally {
    loading.value = false
  }
})

function buildPayload(): ProductPayload {
  return {
    name: form.name,
    type: form.type,
    category_id: form.category_id,
    brand_id: form.brand_id,
    unit_id: form.unit_id as number,
    description: form.description || null,
    track_stock: form.track_stock,
    alert_quantity: Number(form.alert_quantity) || 0,
    tax_rate_bps: Math.round(Number(form.tax_percent) * 100),
    tax_inclusive: form.tax_inclusive,
    is_active: form.is_active,
    variants: variants.value.map((row): VariantPayload => ({
      id: row.id,
      name: form.type === 'single' ? 'Default' : row.name || 'Default',
      sku: row.sku,
      barcode: row.barcode || null,
      cost_price: toMinor(row.cost),
      sale_price: toMinor(row.sale),
      attributes: parseAttributes(row.attributesText),
      is_active: row.is_active,
    })),
  }
}

async function submit(): Promise<void> {
  saving.value = true
  clear()
  try {
    if (isEdit.value) await productApi.update(Number(props.id), buildPayload())
    else await productApi.create(buildPayload())

    notify.success(isEdit.value ? 'Product updated' : 'Product created')
    await router.push({ name: 'catalog.products' })
  } catch (e) {
    capture(e)
    window.scrollTo({ top: 0, behavior: 'smooth' })
  } finally {
    saving.value = false
  }
}
</script>

<template>
  <div class="container-fluid py-3" style="max-width: 1100px">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h4 class="mb-0">{{ isEdit ? 'Edit product' : 'Add product' }}</h4>
      <RouterLink :to="{ name: 'catalog.products' }" class="btn btn-light">Back to products</RouterLink>
    </div>

    <div v-if="loading" class="text-center text-muted py-5">Loading...</div>

    <form v-else @submit.prevent="submit">
      <div v-if="message" class="alert alert-danger py-2">{{ message }}</div>

      <div class="card mb-3">
        <div class="card-header fw-semibold">Basic information</div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-8">
              <FormField label="Product name" required :error="first('name')">
                <input v-model="form.name" class="form-control" :class="{ 'is-invalid': first('name') }" placeholder="e.g. Mineral Water 500ml" />
              </FormField>
            </div>
            <div class="col-md-4">
              <FormField label="Product type" required :error="first('type')">
                <div class="btn-group w-100" role="group">
                  <input id="t-single" type="radio" class="btn-check" :checked="form.type === 'single'" @change="setType('single')" />
                  <label class="btn btn-outline-primary" for="t-single">Single</label>
                  <input id="t-variable" type="radio" class="btn-check" :checked="form.type === 'variable'" @change="setType('variable')" />
                  <label class="btn btn-outline-primary" for="t-variable">Variable</label>
                </div>
              </FormField>
            </div>
          </div>

          <div class="row">
            <div class="col-md-4">
              <FormField label="Category" :error="first('category_id')">
                <select v-model="form.category_id" class="form-select">
                  <option :value="null">— None —</option>
                  <option v-for="c in categoryOptions" :key="c.id" :value="c.id">{{ c.label }}</option>
                </select>
              </FormField>
            </div>
            <div class="col-md-4">
              <FormField label="Brand" :error="first('brand_id')">
                <select v-model="form.brand_id" class="form-select">
                  <option :value="null">— None —</option>
                  <option v-for="b in lookups.brands" :key="b.id" :value="b.id">{{ b.name }}</option>
                </select>
              </FormField>
            </div>
            <div class="col-md-4">
              <FormField label="Unit" required :error="first('unit_id')">
                <select v-model="form.unit_id" class="form-select" :class="{ 'is-invalid': first('unit_id') }">
                  <option :value="null" disabled>Select unit</option>
                  <option v-for="u in lookups.units" :key="u.id" :value="u.id">{{ u.name }} ({{ u.short_name }})</option>
                </select>
              </FormField>
            </div>
          </div>

          <FormField label="Description" :error="first('description')">
            <textarea v-model="form.description" class="form-control" rows="2" placeholder="Optional" />
          </FormField>
        </div>
      </div>

      <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
          <span class="fw-semibold">{{ form.type === 'single' ? 'Pricing & barcode' : 'Variants' }}</span>
          <button v-if="form.type === 'variable'" type="button" class="btn btn-sm btn-outline-primary" @click="addVariant">+ Add variant</button>
        </div>
        <div class="card-body">
          <div v-if="first('variants')" class="alert alert-danger py-2">{{ first('variants') }}</div>

          <div class="table-responsive">
            <table class="table align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th v-if="form.type === 'variable'">Variant name</th>
                  <th>SKU *</th>
                  <th>Barcode</th>
                  <th>Cost price</th>
                  <th>Sale price *</th>
                  <th v-if="form.type === 'variable'">Attributes</th>
                  <th v-if="form.type === 'variable'" />
                </tr>
              </thead>
              <tbody>
                <tr v-for="(row, i) in variants" :key="row.id ?? `new-${i}`">
                  <td v-if="form.type === 'variable'">
                    <input v-model="row.name" class="form-control" placeholder="e.g. Red / M" />
                  </td>
                  <td style="min-width: 140px">
                    <input v-model="row.sku" class="form-control" :class="{ 'is-invalid': first(`variants.${i}.sku`) }" placeholder="SKU" />
                    <div class="text-danger small">{{ first(`variants.${i}.sku`) }}</div>
                  </td>
                  <td style="min-width: 160px">
                    <input v-model="row.barcode" class="form-control" :class="{ 'is-invalid': first(`variants.${i}.barcode`) }" placeholder="Auto-generated if empty" />
                    <div class="text-danger small">{{ first(`variants.${i}.barcode`) }}</div>
                  </td>
                  <td style="min-width: 110px">
                    <input v-model="row.cost" type="number" min="0" step="0.01" class="form-control" :class="{ 'is-invalid': first(`variants.${i}.cost_price`) }" />
                  </td>
                  <td style="min-width: 110px">
                    <input v-model="row.sale" type="number" min="0" step="0.01" class="form-control" :class="{ 'is-invalid': first(`variants.${i}.sale_price`) }" />
                  </td>
                  <td v-if="form.type === 'variable'" style="min-width: 180px">
                    <input v-model="row.attributesText" class="form-control" placeholder="Color=Red, Size=M" />
                  </td>
                  <td v-if="form.type === 'variable'" class="text-end">
                    <button type="button" class="btn btn-sm btn-outline-danger" :disabled="variants.length === 1" @click="removeVariant(i)">&times;</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="card mb-3">
        <div class="card-header fw-semibold">Stock & tax</div>
        <div class="card-body">
          <div class="row align-items-end">
            <div class="col-md-3">
              <FormField label="Low-stock alert quantity" :error="first('alert_quantity')">
                <input v-model.number="form.alert_quantity" type="number" min="0" step="any" class="form-control" />
              </FormField>
            </div>
            <div class="col-md-3">
              <FormField label="Tax rate (%)" :error="first('tax_rate_bps')">
                <input v-model.number="form.tax_percent" type="number" min="0" max="100" step="0.01" class="form-control" />
              </FormField>
            </div>
            <div class="col-md-6 mb-3 d-flex flex-wrap gap-4">
              <div class="form-check form-switch">
                <input id="p-track" v-model="form.track_stock" class="form-check-input" type="checkbox" />
                <label class="form-check-label" for="p-track">Track stock</label>
              </div>
              <div class="form-check form-switch">
                <input id="p-taxinc" v-model="form.tax_inclusive" class="form-check-input" type="checkbox" />
                <label class="form-check-label" for="p-taxinc">Price includes tax</label>
              </div>
              <div class="form-check form-switch">
                <input id="p-active" v-model="form.is_active" class="form-check-input" type="checkbox" />
                <label class="form-check-label" for="p-active">Active</label>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="d-flex justify-content-end gap-2">
        <RouterLink :to="{ name: 'catalog.products' }" class="btn btn-light">Cancel</RouterLink>
        <button type="submit" class="btn btn-primary" :disabled="saving">{{ saving ? 'Saving...' : isEdit ? 'Update product' : 'Save product' }}</button>
      </div>
    </form>
  </div>
</template>

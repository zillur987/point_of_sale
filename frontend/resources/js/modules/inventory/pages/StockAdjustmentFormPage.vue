<script setup lang="ts">
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import type { ProductVariant } from '@/api/catalog'
import { ADJUSTMENT_REASONS, adjustmentApi, type AdjustmentDirection, type AdjustmentReason } from '@/api/inventory'
import VariantPicker from '@/components/inventory/VariantPicker.vue'
import FormField from '@/components/ui/FormField.vue'
import { useNotify } from '@/composables/useNotify'
import { useServerErrors } from '@/composables/useServerErrors'
import { useInventoryLookups } from '@/stores/inventory'
import { todayISO, variantLabel } from '@/utils/inventory'

interface Row {
  variantId: number
  label: string
  sku: string
  unit: string
  allowDecimal: boolean
  direction: AdjustmentDirection
  quantity: string
}

const router = useRouter()
const notify = useNotify()
const inventory = useInventoryLookups()
const { message, capture, clear, first } = useServerErrors()

const saving = ref(false)
const form = reactive({
  warehouse_id: null as number | null,
  reason: 'correction' as AdjustmentReason,
  adjusted_at: todayISO(),
  note: '',
})
const rows = ref<Row[]>([])

const isOpening = computed(() => form.reason === 'opening_stock')

onMounted(async () => {
  await inventory.load(true)
  form.warehouse_id = inventory.defaultWarehouse()?.id ?? inventory.warehouses[0]?.id ?? null
})

// Opening stock can only add stock.
watch(isOpening, (opening) => {
  if (opening) rows.value.forEach((r) => (r.direction = 'in'))
})

function addVariant(variant: ProductVariant): void {
  if (rows.value.some((r) => r.variantId === variant.id)) {
    notify.error('That product is already in the list')
    return
  }
  const unit = variant.product?.unit
  rows.value.push({
    variantId: variant.id,
    label: variantLabel(variant),
    sku: variant.sku,
    unit: unit?.short_name ?? '',
    allowDecimal: unit?.allow_decimal ?? true, // the server enforces the real rule
    direction: 'in',
    quantity: '',
  })
}

const removeRow = (index: number): void => { rows.value.splice(index, 1) }

async function submit(): Promise<void> {
  clear()
  if (!form.warehouse_id) return notify.error('Choose a warehouse')
  if (!rows.value.length) return notify.error('Add at least one product')
  if (rows.value.some((r) => !(Number(r.quantity) > 0))) return notify.error('Every quantity must be greater than zero')

  saving.value = true
  try {
    await adjustmentApi.create({
      warehouse_id: form.warehouse_id,
      reason: form.reason,
      adjusted_at: form.adjusted_at || null,
      note: form.note || null,
      items: rows.value.map((r) => ({
        product_variant_id: r.variantId,
        direction: r.direction,
        quantity: Number(r.quantity),
      })),
    })
    notify.success('Stock adjusted')
    await router.push({ name: 'inventory.adjustments' })
  } catch (e) {
    capture(e)
    window.scrollTo({ top: 0, behavior: 'smooth' })
  } finally {
    saving.value = false
  }
}
</script>

<template>
  <div class="container-fluid py-3" style="max-width: 1000px">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h4 class="mb-0">New stock adjustment</h4>
      <RouterLink :to="{ name: 'inventory.adjustments' }" class="btn btn-light">Back to adjustments</RouterLink>
    </div>

    <form @submit.prevent="submit">
      <div v-if="message" class="alert alert-danger py-2">{{ message }}</div>

      <div class="card mb-3">
        <div class="card-header fw-semibold">Details</div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-4">
              <FormField label="Warehouse" required :error="first('warehouse_id')">
                <select v-model="form.warehouse_id" class="form-select" :class="{ 'is-invalid': first('warehouse_id') }">
                  <option :value="null" disabled>Select warehouse</option>
                  <option v-for="w in inventory.warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
                </select>
              </FormField>
            </div>
            <div class="col-md-4">
              <FormField label="Reason" required :error="first('reason')">
                <select v-model="form.reason" class="form-select">
                  <option v-for="r in ADJUSTMENT_REASONS" :key="r.value" :value="r.value">{{ r.label }}</option>
                </select>
              </FormField>
            </div>
            <div class="col-md-4">
              <FormField label="Date" :error="first('adjusted_at')">
                <input v-model="form.adjusted_at" type="date" class="form-control" :max="todayISO()" />
              </FormField>
            </div>
          </div>
          <FormField label="Note" :error="first('note')">
            <input v-model="form.note" class="form-control" placeholder="Optional" maxlength="255" />
          </FormField>
        </div>
      </div>

      <div class="card mb-3">
        <div class="card-header fw-semibold">Products</div>
        <div class="card-body">
          <div v-if="first('items')" class="alert alert-danger py-2">{{ first('items') }}</div>
          <VariantPicker class="mb-3" @select="addVariant" />

          <div v-if="!rows.length" class="text-center text-muted py-4">Search above to add products.</div>
          <div v-else class="table-responsive">
            <table class="table align-middle mb-0">
              <thead class="table-light">
                <tr><th>Product</th><th style="width: 150px">Change</th><th style="width: 170px">Quantity</th><th style="width: 50px" /></tr>
              </thead>
              <tbody>
                <tr v-for="(row, i) in rows" :key="row.variantId">
                  <td>
                    <div class="fw-medium">{{ row.label }}</div>
                    <small class="text-muted">{{ row.sku }}</small>
                    <div class="text-danger small">{{ first(`items.${i}.product_variant_id`) }}</div>
                  </td>
                  <td>
                    <select v-model="row.direction" class="form-select" :disabled="isOpening">
                      <option value="in">Add (+)</option>
                      <option value="out">Remove (−)</option>
                    </select>
                    <div class="text-danger small">{{ first(`items.${i}.direction`) }}</div>
                  </td>
                  <td>
                    <div class="input-group">
                      <input
                        v-model="row.quantity"
                        type="number"
                        min="0"
                        :step="row.allowDecimal ? 'any' : '1'"
                        class="form-control"
                        :class="{ 'is-invalid': first(`items.${i}.quantity`) }"
                      />
                      <span v-if="row.unit" class="input-group-text">{{ row.unit }}</span>
                    </div>
                    <div class="text-danger small">{{ first(`items.${i}.quantity`) }}</div>
                  </td>
                  <td class="text-end">
                    <button type="button" class="btn btn-sm btn-outline-danger" @click="removeRow(i)">&times;</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="d-flex justify-content-end gap-2">
        <RouterLink :to="{ name: 'inventory.adjustments' }" class="btn btn-light">Cancel</RouterLink>
        <button type="submit" class="btn btn-primary" :disabled="saving">{{ saving ? 'Saving...' : 'Save adjustment' }}</button>
      </div>
    </form>
  </div>
</template>

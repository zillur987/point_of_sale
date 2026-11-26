<script setup lang="ts">
import { reactive, ref } from 'vue'
import { warehouseApi, type Warehouse, type WarehousePayload } from '@/api/inventory'
import AppDrawer from '@/components/ui/AppDrawer.vue'
import AppPagination from '@/components/ui/AppPagination.vue'
import FormField from '@/components/ui/FormField.vue'
import { useCrudList } from '@/composables/useCrudList'
import { useNotify } from '@/composables/useNotify'
import { useServerErrors } from '@/composables/useServerErrors'
import { useInventoryLookups } from '@/stores/inventory'
import { confirmDelete } from '@/utils/confirm'

const notify = useNotify()
const lookups = useInventoryLookups()
const { errors, message, capture, clear, first } = useServerErrors()
const { items, meta, loading, page, filters, load } = useCrudList<Warehouse, { search: string }>(
  warehouseApi.list,
  { search: '' },
)

const emptyForm = (): WarehousePayload => ({
  name: '', code: '', phone: null, address: null, is_default: false, is_active: true,
})

const drawerOpen = ref(false)
const saving = ref(false)
const editing = ref<Warehouse | null>(null)
const form = reactive<WarehousePayload>(emptyForm())

function openCreate(): void {
  editing.value = null
  Object.assign(form, emptyForm())
  clear()
  drawerOpen.value = true
}

function openEdit(row: Warehouse): void {
  editing.value = row
  Object.assign(form, {
    name: row.name, code: row.code, phone: row.phone, address: row.address,
    is_default: row.is_default, is_active: row.is_active,
  })
  clear()
  drawerOpen.value = true
}

async function save(): Promise<void> {
  saving.value = true
  clear()
  try {
    const payload = { ...form, phone: form.phone || null, address: form.address || null }
    if (editing.value) await warehouseApi.update(editing.value.id, payload)
    else await warehouseApi.create(payload)

    notify.success(editing.value ? 'Warehouse updated' : 'Warehouse created')
    drawerOpen.value = false
    await Promise.all([load(), lookups.load(true)])
  } catch (e) {
    capture(e)
  } finally {
    saving.value = false
  }
}

async function remove(row: Warehouse): Promise<void> {
  if (!confirmDelete(`Delete warehouse "${row.name}"?`)) return
  try {
    await warehouseApi.remove(row.id)
    notify.success('Warehouse deleted')
    await Promise.all([load(), lookups.load(true)])
  } catch (e) {
    capture(e)
    notify.error(message.value)
  }
}
</script>

<template>
  <div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h4 class="mb-0">Warehouses</h4>
      <button class="btn btn-primary" @click="openCreate">+ Add warehouse</button>
    </div>

    <div class="card">
      <div class="card-body">
        <input v-model="filters.search" class="form-control mb-3" style="max-width: 320px" placeholder="Search name or code..." />

        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr><th>Code</th><th>Name</th><th>Phone</th><th>Address</th><th>Status</th><th class="text-end">Actions</th></tr>
            </thead>
            <tbody>
              <tr v-if="loading && !items.length"><td colspan="6" class="text-center text-muted py-4">Loading...</td></tr>
              <tr v-else-if="!items.length"><td colspan="6" class="text-center text-muted py-4">No warehouses found</td></tr>
              <tr v-for="row in items" :key="row.id">
                <td><code>{{ row.code }}</code></td>
                <td class="fw-medium">
                  {{ row.name }}
                  <span v-if="row.is_default" class="badge bg-primary ms-1">Default</span>
                </td>
                <td>{{ row.phone ?? '—' }}</td>
                <td>{{ row.address ?? '—' }}</td>
                <td>
                  <span class="badge" :class="row.is_active ? 'bg-success' : 'bg-secondary'">
                    {{ row.is_active ? 'Active' : 'Inactive' }}
                  </span>
                </td>
                <td class="text-end text-nowrap">
                  <button class="btn btn-sm btn-outline-primary me-1" @click="openEdit(row)">Edit</button>
                  <button class="btn btn-sm btn-outline-danger" :disabled="row.is_default" @click="remove(row)">Delete</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <AppPagination :meta="meta" @change="page = $event" />
      </div>
    </div>

    <AppDrawer v-model="drawerOpen" :title="editing ? 'Edit warehouse' : 'Add warehouse'">
      <div v-if="message && !Object.keys(errors).length" class="alert alert-danger py-2">{{ message }}</div>

      <FormField label="Name" required :error="first('name')">
        <input v-model="form.name" class="form-control" :class="{ 'is-invalid': first('name') }" placeholder="e.g. Main store" />
      </FormField>
      <FormField label="Code" required :error="first('code')">
        <input v-model="form.code" class="form-control text-uppercase" :class="{ 'is-invalid': first('code') }" placeholder="e.g. MAIN" maxlength="20" />
      </FormField>
      <FormField label="Phone" :error="first('phone')">
        <input v-model="form.phone" class="form-control" />
      </FormField>
      <FormField label="Address" :error="first('address')">
        <textarea v-model="form.address" class="form-control" rows="2" />
      </FormField>

      <div class="form-check form-switch mb-2">
        <input id="wh-default" v-model="form.is_default" class="form-check-input" type="checkbox" :disabled="editing?.is_default" />
        <label class="form-check-label" for="wh-default">Default warehouse (POS sells from here)</label>
      </div>
      <div class="form-check form-switch">
        <input id="wh-active" v-model="form.is_active" class="form-check-input" type="checkbox" :disabled="form.is_default" />
        <label class="form-check-label" for="wh-active">Active</label>
      </div>

      <template #footer>
        <button class="btn btn-light" @click="drawerOpen = false">Cancel</button>
        <button class="btn btn-primary" :disabled="saving" @click="save">{{ saving ? 'Saving...' : 'Save' }}</button>
      </template>
    </AppDrawer>
  </div>
</template>

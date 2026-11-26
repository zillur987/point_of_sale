<script setup lang="ts">
import { reactive, ref } from 'vue'
import { brandApi, type Brand, type BrandPayload } from '@/api/catalog'
import AppDrawer from '@/components/ui/AppDrawer.vue'
import AppPagination from '@/components/ui/AppPagination.vue'
import FormField from '@/components/ui/FormField.vue'
import { useCrudList } from '@/composables/useCrudList'
import { useNotify } from '@/composables/useNotify'
import { useServerErrors } from '@/composables/useServerErrors'
import { confirmDelete } from '@/utils/confirm'

const notify = useNotify()
const { errors, message, capture, clear, first } = useServerErrors()
const { items, meta, loading, page, filters, load } = useCrudList<Brand, { search: string }>(
  brandApi.list,
  { search: '' },
)

const emptyForm = (): BrandPayload => ({ name: '', is_active: true })

const drawerOpen = ref(false)
const saving = ref(false)
const editingId = ref<number | null>(null)
const form = reactive<BrandPayload>(emptyForm())

function openCreate(): void {
  editingId.value = null
  Object.assign(form, emptyForm())
  clear()
  drawerOpen.value = true
}

function openEdit(row: Brand): void {
  editingId.value = row.id
  Object.assign(form, { name: row.name, is_active: row.is_active })
  clear()
  drawerOpen.value = true
}

async function save(): Promise<void> {
  saving.value = true
  clear()
  try {
    if (editingId.value) await brandApi.update(editingId.value, { ...form })
    else await brandApi.create({ ...form })

    notify.success(editingId.value ? 'Brand updated' : 'Brand created')
    drawerOpen.value = false
    await load()
  } catch (e) {
    capture(e)
  } finally {
    saving.value = false
  }
}

async function remove(row: Brand): Promise<void> {
  if (!confirmDelete(`Delete brand "${row.name}"?`)) return
  try {
    await brandApi.remove(row.id)
    notify.success('Brand deleted')
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
      <h4 class="mb-0">Brands</h4>
      <button class="btn btn-primary" @click="openCreate">+ Add brand</button>
    </div>

    <div class="card">
      <div class="card-body">
        <input v-model="filters.search" class="form-control mb-3" style="max-width: 320px" placeholder="Search brands..." />

        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr><th>Name</th><th>Status</th><th class="text-end">Actions</th></tr>
            </thead>
            <tbody>
              <tr v-if="loading && !items.length"><td colspan="3" class="text-center text-muted py-4">Loading...</td></tr>
              <tr v-else-if="!items.length"><td colspan="3" class="text-center text-muted py-4">No brands found</td></tr>
              <tr v-for="row in items" :key="row.id">
                <td class="fw-medium">{{ row.name }}</td>
                <td>
                  <span class="badge" :class="row.is_active ? 'bg-success' : 'bg-secondary'">
                    {{ row.is_active ? 'Active' : 'Inactive' }}
                  </span>
                </td>
                <td class="text-end">
                  <button class="btn btn-sm btn-outline-primary me-1" @click="openEdit(row)">Edit</button>
                  <button class="btn btn-sm btn-outline-danger" @click="remove(row)">Delete</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <AppPagination :meta="meta" @change="page = $event" />
      </div>
    </div>

    <AppDrawer v-model="drawerOpen" :title="editingId ? 'Edit brand' : 'Add brand'">
      <div v-if="message && !Object.keys(errors).length" class="alert alert-danger py-2">{{ message }}</div>

      <FormField label="Name" required :error="first('name')">
        <input v-model="form.name" class="form-control" :class="{ 'is-invalid': first('name') }" placeholder="e.g. Coca-Cola" />
      </FormField>

      <div class="form-check form-switch">
        <input id="brand-active" v-model="form.is_active" class="form-check-input" type="checkbox" />
        <label class="form-check-label" for="brand-active">Active</label>
      </div>

      <template #footer>
        <button class="btn btn-light" @click="drawerOpen = false">Cancel</button>
        <button class="btn btn-primary" :disabled="saving" @click="save">{{ saving ? 'Saving...' : 'Save' }}</button>
      </template>
    </AppDrawer>
  </div>
</template>

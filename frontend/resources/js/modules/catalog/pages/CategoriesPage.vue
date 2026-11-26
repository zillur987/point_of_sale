<script setup lang="ts">
import { reactive, ref } from 'vue'
import { categoryApi, type Category, type CategoryPayload } from '@/api/catalog'
import AppDrawer from '@/components/ui/AppDrawer.vue'
import AppPagination from '@/components/ui/AppPagination.vue'
import FormField from '@/components/ui/FormField.vue'
import { useCrudList } from '@/composables/useCrudList'
import { useNotify } from '@/composables/useNotify'
import { useServerErrors } from '@/composables/useServerErrors'
import { confirmDelete } from '@/utils/confirm'

interface Option { id: number; label: string }

const notify = useNotify()
const { errors, message, capture, clear, first } = useServerErrors()
const { items, meta, loading, page, filters, load } = useCrudList<Category, { search: string }>(
  categoryApi.list,
  { search: '' },
)

const emptyForm = (): CategoryPayload => ({ name: '', parent_id: null, description: null, sort_order: 0, is_active: true })

const drawerOpen = ref(false)
const saving = ref(false)
const editingId = ref<number | null>(null)
const form = reactive<CategoryPayload>(emptyForm())
const parentOptions = ref<Option[]>([])

/** Flattens the tree for a <select>, skipping `skipId` and its whole subtree (prevents cycles in the UI). */
function flatten(nodes: Category[], depth = 0, skipId: number | null = null): Option[] {
  return nodes.flatMap((node) =>
    node.id === skipId
      ? []
      : [{ id: node.id, label: `${'— '.repeat(depth)}${node.name}` }, ...flatten(node.children ?? [], depth + 1, skipId)],
  )
}

async function loadParents(skipId: number | null = null): Promise<void> {
  const { data } = await categoryApi.tree()
  parentOptions.value = flatten(data.data, 0, skipId)
}

async function openCreate(): Promise<void> {
  editingId.value = null
  Object.assign(form, emptyForm())
  clear()
  await loadParents()
  drawerOpen.value = true
}

async function openEdit(row: Category): Promise<void> {
  editingId.value = row.id
  Object.assign(form, {
    name: row.name,
    parent_id: row.parent_id,
    description: row.description,
    sort_order: row.sort_order,
    is_active: row.is_active,
  })
  clear()
  await loadParents(row.id)
  drawerOpen.value = true
}

async function save(): Promise<void> {
  saving.value = true
  clear()
  try {
    if (editingId.value) await categoryApi.update(editingId.value, { ...form })
    else await categoryApi.create({ ...form })

    notify.success(editingId.value ? 'Category updated' : 'Category created')
    drawerOpen.value = false
    await load()
  } catch (e) {
    capture(e)
  } finally {
    saving.value = false
  }
}

async function remove(row: Category): Promise<void> {
  if (!confirmDelete(`Delete category "${row.name}"?`)) return
  try {
    await categoryApi.remove(row.id)
    notify.success('Category deleted')
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
      <h4 class="mb-0">Categories</h4>
      <button class="btn btn-primary" @click="openCreate">+ Add category</button>
    </div>

    <div class="card">
      <div class="card-body">
        <input v-model="filters.search" class="form-control mb-3" style="max-width: 320px" placeholder="Search categories..." />

        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr><th>Name</th><th>Parent</th><th>Order</th><th>Status</th><th class="text-end">Actions</th></tr>
            </thead>
            <tbody>
              <tr v-if="loading && !items.length"><td colspan="5" class="text-center text-muted py-4">Loading...</td></tr>
              <tr v-else-if="!items.length"><td colspan="5" class="text-center text-muted py-4">No categories found</td></tr>
              <tr v-for="row in items" :key="row.id">
                <td class="fw-medium">{{ row.name }}</td>
                <td>{{ row.parent?.name ?? '—' }}</td>
                <td>{{ row.sort_order }}</td>
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

    <AppDrawer v-model="drawerOpen" :title="editingId ? 'Edit category' : 'Add category'">
      <div v-if="message && !Object.keys(errors).length" class="alert alert-danger py-2">{{ message }}</div>

      <FormField label="Name" required :error="first('name')">
        <input v-model="form.name" class="form-control" :class="{ 'is-invalid': first('name') }" placeholder="e.g. Beverages" />
      </FormField>

      <FormField label="Parent category" :error="first('parent_id')">
        <select v-model="form.parent_id" class="form-select">
          <option :value="null">— None (top level) —</option>
          <option v-for="o in parentOptions" :key="o.id" :value="o.id">{{ o.label }}</option>
        </select>
      </FormField>

      <FormField label="Description" :error="first('description')">
        <textarea v-model="form.description" class="form-control" rows="3" placeholder="Optional" />
      </FormField>

      <FormField label="Sort order" :error="first('sort_order')">
        <input v-model.number="form.sort_order" type="number" min="0" class="form-control" />
      </FormField>

      <div class="form-check form-switch">
        <input id="cat-active" v-model="form.is_active" class="form-check-input" type="checkbox" />
        <label class="form-check-label" for="cat-active">Active</label>
      </div>

      <template #footer>
        <button class="btn btn-light" @click="drawerOpen = false">Cancel</button>
        <button class="btn btn-primary" :disabled="saving" @click="save">{{ saving ? 'Saving...' : 'Save' }}</button>
      </template>
    </AppDrawer>
  </div>
</template>

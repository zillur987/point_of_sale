<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { customerApi } from '@/api/customers'
import type { CustomerGroup } from '@/api/customers'

const rows = ref<CustomerGroup[]>([])
const loading = ref(false)
const saving = ref(false)
const error = ref('')
const showDrawer = ref(false)
const editingId = ref<number | null>(null)
const form = ref({ name: '', description: '', is_active: true })

async function load() {
    loading.value = true
    error.value = ''
    try {
        const response = await customerApi.groups()
        rows.value = response.data.data
    } catch (e: any) {
        error.value = e?.response?.data?.message || 'Could not load customer groups.'
    } finally {
        loading.value = false
    }
}

function create() {
    editingId.value = null
    form.value = { name: '', description: '', is_active: true }
    showDrawer.value = true
}

function edit(group: CustomerGroup) {
    editingId.value = group.id
    form.value = {
        name: group.name,
        description: group.description || '',
        is_active: group.is_active,
    }
    showDrawer.value = true
}

async function save() {
    saving.value = true
    error.value = ''
    try {
        if (editingId.value) {
            await customerApi.updateGroup(editingId.value, form.value)
        } else {
            await customerApi.createGroup(form.value)
        }
        showDrawer.value = false
        await load()
    } catch (e: any) {
        error.value = e?.response?.data?.message || 'Could not save customer group.'
    } finally {
        saving.value = false
    }
}

onMounted(load)
</script>

<template>
    <section class="page">
        <header>
            <div><small>CUSTOMER SEGMENTATION</small><h1>Customer groups</h1><p>Organize customers into useful business segments.</p></div>
            <button @click="create">＋ Add group</button>
        </header>

        <p v-if="error" class="error">{{ error }}</p>

        <div class="card">
            <div class="scroll">
                <table>
                    <thead><tr><th>Group</th><th>Description</th><th>Customers</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                        <tr v-for="group in rows" :key="group.id">
                            <td><b>{{ group.name }}</b></td>
                            <td>{{ group.description || '—' }}</td>
                            <td>{{ group.customers_count ?? 0 }}</td>
                            <td><span :class="group.is_active ? 'active' : 'inactive'">{{ group.is_active ? 'Active' : 'Inactive' }}</span></td>
                            <td><button class="light" @click="edit(group)">Edit</button></td>
                        </tr>
                        <tr v-if="!rows.length"><td colspan="5" class="empty">{{ loading ? 'Loading...' : 'No customer groups found.' }}</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div v-if="showDrawer" class="overlay" @click.self="showDrawer = false">
            <form class="drawer" @submit.prevent="save">
                <div class="drawer-head"><div><small>CUSTOMER SEGMENT</small><h2>{{ editingId ? 'Edit group' : 'Add group' }}</h2></div><button type="button" class="light" @click="showDrawer = false">✕</button></div>
                <label>Group name<input v-model="form.name" required maxlength="120"></label>
                <label>Description<textarea v-model="form.description" rows="3" maxlength="500"></textarea></label>
                <label class="check"><input v-model="form.is_active" type="checkbox"> Active group</label>
                <footer><button type="button" class="light" @click="showDrawer = false">Cancel</button><button type="submit" :disabled="saving">{{ saving ? 'Saving...' : 'Save group' }}</button></footer>
            </form>
        </div>
    </section>
</template>

<style scoped>
.page{color:#172033}.page header,.drawer-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px}.page small,.drawer small{font-size:10px;letter-spacing:1.2px;color:#64748b;font-weight:800}.page h1{font-size:25px;margin:5px 0}.page p{font-size:13px;color:#7b8798}.page header>button,.drawer footer button[type=submit]{border:0;background:#172033;color:#fff;padding:10px 14px;border-radius:8px;cursor:pointer}.card{background:#fff;border:1px solid #e7ebf1;border-radius:13px;overflow:hidden}.scroll{overflow:auto}table{width:100%;border-collapse:collapse}th,td{text-align:left;padding:14px 15px;border-top:1px solid #edf0f4;font-size:12px}th{background:#f8fafc;color:#64748b;text-transform:uppercase;font-size:10px}td{color:#475569}.active,.inactive{padding:5px 8px;border-radius:20px;background:#ecfdf5;color:#047857;font-size:10px}.inactive{background:#f1f5f9;color:#64748b}.light{background:#fff!important;color:#334155!important;border:1px solid #dfe5ed!important;padding:8px 11px;border-radius:7px}.empty{text-align:center;padding:30px;color:#94a3b8}.overlay{position:fixed;inset:0;background:#0f172a66;display:flex;justify-content:flex-end;z-index:1000}.drawer{background:#fff;width:min(480px,100%);height:100%;overflow:auto;padding:25px;box-shadow:-10px 0 35px #0002}.drawer h2{margin:5px 0 0}.drawer label{display:flex;flex-direction:column;gap:6px;font-size:12px;font-weight:700;margin:14px 0}.drawer input,.drawer textarea{border:1px solid #dfe5ed;border-radius:7px;padding:10px;font:inherit}.drawer .check{flex-direction:row;align-items:center}.drawer .check input{width:auto}.drawer footer{display:flex;justify-content:flex-end;gap:10px;margin-top:22px}.error{color:#be123c}
</style>

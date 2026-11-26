<script setup lang="ts">
import { onMounted, ref, watch } from 'vue'
import { RouterLink } from 'vue-router'
import { customerApi } from '@/api/customers'
import type { Customer, CustomerGroup } from '@/api/customers'

const rows = ref<Customer[]>([])
const groups = ref<CustomerGroup[]>([])
const loading = ref(false)
const saving = ref(false)
const search = ref('')
const groupId = ref('')
const status = ref('')
const page = ref(1)
const lastPage = ref(1)
const error = ref('')
const showDrawer = ref(false)
const editingId = ref<number | null>(null)

const blank = () => ({
    customer_group_id: null as number | null,
    name: '',
    email: '',
    phone: '',
    address: '',
    city: '',
    country: 'Bangladesh',
    notes: '',
    is_active: true,
})
const form = ref(blank())

async function load() {
    loading.value = true
    error.value = ''
    try {
        const response = await customerApi.customers({
            search: search.value || undefined,
            customer_group_id: groupId.value || undefined,
            is_active: status.value === '' ? undefined : status.value,
            page: page.value,
        })
        rows.value = response.data.data
        lastPage.value = response.data.meta?.last_page || 1
    } catch (e: any) {
        error.value = e?.response?.data?.message || 'Could not load customers.'
    } finally {
        loading.value = false
    }
}

async function loadGroups() {
    try {
        const response = await customerApi.groups()
        groups.value = response.data.data
    } catch {
        // Group filtering remains optional if the user lacks group access.
    }
}

function create() {
    editingId.value = null
    form.value = blank()
    showDrawer.value = true
}

function edit(customer: Customer) {
    editingId.value = customer.id
    form.value = {
        customer_group_id: customer.customer_group_id,
        name: customer.name,
        email: customer.email || '',
        phone: customer.phone || '',
        address: customer.address || '',
        city: customer.city || '',
        country: customer.country || '',
        notes: customer.notes || '',
        is_active: customer.is_active,
    }
    showDrawer.value = true
}

async function save() {
    saving.value = true
    error.value = ''
    try {
        const payload = {
            ...form.value,
            email: form.value.email || null,
            phone: form.value.phone || null,
            address: form.value.address || null,
            city: form.value.city || null,
            country: form.value.country || null,
            notes: form.value.notes || null,
        }

        if (editingId.value) {
            await customerApi.updateCustomer(editingId.value, payload)
        } else {
            await customerApi.createCustomer(payload)
        }

        showDrawer.value = false
        await load()
    } catch (e: any) {
        error.value = e?.response?.data?.message || 'Could not save customer.'
    } finally {
        saving.value = false
    }
}

watch([search, groupId, status], () => {
    page.value = 1
    load()
})

onMounted(() => {
    load()
    loadGroups()
})
</script>

<template>
    <section class="page">
        <header>
            <div>
                <small>CUSTOMER RELATIONSHIPS</small>
                <h1>Customers</h1>
                <p>Manage customer profiles, groups and loyalty balances.</p>
            </div>
            <button @click="create">＋ Add customer</button>
        </header>

        <p v-if="error" class="error">{{ error }}</p>

        <div class="card">
            <div class="filters">
                <input v-model="search" placeholder="Search name, phone or email">
                <select v-model="groupId">
                    <option value="">All groups</option>
                    <option v-for="group in groups" :key="group.id" :value="String(group.id)">
                        {{ group.name }}
                    </option>
                </select>
                <select v-model="status">
                    <option value="">All statuses</option>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
                <span>{{ rows.length }} customers on this page</span>
            </div>

            <div class="scroll">
                <table>
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Contact</th>
                            <th>Group</th>
                            <th>Loyalty points</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="customer in rows" :key="customer.id">
                            <td>
                                <b>{{ customer.name }}</b>
                                <small>{{ customer.email || 'No email added' }}</small>
                            </td>
                            <td>{{ customer.phone || '—' }}</td>
                            <td>{{ customer.group?.name || 'General' }}</td>
                            <td><b>{{ customer.points_balance.toLocaleString() }}</b></td>
                            <td>
                                <span :class="customer.is_active ? 'active' : 'inactive'">
                                    {{ customer.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="actions">
                                <RouterLink :to="{ name: 'customers.show', params: { id: customer.id } }">View</RouterLink>
                                <button class="light" @click="edit(customer)">Edit</button>
                            </td>
                        </tr>
                        <tr v-if="!rows.length">
                            <td colspan="6" class="empty">
                                {{ loading ? 'Loading customers...' : 'No customers found.' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <footer>
                <button class="light" :disabled="page <= 1 || loading" @click="page--; load()">Previous</button>
                <span>Page {{ page }} of {{ lastPage }}</span>
                <button class="light" :disabled="page >= lastPage || loading" @click="page++; load()">Next</button>
            </footer>
        </div>

        <div v-if="showDrawer" class="overlay" @click.self="showDrawer = false">
            <form class="drawer" @submit.prevent="save">
                <div class="drawer-head">
                    <div>
                        <small>CUSTOMER PROFILE</small>
                        <h2>{{ editingId ? 'Edit customer' : 'Add customer' }}</h2>
                    </div>
                    <button type="button" class="light" @click="showDrawer = false">✕</button>
                </div>

                <label>Full name<input v-model="form.name" required maxlength="150"></label>
                <div class="two">
                    <label>Phone<input v-model="form.phone" maxlength="40"></label>
                    <label>Email<input v-model="form.email" type="email" maxlength="180"></label>
                </div>
                <label>Customer group
                    <select v-model="form.customer_group_id">
                        <option :value="null">General customer</option>
                        <option v-for="group in groups" :key="group.id" :value="group.id">{{ group.name }}</option>
                    </select>
                </label>
                <div class="two">
                    <label>City<input v-model="form.city"></label>
                    <label>Country<input v-model="form.country"></label>
                </div>
                <label>Address<textarea v-model="form.address" rows="2"></textarea></label>
                <label>Notes<textarea v-model="form.notes" rows="3"></textarea></label>
                <label class="check"><input v-model="form.is_active" type="checkbox"> Active customer</label>

                <footer>
                    <button type="button" class="light" @click="showDrawer = false">Cancel</button>
                    <button type="submit" :disabled="saving">{{ saving ? 'Saving...' : 'Save customer' }}</button>
                </footer>
            </form>
        </div>
    </section>
</template>

<style scoped>
.page{color:#172033}.page header,.drawer-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px}.page small,.drawer small{font-size:10px;letter-spacing:1.2px;color:#64748b;font-weight:800}.page h1{font-size:25px;margin:5px 0}.page p{font-size:13px;color:#7b8798}.page header>button,.drawer footer button[type=submit]{border:0;background:#172033;color:#fff;padding:10px 14px;border-radius:8px;cursor:pointer}.card{background:#fff;border:1px solid #e7ebf1;border-radius:13px;overflow:hidden}.filters{display:flex;gap:10px;align-items:center;flex-wrap:wrap;padding:15px}.filters input,.filters select,.drawer input,.drawer select,.drawer textarea{border:1px solid #dfe5ed;border-radius:7px;padding:10px;font:inherit}.filters input{width:260px}.filters span{margin-left:auto;font-size:11px;color:#94a3b8}.scroll{overflow:auto}table{width:100%;border-collapse:collapse;white-space:nowrap}th,td{text-align:left;padding:13px 14px;border-top:1px solid #edf0f4;font-size:12px}th{background:#f8fafc;color:#64748b;text-transform:uppercase;font-size:10px}td{color:#475569}td small{display:block;margin-top:4px;color:#94a3b8}.active,.inactive{padding:5px 8px;border-radius:20px;background:#ecfdf5;color:#047857}.inactive{background:#f1f5f9;color:#64748b}.actions{display:flex;gap:12px;align-items:center}.actions a{color:#2563eb;text-decoration:none}.light{background:#fff!important;color:#334155!important;border:1px solid #dfe5ed!important}.empty{text-align:center;padding:30px}footer{display:flex;justify-content:flex-end;align-items:center;gap:12px;padding:14px;font-size:11px;color:#64748b}footer button{border-radius:7px;padding:7px 10px}.overlay{position:fixed;inset:0;background:#0f172a66;display:flex;justify-content:flex-end;z-index:1000}.drawer{background:#fff;width:min(540px,100%);height:100%;overflow:auto;padding:25px;box-shadow:-10px 0 35px #0002}.drawer h2{margin:5px 0 0}.drawer label{display:flex;flex-direction:column;gap:6px;font-size:12px;font-weight:700;margin:13px 0}.drawer input,.drawer select,.drawer textarea{width:100%;box-sizing:border-box}.two{display:grid;grid-template-columns:1fr 1fr;gap:12px}.drawer .check{flex-direction:row;align-items:center}.drawer .check input{width:auto}.drawer footer{display:flex;justify-content:flex-end;gap:10px;margin-top:22px}.error{color:#be123c}
</style>

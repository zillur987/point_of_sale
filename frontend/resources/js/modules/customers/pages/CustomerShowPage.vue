<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import { customerApi } from '@/api/customers'
import type { Customer, LoyaltyTransaction } from '@/api/customers'

const props = defineProps<{ id: string | number }>()
const customer = ref<Customer | null>(null)
const transactions = ref<LoyaltyTransaction[]>([])
const loading = ref(false)
const saving = ref(false)
const error = ref('')
const loyaltyError = ref('')
const showDrawer = ref(false)
const type = ref<'earn' | 'redeem' | 'adjustment'>('earn')
const points = ref(1)
const direction = ref<'add' | 'subtract'>('add')
const note = ref('')

async function load() {
    loading.value = true
    error.value = ''
    try {
        const id = Number(props.id)
        const customerResponse = await customerApi.customer(id)
        customer.value = customerResponse.data.data

        // Loyalty history has its own permission; do not block the profile
        // when the current user can view customers but cannot view loyalty.
        try {
            const loyaltyResponse = await customerApi.loyalty(id)
            transactions.value = loyaltyResponse.data.data
            loyaltyError.value = ''
        } catch (loyaltyException: any) {
            transactions.value = []
            loyaltyError.value =
                loyaltyException?.response?.status === 403
                    ? 'You do not have permission to view loyalty activity.'
                    : 'Could not load loyalty activity.'
        }
    } catch (e: any) {
        error.value = e?.response?.data?.message || 'Could not load customer profile.'
    } finally {
        loading.value = false
    }
}

async function savePoints() {
    if (!customer.value) return
    saving.value = true
    error.value = ''
    try {
        await customerApi.addLoyalty(customer.value.id, {
            type: type.value,
            points: points.value,
            direction: type.value === 'adjustment' ? direction.value : undefined,
            note: note.value || undefined,
        })
        showDrawer.value = false
        points.value = 1
        note.value = ''
        await load()
    } catch (e: any) {
        error.value = e?.response?.data?.message || 'Could not update loyalty points.'
    } finally {
        saving.value = false
    }
}

function signed(value: number) {
    return value > 0 ? `+${value}` : String(value)
}

onMounted(load)
</script>

<template>
    <section class="page">
        <header>
            <div>
                <small>CUSTOMER RELATIONSHIPS</small>
                <h1>Customer profile</h1>
                <p>View customer details and loyalty activity.</p>
            </div>
            <RouterLink :to="{ name: 'customers.index' }">← Back to customers</RouterLink>
        </header>

        <p v-if="error" class="error">{{ error }}</p>
        <div v-if="loading && !customer" class="card empty">Loading customer...</div>

        <template v-if="customer">
            <div class="summary">
                <div class="card identity">
                    <div class="avatar">{{ customer.name.slice(0, 1).toUpperCase() }}</div>
                    <div>
                        <h2>{{ customer.name }}</h2>
                        <p>{{ customer.group?.name || 'General customer' }}</p>
                        <span :class="customer.is_active ? 'active' : 'inactive'">{{ customer.is_active ? 'Active' : 'Inactive' }}</span>
                    </div>
                </div>
                <div class="card points">
                    <small>LOYALTY BALANCE</small>
                    <strong>{{ customer.points_balance.toLocaleString() }}</strong>
                    <span>Available points</span>
                    <button @click="showDrawer = true">＋ Adjust points</button>
                </div>
            </div>

            <div class="card details">
                <h3>Contact information</h3>
                <div class="detail-grid">
                    <div><small>Phone</small><b>{{ customer.phone || '—' }}</b></div>
                    <div><small>Email</small><b>{{ customer.email || '—' }}</b></div>
                    <div><small>Location</small><b>{{ [customer.city, customer.country].filter(Boolean).join(', ') || '—' }}</b></div>
                    <div><small>Address</small><b>{{ customer.address || '—' }}</b></div>
                </div>
                <div v-if="customer.notes" class="notes"><small>Notes</small><p>{{ customer.notes }}</p></div>
            </div>

            <div class="card history">
                <div class="history-head"><div><h3>Loyalty activity</h3><p>Recorded point changes for this customer.</p></div><span>{{ transactions.length }} transactions loaded</span></div>
                <p v-if="loyaltyError" class="loyalty-error">{{ loyaltyError }}</p>
                <div v-else class="scroll">
                    <table>
                        <thead><tr><th>Date</th><th>Type</th><th>Points change</th><th>Balance after</th><th>Note</th><th>Processed by</th></tr></thead>
                        <tbody>
                            <tr v-for="item in transactions" :key="item.id">
                                <td>{{ new Date(item.created_at).toLocaleString() }}</td>
                                <td><span class="type">{{ item.type }}</span></td>
                                <td :class="item.points_change >= 0 ? 'positive' : 'negative'"><b>{{ signed(item.points_change) }}</b></td>
                                <td>{{ item.balance_after.toLocaleString() }}</td>
                                <td>{{ item.note || '—' }}</td>
                                <td>{{ item.user?.name || 'System' }}</td>
                            </tr>
                            <tr v-if="!transactions.length"><td colspan="6" class="empty">No loyalty activity yet.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </template>

        <div v-if="showDrawer" class="overlay" @click.self="showDrawer = false">
            <form class="drawer" @submit.prevent="savePoints">
                <div class="drawer-head"><div><small>LOYALTY MANAGEMENT</small><h2>Update points</h2></div><button type="button" class="light" @click="showDrawer = false">✕</button></div>
                <label>Transaction type
                    <select v-model="type"><option value="earn">Earn points</option><option value="redeem">Redeem points</option><option value="adjustment">Manual adjustment</option></select>
                </label>
                <label>Points<input v-model.number="points" type="number" min="1" max="100000000" required></label>
                <label v-if="type === 'adjustment'">Adjustment direction
                    <select v-model="direction"><option value="add">Add points</option><option value="subtract">Subtract points</option></select>
                </label>
                <label>Reason / note<textarea v-model="note" rows="3" maxlength="500"></textarea></label>
                <p class="hint">Redemptions and deductions cannot exceed the customer's available balance.</p>
                <footer><button type="button" class="light" @click="showDrawer = false">Cancel</button><button type="submit" :disabled="saving">{{ saving ? 'Saving...' : 'Save transaction' }}</button></footer>
            </form>
        </div>
    </section>
</template>

<style scoped>
.page{color:#172033}.page header,.drawer-head,.history-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:18px}.page small,.drawer small{font-size:10px;letter-spacing:1.2px;color:#64748b;font-weight:800}.page h1{font-size:25px;margin:5px 0}.page p{font-size:13px;color:#7b8798}.page header a{color:#2563eb;text-decoration:none;font-size:12px}.summary{display:grid;grid-template-columns:1fr 280px;gap:15px;margin-bottom:15px}.card{background:#fff;border:1px solid #e7ebf1;border-radius:13px;padding:20px}.identity{display:flex;align-items:center;gap:15px}.avatar{width:54px;height:54px;border-radius:14px;background:#eaf0ff;color:#3157a4;display:grid;place-items:center;font-size:22px;font-weight:800}.identity h2{margin:0 0 5px;font-size:19px}.identity p{margin:0 0 8px}.active,.inactive,.type{padding:5px 8px;border-radius:20px;background:#ecfdf5;color:#047857;font-size:10px}.inactive{background:#f1f5f9;color:#64748b}.points{display:flex;flex-direction:column;gap:7px}.points strong{font-size:30px}.points span{font-size:11px;color:#94a3b8}.points button,.drawer footer button[type=submit]{border:0;background:#172033;color:white;border-radius:7px;padding:9px 12px;cursor:pointer}.details h3,.history h3{margin:0 0 15px;font-size:15px}.detail-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px}.detail-grid div{display:flex;flex-direction:column;gap:5px}.detail-grid small,.notes small{color:#94a3b8;font-size:10px;text-transform:uppercase}.detail-grid b{font-size:12px;font-weight:600}.notes{margin-top:18px}.notes p{margin:5px 0}.history{margin-top:15px;padding:0;overflow:hidden}.history-head{padding:18px 20px;margin:0}.history-head h3{margin:0 0 5px}.history-head p{margin:0}.history-head>span{font-size:11px;color:#94a3b8}.scroll{overflow:auto}table{width:100%;border-collapse:collapse;white-space:nowrap}th,td{text-align:left;padding:12px 14px;border-top:1px solid #edf0f4;font-size:11px}th{background:#f8fafc;color:#64748b;text-transform:uppercase;font-size:10px}.positive{color:#047857}.negative{color:#be123c}.empty{text-align:center;padding:30px;color:#94a3b8}.overlay{position:fixed;inset:0;background:#0f172a66;display:flex;justify-content:flex-end;z-index:1000}.drawer{background:white;width:min(480px,100%);height:100%;overflow:auto;padding:25px;box-shadow:-10px 0 35px #0002}.drawer h2{margin:5px 0 0}.drawer label{display:flex;flex-direction:column;gap:6px;font-size:12px;font-weight:700;margin:15px 0}.drawer input,.drawer select,.drawer textarea{border:1px solid #dfe5ed;border-radius:7px;padding:10px;font:inherit}.drawer footer{display:flex;justify-content:flex-end;gap:10px;margin-top:22px}.light{background:white!important;color:#334155!important;border:1px solid #dfe5ed!important;padding:8px 11px;border-radius:7px}.hint{font-size:11px;color:#64748b}.error,.loyalty-error{color:#be123c;padding:0 20px 14px;font-size:12px}@media(max-width:760px){.summary{grid-template-columns:1fr}.detail-grid{grid-template-columns:1fr}}
</style>

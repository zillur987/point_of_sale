
<script setup lang="ts">
import { onMounted, ref, watch } from 'vue'
import { RouterLink } from 'vue-router'
import { posApi } from '@/api/sales'
import type { SaleRecord } from '@/api/sales'

const rows = ref<SaleRecord[]>([])
const loading = ref(false)
const search = ref('')
const page = ref(1)
const lastPage = ref(1)
const error = ref('')

let timer: ReturnType<typeof setTimeout> | undefined

const money = (value: number) =>
    new Intl.NumberFormat('en-BD', {
        style: 'currency',
        currency: 'BDT',
    }).format((value || 0) / 100)

async function load() {
    loading.value = true
    error.value = ''

    try {
        const response = await posApi.sales({
            search: search.value || undefined,
            page: page.value,
        })

        rows.value = response.data.data
        lastPage.value = response.data.meta?.last_page || 1
    } catch (e: any) {
        error.value =
            e?.response?.data?.message || 'Could not load sales.'
    } finally {
        loading.value = false
    }
}

watch(search, () => {
    clearTimeout(timer)

    timer = setTimeout(() => {
        page.value = 1
        load()
    }, 300)
})

onMounted(load)
</script>

<template>
    <section class="sales-page">
        <header>
            <div>
                <span>TRANSACTION MANAGEMENT</span>
                <h1>Sales history</h1>
                <p>Review completed POS transactions.</p>
            </div>

            <RouterLink :to="{ name: 'sales.pos' }">
                ＋ New sale
            </RouterLink>
        </header>

        <div v-if="error" class="error">
            {{ error }}
        </div>

        <div class="table-card">
            <div class="filters">
                <input
                    v-model="search"
                    placeholder="Search invoice number..."
                >

                <span>{{ rows.length }} records on this page</span>
            </div>

            <div class="table-scroll">
                <table>
                    <thead>
                        <tr>
                            <th>Invoice</th>
                            <th>Date</th>
                            <th>Warehouse</th>
                            <th>Cashier</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr
                            v-for="s in rows"
                            :key="s.id"
                        >
                            <td>
                                <b>{{ s.invoice_no }}</b>
                            </td>

                            <td>
                                {{ new Date(s.sold_at).toLocaleString() }}
                            </td>

                            <td>
                                {{ s.warehouse?.name || '—' }}
                            </td>

                            <td>
                                {{ s.user?.name || '—' }}
                            </td>

                            <td>
                                {{ s.items_count ?? '—' }}
                            </td>

                            <td>
                                <b>{{ money(s.grand_total) }}</b>
                            </td>

                            <td>
                                <span class="status">
                                    {{ s.status }}
                                </span>
                            </td>
                        </tr>

                        <tr v-if="!rows.length">
                            <td colspan="7" class="empty">
                                {{ loading ? 'Loading...' : 'No sales found.' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <footer>
                <button
                    :disabled="page <= 1 || loading"
                    @click="page--; load()"
                >
                    Previous
                </button>

                <span>
                    Page {{ page }} of {{ lastPage }}
                </span>

                <button
                    :disabled="page >= lastPage || loading"
                    @click="page++; load()"
                >
                    Next
                </button>
            </footer>
        </div>
    </section>
</template>

<style scoped>
.sales-page {
    color: #172033;
}

.sales-page header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 22px;
}

.sales-page header span {
    font-size: 10px;
    letter-spacing: 1.4px;
    color: #64748b;
    font-weight: 800;
}

.sales-page h1 {
    font-size: 25px;
    margin: 5px 0;
}

.sales-page p {
    margin: 0;
    color: #7b8798;
    font-size: 13px;
}

.sales-page header a {
    background: #172033;
    color: white;
    text-decoration: none;
    border-radius: 8px;
    padding: 11px 15px;
    font-size: 12px;
}

.table-card {
    background: #fff;
    border: 1px solid #e7ebf1;
    border-radius: 13px;
    overflow: hidden;
}

.filters {
    padding: 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.filters input {
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 10px;
    width: min(320px, 70%);
}

.filters span {
    font-size: 11px;
    color: #94a3b8;
}

.table-scroll {
    overflow: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
    white-space: nowrap;
}

th,
td {
    text-align: left;
    padding: 13px 15px;
    border-top: 1px solid #edf0f4;
    font-size: 12px;
}

th {
    background: #f8fafc;
    color: #64748b;
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: .5px;
}

.status {
    background: #ecfdf5;
    color: #047857;
    border-radius: 20px;
    padding: 5px 8px;
    font-size: 10px;
}

.empty {
    text-align: center;
    color: #94a3b8;
    padding: 35px;
}

footer {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 12px;
    padding: 14px;
    font-size: 11px;
    color: #64748b;
}

footer button {
    border: 1px solid #e2e8f0;
    background: white;
    border-radius: 7px;
    padding: 7px 11px;
}

footer button:disabled {
    opacity: .4;
}

.error {
    color: #be123c;
    margin-bottom: 12px;
}
</style>

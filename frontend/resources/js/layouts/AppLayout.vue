
<script setup>
import { ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import Sidebar from '@/layouts/Sidebar.vue'
import Topbar from '@/layouts/Topbar.vue'

const auth = useAuthStore()
const router = useRouter()

const collapsed = ref(localStorage.getItem('crm.sidebar') === '1')
const mobileOpen = ref(false)

watch(collapsed, value => {
    localStorage.setItem('crm.sidebar', value ? '1' : '0')
})

const toggleSidebar = () => {
    collapsed.value = !collapsed.value
}

const closeMobileSidebar = () => {
    mobileOpen.value = false
}

const openMobileSidebar = () => {
    mobileOpen.value = true
}

async function logout() {
    try {
        await auth.logout()
        await router.push({ name: 'login' })
    } catch (error) {
        console.error('Logout failed:', error)
    }
}
</script>

<template>
    <div class="crm-shell" :class="{ 'is-collapsed': collapsed }">
        <Sidebar
            :collapsed="collapsed"
            :mobile-open="mobileOpen"
            :user="auth.user"
            @toggle="toggleSidebar"
            @close="closeMobileSidebar"
        />

        <div
            v-if="mobileOpen"
            class="crm-overlay"
            @click="closeMobileSidebar"
        ></div>

        <div class="crm-main">
            <Topbar
                :user="auth.user"
                @menu="openMobileSidebar"
                @logout="logout"
            />

            <main class="crm-content">
                <RouterView />
            </main>
        </div>
    </div>
</template>

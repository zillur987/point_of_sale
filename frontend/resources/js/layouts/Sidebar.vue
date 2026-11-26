
<script setup>
import { computed } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { sidebarMenus } from '@/menu'
import { usePermission } from '@/composables/usePermission'

defineProps({
    collapsed: Boolean,
    mobileOpen: Boolean,
})

const emit = defineEmits(['toggle', 'close'])

const route = useRoute()
const { canAny } = usePermission()

const hasPermission = (permission) => {
    return !permission || canAny(permission)
}

const visibleMenus = computed(() =>
    sidebarMenus
        .map(menu => {
            const children = (menu.children || []).filter(child =>
                hasPermission(child.permission)
            )

            const hasDirectRoute = Boolean(menu.to)

            return {
                ...menu,
                children,
                visible:
                    hasPermission(menu.permission) ||
                    children.length > 0 ||
                    (hasDirectRoute && hasPermission(menu.permission)),
            }
        })
        .filter(menu => menu.visible)
)

const isActive = (child) => {
    if (!child.to) return false

    if (child.to.name) {
        return route.name === child.to.name
    }

    return route.path === child.to
}
</script>

<template>
    <aside
        class="crm-sidebar"
        :class="{
            'is-collapsed': collapsed,
            'is-open': mobileOpen,
        }"
    >
        <div class="crm-brand">
            <span class="crm-brand-mark">
                <i class="bi bi-shop"></i>
            </span>
            <span class="crm-brand-name">POS</span>
        </div>

        <nav class="crm-nav" aria-label="Main">
            <div
                v-for="menu in visibleMenus"
                :key="menu.label"
                class="crm-nav-group"
            >
                <div class="crm-nav-label" v-if="menu.children.length">
                    <i :class="menu.icon"></i>
                    <span>{{ menu.label }}</span>
                </div>

                <RouterLink
                    v-if="menu.to && hasPermission(menu.permission)"
                    :to="menu.to"
                    class="crm-nav-link"
                    :class="{ 'is-active': isActive(menu) }"
                    :title="collapsed ? menu.label : null"
                    @click="emit('close')"
                >
                    <i v-if="menu.icon" :class="menu.icon"></i>
                    <span>{{ menu.label }}</span>
                </RouterLink>

                <RouterLink
                    v-for="child in menu.children"
                    :key="child.label"
                    :to="child.to"
                    class="crm-nav-link"
                    :class="{ 'is-active': isActive(child) }"
                    :title="collapsed ? child.label : null"
                    @click="emit('close')"
                >
                    <i v-if="child.icon" :class="child.icon"></i>
                    <span>{{ child.label }}</span>
                </RouterLink>
            </div>
        </nav>
    </aside>
</template>

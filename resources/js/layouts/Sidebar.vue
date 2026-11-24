<script setup>
import { computed, nextTick, onMounted, ref, watch } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { usePermission } from '@/composables/usePermission'

defineProps({
  collapsed: Boolean,
  mobileOpen: Boolean,
})
const emit = defineEmits(['toggle', 'close'])

// Add new pages here. `badge` is optional (pass real counts from your API/store).
// `permission` is what a user needs to see the link (a string, or an array meaning "any of").
// Super Admin sees everything. A link without `permission` is shown to everyone.
const groups = [
  {
    label: 'Overview',
    items: [{ name: 'Dashboard', icon: 'bi-grid-1x2', to: '', permission: 'dashboard.view' }],
  },
  {
    label: 'Team',
    items: [
      { name: 'Team members', icon: 'bi-person-badge', to: '/team', permission: 'team.view' },
      { name: 'Roles & permissions', icon: 'bi-shield-lock', to: '/roles', permission: 'roles.view' },
      { name: 'User access', icon: 'bi-person-gear', to: '/access/users', permission: 'users.view' },
      { name: 'Integrations', icon: 'bi-plug', to: '/integrations', permission: 'integrations.view' },
    ],
  },
]

const route = useRoute()
const { canAny } = usePermission()

// Only the links this user may open; a group with no visible link is hidden too.


const visibleGroups = computed(() =>
  groups
    .filter((group) => group && typeof group === 'object')
    .map((group) => {
      const items = Array.isArray(group.items) ? group.items : [];

      return {
        ...group,
        items: items.filter(
          (item) =>
            item &&
            (
              !item.permission ||
              canAny(...[].concat(item.permission))
            )
        ),
      };
    })
    .filter((group) => group.items.length > 0)
);



const navEl = ref(null)

// With a long menu the active link can sit below the fold, so bring it into view.
const revealActive = () =>
  nextTick(() => navEl.value?.querySelector('.crm-nav-link.active')?.scrollIntoView({ block: 'nearest' }))

onMounted(revealActive)
watch(() => route.path, revealActive)

// "/leads" matches "/leads" and "/leads/create", but not "/leads-archive".
const isActive = (to) => (to === '/' ? route.path === '/' : route.path === to || route.path.startsWith(`${to}/`))
</script>

<template>
  <aside class="crm-sidebar" :class="{ 'is-collapsed': collapsed, 'is-open': mobileOpen }">
    <div class="crm-brand">
      <span class="crm-brand-mark"><i class="bi bi-people-fill"></i></span>
      <span class="crm-brand-name">CRM</span>
      <button
        type="button"
        class="crm-icon-btn crm-collapse-btn"
        :aria-label="collapsed ? 'Expand sidebar' : 'Collapse sidebar'"
        @click="emit('toggle')"
      >
        <i class="bi" :class="collapsed ? 'bi-chevron-bar-right' : 'bi-chevron-bar-left'"></i>
      </button>
    </div>

    <nav ref="navEl" class="crm-nav" aria-label="Main">
      <div v-for="group in visibleGroups" :key="group.label" class="crm-nav-group">
        <div class="crm-nav-label">{{ group.label }}</div>
        <RouterLink
          v-for="item in group.items"
          :key="item.to"
          :to="item.to"
          class="crm-nav-link"
          :class="{ active: isActive(item.to) }"
          :aria-current="isActive(item.to) ? 'page' : null"
          :title="collapsed ? item.name : null"
          @click="emit('close')"
        >
          <i class="bi" :class="item.icon"></i>
          <span class="crm-nav-text">{{ item.name }}</span>
          <span v-if="item.badge" class="crm-nav-badge">{{ item.badge }}</span>
        </RouterLink>
      </div>
    </nav>
  </aside>
</template>
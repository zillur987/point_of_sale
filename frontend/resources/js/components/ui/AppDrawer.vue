<script setup lang="ts">
import { onBeforeUnmount, onMounted } from 'vue'

const props = defineProps<{ modelValue: boolean; title: string; width?: string }>()
const emit = defineEmits<{ 'update:modelValue': [value: boolean] }>()

const close = () => emit('update:modelValue', false)
const onKey = (e: KeyboardEvent) => {
  if (e.key === 'Escape' && props.modelValue) close()
}

onMounted(() => window.addEventListener('keydown', onKey))
onBeforeUnmount(() => window.removeEventListener('keydown', onKey))
</script>

<template>
  <Teleport to="body">
    <Transition name="drawer">
      <div v-if="modelValue" class="app-drawer" role="dialog" aria-modal="true">
        <div class="app-drawer__backdrop" @click="close" />
        <aside class="app-drawer__panel" :style="{ width: width ?? '460px' }">
          <header class="app-drawer__header">
            <h5 class="mb-0">{{ title }}</h5>
            <button type="button" class="btn-close" aria-label="Close" @click="close" />
          </header>
          <div class="app-drawer__body"><slot /></div>
          <footer v-if="$slots.footer" class="app-drawer__footer"><slot name="footer" /></footer>
        </aside>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.app-drawer { position: fixed; inset: 0; z-index: 1050; }
.app-drawer__backdrop { position: absolute; inset: 0; background: rgba(0, 0, 0, 0.4); }
.app-drawer__panel {
  position: absolute; top: 0; right: 0; bottom: 0; max-width: 100%;
  display: flex; flex-direction: column; background: #fff;
  box-shadow: -8px 0 24px rgba(0, 0, 0, 0.15);
}
.app-drawer__header { display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.25rem; border-bottom: 1px solid #e9ecef; }
.app-drawer__body { flex: 1; overflow-y: auto; padding: 1.25rem; }
.app-drawer__footer { padding: 0.75rem 1.25rem; border-top: 1px solid #e9ecef; display: flex; gap: 0.5rem; justify-content: flex-end; }

.drawer-enter-active, .drawer-leave-active { transition: opacity 0.2s ease; }
.drawer-enter-active .app-drawer__panel, .drawer-leave-active .app-drawer__panel { transition: transform 0.25s ease; }
.drawer-enter-from, .drawer-leave-to { opacity: 0; }
.drawer-enter-from .app-drawer__panel, .drawer-leave-to .app-drawer__panel { transform: translateX(100%); }
</style>

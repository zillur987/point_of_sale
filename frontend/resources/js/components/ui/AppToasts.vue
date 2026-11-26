<script setup lang="ts">
import { useNotify } from '@/composables/useNotify'

const { toasts } = useNotify()
</script>

<template>
  <div class="position-fixed top-0 end-0 p-3" style="z-index: 2000">
    <div
      v-for="t in toasts"
      :key="t.id"
      class="alert shadow-sm py-2 px-3 mb-2"
      :class="t.type === 'success' ? 'alert-success' : 'alert-danger'"
      role="alert"
    >
      {{ t.message }}
    </div>
  </div>
</template>

<script setup lang="ts">
defineProps<{ label: string; error?: string; required?: boolean }>()
</script>

<template>
  <div class="mb-3">
    <label class="form-label fw-medium">
      {{ label }}<span v-if="required" class="text-danger"> *</span>
    </label>
    <slot />
    <div v-if="error" class="text-danger small mt-1">{{ error }}</div>
  </div>
</template>

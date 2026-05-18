<template>
  <div class="section-card">
    <div class="section-header">
      <h6 class="section-title mb-0">{{ title }}</h6>
      <slot name="action" />
    </div>
    <div class="section-body">
      <slot />
    </div>
  </div>
</template>

<script setup lang="ts">
defineProps<{ title: string }>()
</script>

<style scoped lang="scss">
.section-card {
  background: var(--bs-card-bg, #fff);
  border-radius: 0.75rem;
  border: 1px solid var(--bs-border-color);
  overflow: hidden;
  margin-bottom: 1rem;
}

.section-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.875rem 1rem;
  border-bottom: 1px solid var(--bs-border-color);
}

.section-title { font-size: .9rem; font-weight: 600; }

.section-body { padding: 0.75rem 1rem; }
</style>

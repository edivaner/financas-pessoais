<template>
  <div class="stat-card">
    <div class="stat-icon" :style="{ background: iconBg }">
      <i :class="icon"></i>
    </div>
    <div class="stat-body">
      <span class="stat-label">{{ label }}</span>
      <span class="stat-value" :class="valueClass">{{ formatted }}</span>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useUiStore } from '@/stores/ui'

const uiStore = useUiStore()

const props = defineProps<{
  label: string
  value: number
  icon: string
  iconBg?: string
  color?: 'success' | 'danger' | 'info' | 'warning' | 'primary'
}>()

const valueClass = computed(() => `text-${props.color ?? 'body'}`)
const formatted  = computed(() => uiStore.formatCurrency(props.value))
</script>

<style scoped lang="scss">
.stat-card {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.875rem 1rem;
  background: var(--bs-card-bg, #fff);
  border-radius: 0.75rem;
  border: 1px solid var(--bs-border-color);
  box-shadow: 0 1px 3px rgba(0,0,0,.06);
}

.stat-icon {
  width: 40px;
  height: 40px;
  border-radius: 0.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1.1rem;
  flex-shrink: 0;
}

.stat-body {
  display: flex;
  flex-direction: column;
  min-width: 0;
}

.stat-label {
  font-size: .7rem;
  color: var(--bs-secondary);
  white-space: nowrap;
}

.stat-value {
  font-size: .95rem;
  font-weight: 700;
  white-space: nowrap;
}
</style>

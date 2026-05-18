<template>
  <div class="limit-bar-wrap">
    <div class="limit-bar-header">
      <div class="limit-icon">
        <i :class="icon || 'bi bi-tag-fill'" :style="{ color: iconColor || '#22c55e' }"></i>
      </div>
      <div class="limit-info">
        <span class="limit-name">{{ name }}</span>
        <span class="limit-values">{{ formattedGasto }} / {{ formattedLimite }}</span>
      </div>
      <span class="limit-pct" :class="pctClass">{{ pct }}%</span>
    </div>
    <div class="progress mt-1" style="height:6px">
      <div
        class="progress-bar"
        :class="barClass"
        :style="{ width: Math.min(pct, 100) + '%' }"
      ></div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
  name: string
  valorGasto: number
  valorLimite: number
  icon?: string
  iconColor?: string
}>()

const pct = computed(() =>
  props.valorLimite > 0 ? Math.round((props.valorGasto / props.valorLimite) * 100) : 0
)

const barClass = computed(() => {
  if (pct.value >= 100) return 'bg-danger'
  if (pct.value >= 70)  return 'bg-warning'
  return 'bg-success'
})

const pctClass = computed(() => {
  if (pct.value >= 100) return 'text-danger'
  if (pct.value >= 70)  return 'text-warning'
  return 'text-success'
})

const fmt = (v: number) =>
  new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(v ?? 0)

const formattedGasto  = computed(() => fmt(props.valorGasto))
const formattedLimite = computed(() => fmt(props.valorLimite))
</script>

<style scoped lang="scss">
.limit-bar-wrap { padding: 0.5rem 0; }

.limit-bar-header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.limit-icon {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: var(--bs-secondary-bg, #f1f5f9);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: .9rem;
  flex-shrink: 0;
}

.limit-info {
  flex: 1;
  display: flex;
  flex-direction: column;
  min-width: 0;
}

.limit-name   { font-size: .85rem; font-weight: 500; }
.limit-values { font-size: .7rem; color: var(--bs-secondary); }
.limit-pct    { font-size: .8rem; font-weight: 700; }
</style>

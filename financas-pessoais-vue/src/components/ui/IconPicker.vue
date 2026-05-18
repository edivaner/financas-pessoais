<template>
  <div>
    <label class="form-label">{{ label }}</label>
    <div class="d-flex gap-2 align-items-center mb-2">
      <div class="icon-preview" :style="{ background: previewColor + '22', color: previewColor }">
        <i :class="modelValue || 'bi bi-tag'"></i>
      </div>
      <button type="button" class="btn btn-outline-secondary btn-sm" @click="open = !open">
        {{ open ? 'Fechar' : 'Escolher ícone' }}
      </button>
      <span class="text-secondary small">{{ modelValue || 'nenhum' }}</span>
    </div>

    <div v-if="open" class="icon-picker-panel border rounded p-2">
      <input
        v-model="search"
        class="form-control form-control-sm mb-2"
        placeholder="Buscar ícone..."
        @input="search = ($event.target as HTMLInputElement).value"
      />
      <div class="icon-grid">
        <button
          v-for="icon in filtered"
          :key="icon"
          type="button"
          class="icon-btn"
          :class="{ active: modelValue === 'bi ' + icon }"
          :title="icon"
          @click="select(icon)"
        >
          <i :class="'bi ' + icon"></i>
        </button>
      </div>
      <p v-if="!filtered.length" class="text-center text-secondary small mt-2 mb-0">Nenhum ícone encontrado.</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'

const props = defineProps<{ modelValue: string; label?: string; previewColor?: string }>()
const emit  = defineEmits<{ (e: 'update:modelValue', v: string): void }>()

const open   = ref(false)
const search = ref('')

const icons = [
  // usados nas categorias do sistema
  'bi-activity', 'bi-egg-fried', 'bi-calendar-check', 'bi-cup-hot', 'bi-heart',
  'bi-bag', 'bi-book', 'bi-exclamation-triangle', 'bi-credit-card', 'bi-controller',
  'bi-droplet', 'bi-palette', 'bi-receipt', 'bi-tools', 'bi-cart', 'bi-house',
  'bi-three-dots', 'bi-credit-card-2-front', 'bi-piggy-bank', 'bi-hospital',
  'bi-shield-check', 'bi-bank', 'bi-play-circle', 'bi-arrow-left-right',
  'bi-car-front', 'bi-airplane', 'bi-plus-circle', 'bi-cash-coin', 'bi-graph-up',
  // finanças
  'bi-wallet2', 'bi-cash', 'bi-cash-stack', 'bi-coin', 'bi-currency-dollar',
  'bi-currency-exchange', 'bi-bar-chart', 'bi-bar-chart-line', 'bi-pie-chart',
  'bi-graph-up-arrow', 'bi-graph-down-arrow', 'bi-arrow-up-circle', 'bi-arrow-down-circle',
  // alimentação / saúde
  'bi-cup-straw', 'bi-basket', 'bi-basket2', 'bi-capsule', 'bi-heart-pulse',
  'bi-bandaid', 'bi-lungs', 'bi-bicycle',
  // casa / moradia
  'bi-building', 'bi-lamp', 'bi-plug', 'bi-lightbulb', 'bi-door-open',
  'bi-wrench', 'bi-hammer', 'bi-trash',
  // transporte
  'bi-bus-front', 'bi-bicycle', 'bi-fuel-pump', 'bi-train-front', 'bi-scooter',
  // lazer / entretenimento
  'bi-music-note', 'bi-camera', 'bi-film', 'bi-tv', 'bi-phone',
  'bi-joystick', 'bi-dice-5', 'bi-balloon', 'bi-gift',
  // trabalho / educação
  'bi-briefcase', 'bi-laptop', 'bi-pencil', 'bi-journal', 'bi-mortarboard',
  'bi-people', 'bi-person', 'bi-stars',
  // viagem
  'bi-luggage', 'bi-map', 'bi-compass', 'bi-geo-alt',
  // outros
  'bi-tag', 'bi-star', 'bi-lock', 'bi-bell', 'bi-bookmark',
  'bi-cloud', 'bi-gear', 'bi-shield', 'bi-trophy',
]

const filtered = computed(() => {
  const q = search.value.toLowerCase()
  return q ? icons.filter(i => i.includes(q)) : icons
})

const select = (icon: string) => {
  emit('update:modelValue', 'bi ' + icon)
  open.value = false
}
</script>

<style scoped lang="scss">
.icon-preview {
  width: 40px; height: 40px; border-radius: 0.5rem;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.25rem; flex-shrink: 0;
  background: #f1f5f9; color: #64748b;
}
.icon-picker-panel {
  max-height: 260px; overflow-y: auto;
  background: var(--bs-body-bg);
}
.icon-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(44px, 1fr));
  gap: 4px;
}
.icon-btn {
  width: 44px; height: 44px; border: 1px solid transparent;
  border-radius: 0.5rem; background: none; font-size: 1.25rem;
  display: flex; align-items: center; justify-content: center;
  cursor: pointer; transition: background 0.15s;
  &:hover { background: var(--bs-secondary-bg); }
  &.active { border-color: var(--bs-success); background: var(--bs-success-bg-subtle); color: var(--bs-success); }
}
</style>

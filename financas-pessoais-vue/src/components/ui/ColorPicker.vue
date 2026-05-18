<template>
  <div>
    <label class="form-label">{{ label }}</label>
    <div class="color-grid">
      <button
        v-for="color in colors"
        :key="color.value"
        type="button"
        class="color-btn"
        :class="{ active: modelValue === color.value }"
        :style="{ background: color.value }"
        :title="color.label"
        @click="emit('update:modelValue', color.value)"
      ></button>
    </div>
  </div>
</template>

<script setup lang="ts">
defineProps<{ modelValue: string; label?: string }>()
const emit = defineEmits<{ (e: 'update:modelValue', v: string): void }>()

const colors = [
  // vermelhos
  { value: '#e74c3c', label: 'Vermelho' },
  { value: '#c0392b', label: 'Vermelho escuro' },
  { value: '#e91e63', label: 'Rosa' },
  { value: '#f06292', label: 'Rosa claro' },
  // laranjas
  { value: '#e67e22', label: 'Laranja' },
  { value: '#f39c12', label: 'Âmbar' },
  { value: '#ff9800', label: 'Laranja médio' },
  // amarelos
  { value: '#f1c40f', label: 'Amarelo' },
  { value: '#ffd600', label: 'Amarelo vivo' },
  // verdes
  { value: '#2ecc71', label: 'Verde' },
  { value: '#27ae60', label: 'Verde escuro' },
  { value: '#1abc9c', label: 'Verde água' },
  { value: '#16a085', label: 'Verde petróleo' },
  { value: '#4caf50', label: 'Verde médio' },
  // azuis
  { value: '#3498db', label: 'Azul' },
  { value: '#2980b9', label: 'Azul escuro' },
  { value: '#1565c0', label: 'Azul marinho' },
  { value: '#00bcd4', label: 'Ciano' },
  { value: '#03a9f4', label: 'Azul claro' },
  // roxos
  { value: '#9b59b6', label: 'Roxo' },
  { value: '#8e44ad', label: 'Roxo escuro' },
  { value: '#673ab7', label: 'Violeta' },
  { value: '#e040fb', label: 'Magenta' },
  // neutros
  { value: '#34495e', label: 'Azul acinzentado' },
  { value: '#2c3e50', label: 'Cinza escuro' },
  { value: '#7f8c8d', label: 'Cinza médio' },
  { value: '#95a5a6', label: 'Cinza claro' },
  { value: '#607d8b', label: 'Azul cinza' },
]
</script>

<style scoped lang="scss">
.color-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(36px, 1fr));
  gap: 6px;
}
.color-btn {
  width: 36px; height: 36px; border-radius: 50%;
  border: 2px solid transparent;
  cursor: pointer; transition: transform 0.15s, border-color 0.15s;
  outline: none;
  &:hover { transform: scale(1.15); }
  &.active {
    border-color: #fff;
    box-shadow: 0 0 0 2px currentColor, 0 0 0 4px rgba(0,0,0,0.15);
    transform: scale(1.1);
  }
}
</style>

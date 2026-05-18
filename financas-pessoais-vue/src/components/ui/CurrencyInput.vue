<template>
  <div class="input-group">
    <span class="input-group-text">{{ uiStore.currencySymbol }}</span>
    <input
      class="form-control"
      type="text"
      inputmode="numeric"
      :value="display"
      :placeholder="placeholder"
      :disabled="disabled"
      @input="onInput"
      @focus="onFocus"
      @blur="onBlur"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import { useUiStore } from '@/stores/ui'

const props = defineProps<{ modelValue: number; placeholder?: string; disabled?: boolean }>()
const emit  = defineEmits<{ (e: 'update:modelValue', v: number): void }>()

const uiStore = useUiStore()
const display = ref('')

const format = (cents: number) => {
  const value = cents / 100
  return new Intl.NumberFormat(uiStore.currency === 'USD' ? 'en-US' : 'pt-BR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(value)
}

const toDisplay = (num: number) => {
  const cents = Math.round(num * 100)
  return cents > 0 ? format(cents) : ''
}

watch(() => props.modelValue, (v) => {
  display.value = toDisplay(v)
}, { immediate: true })

const onInput = (e: Event) => {
  const raw   = (e.target as HTMLInputElement).value.replace(/\D/g, '')
  const cents = parseInt(raw || '0', 10)
  display.value = format(cents)
  emit('update:modelValue', cents / 100)
}

const onFocus = () => {
  if (!props.modelValue) display.value = ''
}

const onBlur = () => {
  display.value = toDisplay(props.modelValue)
}
</script>

import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from 'axios'

export const useUiStore = defineStore('ui', () => {
  const darkMode = ref(false)
  const currency = ref('BRL')

  const loadFromUser = (user: { dark_mode: boolean; currency: string }) => {
    darkMode.value = user.dark_mode ?? false
    currency.value = user.currency || 'BRL'
    applyDarkMode(darkMode.value)
  }

  const applyDarkMode = (value: boolean) => {
    document.documentElement.setAttribute('data-bs-theme', value ? 'dark' : 'light')
  }

  const toggleDarkMode = async () => {
    darkMode.value = !darkMode.value
    applyDarkMode(darkMode.value)
    await savePreferences()
  }

  const setCurrency = async (c: string) => {
    currency.value = c
    await savePreferences()
  }

  const savePreferences = async () => {
    try {
      await axios.put('/api/auth/preferences', {
        dark_mode: darkMode.value,
        currency: currency.value,
      })
    } catch { /* ignore */ }
  }

  const currencySymbol = computed(() => currency.value === 'USD' ? '$' : 'R$')

  const formatCurrency = (value: number) => {
    const code = currency.value || 'BRL'
    return new Intl.NumberFormat(code === 'USD' ? 'en-US' : 'pt-BR', {
      style: 'currency',
      currency: code,
    }).format(value ?? 0)
  }

  return { darkMode, currency, currencySymbol, loadFromUser, toggleDarkMode, setCurrency, formatCurrency }
})

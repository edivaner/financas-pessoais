import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from 'axios'

export const useUiStore = defineStore('ui', () => {
  const darkMode = ref(false)
  const currency = ref('BRL')

  const loadFromUser = (user: { dark_mode: boolean; currency: string }) => {
    darkMode.value = user.dark_mode
    currency.value = user.currency
    applyDarkMode(user.dark_mode)
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

  const formatCurrency = (value: number) =>
    new Intl.NumberFormat(currency.value === 'USD' ? 'en-US' : 'pt-BR', {
      style: 'currency',
      currency: currency.value,
    }).format(value ?? 0)

  return { darkMode, currency, currencySymbol, loadFromUser, toggleDarkMode, setCurrency, formatCurrency }
})

// resources/js/stores/dashboard.ts
import { defineStore } from 'pinia'
import { ref } from 'vue'
import axios from 'axios'

export const useDashboardStore = defineStore('dashboard', () => {
  const data    = ref<any>(null)
  const loading = ref(false)

  const fetchDashboard = async () => {
    try {
      loading.value = true
      const res = await axios.get('/api/dashboard')
      data.value = res.data.data
    } finally {
      loading.value = false
    }
  }

  const totais  = () => data.value?.totais   ?? {}
  const contas  = () => data.value?.contas   ?? []
  const cartoes = () => data.value?.cartoes  ?? []
  const limites = () => data.value?.limites  ?? []

  return { data, loading, fetchDashboard, totais, contas, cartoes, limites }
})

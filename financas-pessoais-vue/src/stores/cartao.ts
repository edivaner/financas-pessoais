import { defineStore } from 'pinia'
import { ref } from 'vue'
import axios from 'axios'

export interface Cartao {
  id: string
  conta_id: string
  nome: string
  tipo: 'CREDITO' | 'DEBITO' | 'MULTIPLO'
  fatura_total: number
  limite_total: number
  dia_vencimento: number | null
  dias_antes_fechamento: number
  imagem?: { id: string; caminho: string } | null
  logo_path?: string | null
  conta?: { id: string; nome: string }
}

export const useCartaoStore = defineStore('cartao', () => {
  const cartoes = ref<Cartao[]>([])
  const loading = ref(false)

  const fetchCartoes = async () => {
    try {
      loading.value = true
      const { data } = await axios.get('/api/cartoes')
      cartoes.value = data.data
    } finally {
      loading.value = false
    }
  }

  const createCartao = async (payload: object) => {
    const { data } = await axios.post('/api/cartoes', payload)
    cartoes.value.push(data.data)
    return data.data
  }

  const updateCartao = async (id: string, payload: object) => {
    const { data } = await axios.put(`/api/cartoes/${id}`, payload)
    const idx = cartoes.value.findIndex(c => c.id === id)
    if (idx !== -1) cartoes.value[idx] = data.data
    return data.data
  }

  const deleteCartao = async (id: string) => {
    await axios.delete(`/api/cartoes/${id}`)
    cartoes.value = cartoes.value.filter(c => c.id !== id)
  }

  return { cartoes, loading, fetchCartoes, createCartao, updateCartao, deleteCartao }
})

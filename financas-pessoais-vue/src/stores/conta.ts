import { defineStore } from 'pinia'
import { ref } from 'vue'
import axios from 'axios'

export interface Conta {
  id: string
  nome: string
  saldo: number
  saldo_investido: number
  carteira: boolean
  somar_tela_inicial: boolean
  imagem?: { id: string; caminho: string } | null
  logo_path?: string | null
}

export const useContaStore = defineStore('conta', () => {
  const contas  = ref<Conta[]>([])
  const loading = ref(false)
  const error   = ref<string | null>(null)

  const fetchContas = async () => {
    try {
      loading.value = true
      const { data } = await axios.get('/api/contas')
      contas.value = data.data
    } catch (e: any) {
      error.value = e.response?.data?.message ?? 'Erro ao buscar contas'
    } finally {
      loading.value = false
    }
  }

  const createConta = async (payload: Omit<Conta, 'id'>) => {
    const { data } = await axios.post('/api/contas', payload)
    contas.value.push(data.data)
    return data.data
  }

  const updateConta = async (id: string, payload: Partial<Conta>) => {
    const { data } = await axios.put(`/api/contas/${id}`, payload)
    const idx = contas.value.findIndex(c => c.id === id)
    if (idx !== -1) contas.value[idx] = data.data
    return data.data
  }

  const deleteConta = async (id: string) => {
    await axios.delete(`/api/contas/${id}`)
    contas.value = contas.value.filter(c => c.id !== id)
  }

  return { contas, loading, error, fetchContas, createConta, updateConta, deleteConta }
})

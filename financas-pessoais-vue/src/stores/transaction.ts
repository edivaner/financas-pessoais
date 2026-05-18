// resources/js/stores/transaction.ts
import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from 'axios'
import { useContaStore } from './conta'
import { useDashboardStore } from './dashboard'

export interface Lancamento {
  id: string
  titulo: string
  descricao?: string
  valor: number
  tipo_lancamento: 'RECEITAS' | 'DESPESAS' | 'TRANSFERENCIA' | 'INVESTIMENTOS'
  conta_origem_id?: string
  conta_destino_id?: string
  cartao_id?: string
  tipo_cartao?: string
  categoria_id?: string
  subcategoria_id?: string
  data: string
  esta_pago: boolean
  parcela_total?: number
  parcela_atual?: number
  para_saldo_investido?: boolean
  categoria?: { id: string; nome: string; cor: string; icone: string }
  subcategoria?: { id: string; nome: string }
  conta_origem?: { id: string; nome: string }
  conta_destino?: { id: string; nome: string }
  cartao?: { id: string; nome: string }
}

export const useTransactionStore = defineStore('transaction', () => {
  const lancamentos = ref<Lancamento[]>([])
  const loading     = ref(false)
  const mesSelecionado = ref(new Date().getMonth() + 1)
  const anoSelecionado = ref(new Date().getFullYear())
  const filtroTipo     = ref<string>('')
  const filtroContaId  = ref<string>('')
  const filtroCartaoId = ref<string>('')

  const fetchLancamentos = async () => {
    try {
      loading.value = true
      const params: Record<string, any> = {
        mes: mesSelecionado.value,
        ano: anoSelecionado.value,
      }
      if (filtroTipo.value)     params.tipo      = filtroTipo.value
      if (filtroContaId.value)  params.conta_id  = filtroContaId.value
      if (filtroCartaoId.value) params.cartao_id = filtroCartaoId.value

      const { data } = await axios.get('/api/lancamentos', { params })
      lancamentos.value = data.data
    } finally {
      loading.value = false
    }
  }

  const refreshRelated = () => {
    useContaStore().fetchContas()
    useDashboardStore().fetchDashboard()
  }

  const createLancamento = async (payload: object) => {
    const { data } = await axios.post('/api/lancamentos', payload)
    await fetchLancamentos()
    refreshRelated()
    return data.data
  }

  const updateLancamento = async (id: string, payload: object) => {
    const { data } = await axios.put(`/api/lancamentos/${id}`, payload)
    await fetchLancamentos()
    refreshRelated()
    return data.data
  }

  const deleteLancamento = async (id: string) => {
    await axios.delete(`/api/lancamentos/${id}`)
    await fetchLancamentos()
    refreshRelated()
  }

  const totalGastos = computed(() =>
    lancamentos.value
      .filter(l => l.tipo_lancamento === 'DESPESAS')
      .reduce((s, l) => s + Number(l.valor), 0)
  )

  const totalReceitas = computed(() =>
    lancamentos.value
      .filter(l => l.tipo_lancamento === 'RECEITAS')
      .reduce((s, l) => s + Number(l.valor), 0)
  )

  const balanco = computed(() => totalReceitas.value - totalGastos.value)

  const totalInvestimentos = computed(() =>
    lancamentos.value
      .filter(l => l.tipo_lancamento === 'INVESTIMENTOS')
      .reduce((s, l) => s + Number(l.valor), 0)
  )

  return {
    lancamentos, loading,
    mesSelecionado, anoSelecionado,
    filtroTipo, filtroContaId, filtroCartaoId,
    fetchLancamentos, createLancamento, updateLancamento, deleteLancamento,
    totalGastos, totalReceitas, balanco, totalInvestimentos,
  }
})

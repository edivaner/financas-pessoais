// resources/js/stores/balance.ts
import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from 'axios'

export const useBalanceStore = defineStore('balance', () => {
  const lancamentos = ref<any[]>([])
  const loading     = ref(false)
  const mes = ref(new Date().getMonth() + 1)
  const ano = ref(new Date().getFullYear())

  const fetchLancamentos = async () => {
    try {
      loading.value = true
      const { data } = await axios.get('/api/lancamentos', {
        params: { mes: mes.value, ano: ano.value }
      })
      lancamentos.value = data.data
    } finally {
      loading.value = false
    }
  }

  const receitas = computed(() =>
    lancamentos.value.filter(l => l.tipo_lancamento === 'RECEITAS').reduce((s, l) => s + Number(l.valor), 0)
  )

  const despesas = computed(() =>
    lancamentos.value.filter(l => l.tipo_lancamento === 'DESPESAS').reduce((s, l) => s + Number(l.valor), 0)
  )

  const investimentos = computed(() =>
    lancamentos.value
      .filter(l => l.tipo_lancamento === 'INVESTIMENTOS' && l.tipo_cartao === 'INVESTIR')
      .reduce((s, l) => s + Number(l.valor), 0)
  )

  const balanco = computed(() => receitas.value - despesas.value)

  const gastosCartao = computed(() =>
    lancamentos.value.filter(l => l.tipo_lancamento === 'DESPESAS' && l.cartao_id)
      .reduce((s, l) => s + Number(l.valor), 0)
  )

  const porCategoria = computed(() => {
    const map: Record<string, { nome: string; cor: string; total: number }> = {}
    for (const l of lancamentos.value.filter(l => l.tipo_lancamento === 'DESPESAS' && l.categoria)) {
      const id = l.categoria_id
      if (!map[id]) map[id] = { nome: l.categoria.nome, cor: l.categoria.cor, total: 0 }
      map[id].total += Number(l.valor)
    }
    return Object.values(map).sort((a, b) => b.total - a.total)
  })

  return {
    lancamentos, loading, mes, ano,
    fetchLancamentos,
    receitas, despesas, investimentos, balanco, gastosCartao, porCategoria,
  }
})

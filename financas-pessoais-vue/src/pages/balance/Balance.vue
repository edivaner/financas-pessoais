<!-- resources/js/pages/balance/Balance.vue -->
<template>
  <AppLayout>
    <div class="container-fluid px-3 py-3">
      <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
          <small class="text-secondary">Relatório</small>
          <h5 class="mb-0 fw-bold">Balanço Geral</h5>
        </div>
        <div class="d-flex gap-2">
          <select class="form-select" v-model="store.mes" style="width:auto" @change="store.fetchLancamentos()">
            <option v-for="m in mesesOptions" :key="m.value" :value="m.value">{{ m.text }}</option>
          </select>
          <select class="form-select" v-model="store.ano" style="width:auto" @change="store.fetchLancamentos()">
            <option v-for="a in anosOptions" :key="a.value" :value="a.value">{{ a.text }}</option>
          </select>
        </div>
      </div>

      <div v-if="store.loading" class="text-center py-5">
        <div class="spinner-border text-success"></div>
      </div>

      <template v-else>
        <!-- Totals -->
        <div class="card-modern mb-4">
          <div class="row g-0">
            <div class="col-6 border-end p-3 text-center">
              <div class="text-secondary small">Entradas</div>
              <div class="fw-bold text-success fs-5">{{ fmt(store.receitas) }}</div>
            </div>
            <div class="col-6 p-3 text-center">
              <div class="text-secondary small">Saídas</div>
              <div class="fw-bold text-danger fs-5">{{ fmt(store.despesas) }}</div>
            </div>
          </div>
          <div class="balance-bar mx-3 mb-3">
            <div class="bar-income"  :style="{ flex: store.receitas || 1 }"></div>
            <div class="bar-expense" :style="{ flex: store.despesas || 1 }"></div>
          </div>
          <div class="d-flex justify-content-between px-3 pb-2">
            <span class="text-secondary"><i class="bi bi-scale me-1"></i>Balanço</span>
            <span class="fw-bold" :class="store.balanco >= 0 ? 'text-success' : 'text-danger'">
              {{ fmt(store.balanco) }}
            </span>
          </div>
        </div>

        <!-- Summary -->
        <div class="card-modern mb-4">
          <div class="p-3 border-bottom fw-semibold">Entradas</div>
          <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
            <div class="d-flex align-items-center gap-2">
              <div class="sum-icon bg-success-subtle text-success"><i class="bi bi-cash-coin"></i></div>
              <span>Receitas</span>
            </div>
            <span class="text-success fw-semibold">{{ fmt(store.receitas) }}</span>
          </div>
          <div class="p-3 border-bottom fw-semibold">Saídas</div>
          <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
            <div class="d-flex align-items-center gap-2">
              <div class="sum-icon bg-danger-subtle text-danger"><i class="bi bi-cash-stack"></i></div>
              <span>Gastos</span>
            </div>
            <span class="text-danger fw-semibold">{{ fmt(store.despesas) }}</span>
          </div>
          <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
            <div class="d-flex align-items-center gap-2">
              <div class="sum-icon bg-warning-subtle text-warning"><i class="bi bi-credit-card"></i></div>
              <span>Gastos cartão</span>
            </div>
            <span class="text-warning fw-semibold">{{ fmt(store.gastosCartao) }}</span>
          </div>
          <div class="d-flex justify-content-between align-items-center p-3">
            <div class="d-flex align-items-center gap-2">
              <div class="sum-icon bg-purple-subtle text-purple"><i class="bi bi-graph-up-arrow"></i></div>
              <span>Investimentos</span>
            </div>
            <span class="text-purple fw-semibold">{{ fmt(store.investimentos) }}</span>
          </div>
        </div>

        <!-- Donut chart por categoria -->
        <div class="card-modern mb-4" v-if="store.porCategoria.length">
          <div class="p-3 border-bottom fw-semibold">Gastos por Categoria</div>
          <div class="p-3">
            <VueApexCharts
              type="donut"
              height="280"
              :options="donutOptions"
              :series="categorySeries"
            />
          </div>
          <div v-for="cat in store.porCategoria" :key="cat.nome" class="d-flex justify-content-between px-3 pb-2">
            <div class="d-flex align-items-center gap-2">
              <div class="dot" :style="{ background: cat.cor }"></div>
              <span class="small">{{ cat.nome }}</span>
            </div>
            <span class="small fw-semibold">{{ fmt(cat.total) }}</span>
          </div>
        </div>

        <!-- Bar chart entradas x saídas -->
        <div class="card-modern mb-4">
          <div class="p-3 border-bottom fw-semibold">Entradas x Saídas</div>
          <div class="p-3">
            <VueApexCharts
              type="bar"
              height="200"
              :options="barOptions"
              :series="barSeries"
            />
          </div>
        </div>
      </template>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { computed, onMounted } from 'vue'
import VueApexCharts from 'vue3-apexcharts'
import AppLayout from '@/components/layouts/AppLayout.vue'
import { useBalanceStore } from '@/stores/balance'
import { useUiStore } from '@/stores/ui'

const store   = useBalanceStore()
const uiStore = useUiStore()

const fmt = (v: number) => uiStore.formatCurrency(v)

const mesesOptions = [
  { value: 1, text: 'Janeiro' }, { value: 2, text: 'Fevereiro' },
  { value: 3, text: 'Março' },   { value: 4, text: 'Abril' },
  { value: 5, text: 'Maio' },    { value: 6, text: 'Junho' },
  { value: 7, text: 'Julho' },   { value: 8, text: 'Agosto' },
  { value: 9, text: 'Setembro' },{ value: 10, text: 'Outubro' },
  { value: 11, text: 'Novembro' },{ value: 12, text: 'Dezembro' },
]

const anosOptions = [2023, 2024, 2025, 2026].map(a => ({ value: a, text: String(a) }))

const categorySeries = computed(() => store.porCategoria.map(c => c.total))

const donutOptions = computed(() => ({
  labels: store.porCategoria.map((c: { nome: string; cor: string; total: number }) => c.nome),
  colors: store.porCategoria.map((c: { nome: string; cor: string; total: number }) => c.cor),
  legend: { position: 'bottom' as const },
  dataLabels: { enabled: false },
  chart: { background: 'transparent' },
  theme: { mode: (document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'dark' : 'light') as 'dark' | 'light' },
}))

const barSeries = computed(() => ([
  { name: 'Entradas', data: [store.receitas] },
  { name: 'Saídas',   data: [store.despesas] },
]))

const barOptions = {
  chart: { toolbar: { show: false }, background: 'transparent' },
  plotOptions: { bar: { horizontal: false, columnWidth: '40%', borderRadius: 6 } },
  colors: ['#22c55e', '#ef4444'],
  xaxis: { categories: ['Mês atual'] },
  dataLabels: { enabled: false },
  legend: { position: 'top' as const },
}

onMounted(() => store.fetchLancamentos())
</script>

<style scoped lang="scss">
.balance-bar {
  height: 8px; border-radius: 4px; display: flex; overflow: hidden; gap: 2px;
}
.bar-income  { background: #22c55e; border-radius: 4px 0 0 4px; min-width: 4px; }
.bar-expense { background: #ef4444; border-radius: 0 4px 4px 0; min-width: 4px; }

.sum-icon {
  width: 32px; height: 32px; border-radius: 0.5rem;
  display: flex; align-items: center; justify-content: center; font-size: .85rem;
}

.dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }

.text-purple      { color: #a855f7; }
.bg-purple-subtle { background: rgba(168,85,247,.12); }
</style>

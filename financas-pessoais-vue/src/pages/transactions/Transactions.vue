<!-- resources/js/pages/transactions/Transactions.vue -->
<template>
  <AppLayout>
    <div class="tx-page">
      <!-- Header -->
      <div class="tx-header px-3 pt-3 pb-0">
        <div class="d-flex align-items-center mb-2">
          <div>
            <small class="text-secondary">Transações</small>
            <h5 class="mb-0 fw-bold">Todos os lançamentos</h5>
          </div>
        </div>

        <!-- Month carousel -->
        <div class="month-carousel d-flex align-items-center gap-2 mb-2 overflow-auto pb-1">
          <button class="btn btn-sm" @click="prevMonth"><i class="bi bi-chevron-left"></i></button>
          <div
            v-for="m in visibleMonths"
            :key="m.key"
            class="month-chip"
            :class="{ active: m.mes === store.mesSelecionado && m.ano === store.anoSelecionado }"
            @click="selectMonth(m.mes, m.ano)"
          >
            <div class="month-name">{{ m.label }}</div>
            <div class="month-year">{{ m.ano }}</div>
          </div>
          <button class="btn btn-sm" @click="nextMonth"><i class="bi bi-chevron-right"></i></button>
        </div>

        <!-- Filter chips -->
        <div class="d-flex gap-2 overflow-auto pb-2">
          <button
            v-for="chip in chips"
            :key="chip.value"
            class="filter-chip"
            :class="{ active: activeChip === chip.value }"
            @click="setChip(chip.value)"
          >{{ chip.label }}</button>
        </div>

        <!-- Selects de conta e cartão -->
        <div class="d-flex gap-2 pb-2">
          <select class="form-select form-select-sm" v-model="selectedContaId" @change="applySelects">
            <option value="">Todas as contas</option>
            <option v-for="c in contaStore.contas" :key="c.id" :value="c.id">{{ c.nome }}</option>
          </select>
          <select class="form-select form-select-sm" v-model="selectedCartaoId" @change="applySelects">
            <option value="">Todos os cartões</option>
            <option v-for="c in cartaoStore.cartoes" :key="c.id" :value="c.id">{{ c.nome }}</option>
          </select>
        </div>
      </div>

      <!-- List -->
      <div class="tx-list px-3 py-2" v-if="!store.loading">
        <p v-if="!store.lancamentos.length" class="text-center text-secondary py-5">
          Nenhum lançamento neste período.
        </p>
        <template v-for="(group, date) in groupedByDate" :key="date">
          <div class="date-header">{{ formatDate(String(date)) }}</div>
          <div v-for="l in group" :key="l.id" class="tx-item card-modern mb-2">
            <div class="d-flex align-items-center">
              <div class="tx-icon me-3" :class="tipoClass(l)">
                <i :class="l.categoria?.icone || tipoIcon(l)"></i>
              </div>
              <div class="flex-grow-1">
                <div class="fw-semibold">{{ l.titulo }}</div>
                <div class="tx-meta">
                  <span v-if="l.conta_origem">{{ l.conta_origem.nome }}</span>
                  <span v-if="l.conta_destino"> → {{ l.conta_destino.nome }}</span>
                  <span v-else-if="l.cartao"> · {{ l.cartao.nome }}</span>
                  <span v-if="l.categoria"> · <i :class="l.categoria.icone" :style="{ color: l.categoria.cor }"></i> {{ l.categoria.nome }}</span>
                  <span v-if="l.subcategoria"> / {{ l.subcategoria.nome }}</span>
                  <span v-if="l.parcela_total"> · <span class="badge bg-secondary">{{ l.parcela_atual }}/{{ l.parcela_total }}x</span></span>
                </div>
                <div v-if="l.descricao" class="tx-desc">{{ l.descricao }}</div>
              </div>
              <div class="text-end">
                <div class="fw-bold" :class="tipoAmountClass(l)">
                  {{ tipoSign(l) }}{{ fmt(l.valor) }}
                </div>
                <div class="d-flex gap-2 justify-content-end mt-1">
                  <button class="btn p-0" style="line-height:1" @click="openEdit(l)">
                    <i class="bi bi-pencil text-secondary" style="font-size:.75rem"></i>
                  </button>
                  <button class="btn p-0" style="line-height:1" @click="confirmDelete(l)">
                    <i class="bi bi-trash text-danger" style="font-size:.75rem"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </template>
      </div>

      <div v-else class="text-center py-5"><div class="spinner-border text-success"></div></div>

      <!-- Sticky footer -->
      <div class="tx-footer">
        <div class="d-flex justify-content-between mb-1">
          <span class="text-secondary">Gastos</span>
          <span class="text-danger fw-semibold">{{ fmt(store.totalGastos) }}</span>
        </div>
        <div class="d-flex justify-content-between mb-1">
          <span class="text-secondary">Receitas</span>
          <span class="text-success fw-semibold">{{ fmt(store.totalReceitas) }}</span>
        </div>
        <div v-if="store.totalInvestimentos > 0" class="d-flex justify-content-between mb-1">
          <span class="text-secondary">Investimentos</span>
          <span class="text-purple fw-semibold">{{ fmt(store.totalInvestimentos) }}</span>
        </div>
        <div class="d-flex justify-content-between fw-bold">
          <span>Balanço total</span>
          <span :class="store.balanco >= 0 ? 'text-success' : 'text-danger'">
            {{ fmt(store.balanco) }}
          </span>
        </div>
      </div>
    </div>

    <!-- Delete confirm modal -->
    <div class="modal fade" :class="{ show: showDelete }" tabindex="-1" :style="showDelete ? 'display:block' : ''">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Excluir lançamento</h5>
            <button type="button" class="btn-close" @click="showDelete = false"></button>
          </div>
          <div class="modal-body">
            <p>Excluir <strong>{{ deleting?.titulo }}</strong>? O saldo será revertido.</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" @click="showDelete = false">Cancelar</button>
            <button type="button" class="btn btn-danger" @click="doDelete">Excluir</button>
          </div>
        </div>
      </div>
    </div>
    <div v-if="showDelete" class="modal-backdrop fade show"></div>

    <!-- Edit bottom sheet -->
    <div v-if="showEdit" class="modal-overlay" @click.self="showEdit = false">
      <div class="edit-sheet">
        <div class="sheet-handle"></div>
        <div class="d-flex align-items-center mb-3">
          <button class="btn btn-sm me-2" @click="showEdit = false"><i class="bi bi-x-lg"></i></button>
          <h6 class="mb-0">Editar lançamento</h6>
        </div>
        <form @submit.prevent="doEdit">
          <div class="mb-3">
            <label class="form-label">Título *</label>
            <input v-model="editForm.titulo" class="form-control" required maxlength="150" />
          </div>
          <div class="mb-3">
            <label class="form-label">Valor *</label>
            <div class="input-group">
              <span class="input-group-text">{{ uiStore.currencySymbol }}</span>
              <input
                :value="editValorDisplay"
                @input="onEditValorInput"
                class="form-control"
                inputmode="decimal"
                placeholder="0,00"
                required
              />
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Descrição</label>
            <input v-model="editForm.descricao" class="form-control" placeholder="Observação opcional" maxlength="255" />
          </div>
          <div class="mb-3">
            <label class="form-label">Categoria</label>
            <select v-model="editForm.categoria_id" class="form-select">
              <option value="">Sem categoria</option>
              <option v-for="c in categoriasAtivas" :key="c.id" :value="c.id">{{ c.nome }}</option>
            </select>
          </div>
          <div class="mb-3" v-if="editSubcategorias.length > 0">
            <label class="form-label">Subcategoria</label>
            <select v-model="editForm.subcategoria_id" class="form-select">
              <option value="">Sem subcategoria</option>
              <option v-for="s in editSubcategorias" :key="s.id" :value="s.id">{{ s.nome }}</option>
            </select>
          </div>
          <div v-if="editError" class="alert alert-danger py-2 mb-3">{{ editError }}</div>
          <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary flex-fill" @click="showEdit = false">Cancelar</button>
            <button type="submit" class="btn btn-primary flex-fill" :disabled="editSaving">
              <span v-if="editSaving" class="spinner-border spinner-border-sm me-1"></span>Salvar
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import AppLayout from '@/components/layouts/AppLayout.vue'
import { useTransactionStore, type Lancamento } from '@/stores/transaction'
import { useCartaoStore } from '@/stores/cartao'
import { useContaStore } from '@/stores/conta'
import { useUiStore } from '@/stores/ui'
import { useCategoriaStore } from '@/stores/categoria'

const store          = useTransactionStore()
const cartaoStore    = useCartaoStore()
const contaStore     = useContaStore()
const uiStore        = useUiStore()
const categoriaStore = useCategoriaStore()
const route       = useRoute()
const router      = useRouter()

const syncUrl = () => {
  const query: Record<string, string> = {}
  if (activeChip.value && activeChip.value !== 'geral') query.tipo = activeChip.value
  if (selectedContaId.value)  query.conta_id  = selectedContaId.value
  if (selectedCartaoId.value) query.cartao_id = selectedCartaoId.value
  router.replace({ query })
}
const showDelete = ref(false)
const deleting   = ref<Lancamento | null>(null)
const showEdit   = ref(false)
const editing    = ref<Lancamento | null>(null)
const editSaving = ref(false)
const editError  = ref('')
const editForm   = ref({ titulo: '', descricao: '', valor: 0, categoria_id: '', subcategoria_id: '' })
const editValorDisplay = ref('')

const onEditValorInput = (e: Event) => {
  const raw = (e.target as HTMLInputElement).value.replace(/\D/g, '')
  const cents = parseInt(raw || '0', 10)
  editForm.value.valor = cents / 100
  editValorDisplay.value = (cents / 100).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

const categoriasAtivas = computed(() => categoriaStore.categorias.filter((c: any) => c.ativo))

const editSubcategorias = computed(() =>
  editForm.value.categoria_id
    ? categoriaStore.subcategorias.filter((s: any) => s.categoria_id === editForm.value.categoria_id && s.ativo)
    : []
)

watch(() => editForm.value.categoria_id, () => { editForm.value.subcategoria_id = '' })

const openEdit = (l: Lancamento) => {
  editing.value = l
  editForm.value = {
    titulo:          l.titulo,
    descricao:       l.descricao ?? '',
    valor:           Number(l.valor),
    categoria_id:    l.categoria_id ?? '',
    subcategoria_id: l.subcategoria_id ?? '',
  }
  editValorDisplay.value = Number(l.valor).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
  editError.value = ''
  showEdit.value = true
}

const doEdit = async () => {
  if (!editing.value) return
  try {
    editSaving.value = true
    editError.value  = ''
    await store.updateLancamento(editing.value.id, {
      titulo:          editForm.value.titulo,
      descricao:       editForm.value.descricao || undefined,
      valor:           editForm.value.valor,
      categoria_id:    editForm.value.categoria_id || undefined,
      subcategoria_id: editForm.value.subcategoria_id || undefined,
    })
    showEdit.value = false
  } catch (e: any) {
    const errors = e.response?.data?.errors
    if (errors) {
      editError.value = (Object.values(errors).flat() as string[])[0] ?? 'Erro ao salvar'
    } else {
      editError.value = e.response?.data?.message ?? e.message ?? 'Erro ao salvar'
    }
  } finally {
    editSaving.value = false
  }
}
const activeChip  = ref('geral')
const selectedContaId  = ref('')
const selectedCartaoId = ref('')

const chips = [
  { value: 'geral',         label: 'Geral' },
  { value: 'RECEITAS',      label: 'Receitas' },
  { value: 'DESPESAS',      label: 'Despesas' },
  { value: 'TRANSFERENCIA', label: 'Transferências' },
  { value: 'INVESTIMENTOS', label: 'Investimentos' },
]

const setChip = (v: string) => {
  activeChip.value       = v
  selectedContaId.value  = ''
  selectedCartaoId.value = ''
  store.filtroTipo       = v === 'geral' ? '' : v
  store.filtroContaId    = ''
  store.filtroCartaoId   = ''
  syncUrl()
  store.fetchLancamentos()
}

const applySelects = () => {
  store.filtroContaId  = selectedContaId.value
  store.filtroCartaoId = selectedCartaoId.value
  syncUrl()
  store.fetchLancamentos()
}

const visibleMonths = computed(() => {
  const result = []
  const meses = ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez']
  for (let i = -1; i <= 1; i++) {
    const d = new Date(store.anoSelecionado, store.mesSelecionado - 1 + i)
    result.push({
      mes: d.getMonth() + 1,
      ano: d.getFullYear(),
      label: meses[d.getMonth()],
      key: `${d.getFullYear()}-${d.getMonth()}`
    })
  }
  return result
})

const prevMonth = () => {
  if (store.mesSelecionado === 1) { store.mesSelecionado = 12; store.anoSelecionado-- }
  else store.mesSelecionado--
  store.fetchLancamentos()
}

const nextMonth = () => {
  if (store.mesSelecionado === 12) { store.mesSelecionado = 1; store.anoSelecionado++ }
  else store.mesSelecionado++
  store.fetchLancamentos()
}

const selectMonth = (mes: number, ano: number) => {
  store.mesSelecionado = mes
  store.anoSelecionado = ano
  store.fetchLancamentos()
}

const groupedByDate = computed(() => {
  const groups: Record<string, Lancamento[]> = {}
  for (const l of store.lancamentos) {
    const d = l.data.substring(0, 10)
    if (!groups[d]) groups[d] = []
    groups[d].push(l)
  }
  return groups
})

const fmt = (v: number) => uiStore.formatCurrency(v)

const formatDate = (d: string) =>
  new Date(d + 'T12:00:00').toLocaleDateString('pt-BR', { weekday: 'short', day: '2-digit', month: 'long' })

const tipoIcon = (l: Lancamento) => ({
  RECEITAS:      'bi bi-arrow-down-circle',
  DESPESAS:      'bi bi-arrow-up-circle',
  TRANSFERENCIA: 'bi bi-arrow-left-right',
  INVESTIMENTOS: 'bi bi-graph-up-arrow',
}[l.tipo_lancamento] ?? 'bi bi-dash-circle')

const tipoClass = (l: Lancamento) => ({
  RECEITAS:      'tx-icon-income',
  DESPESAS:      'tx-icon-expense',
  TRANSFERENCIA: 'tx-icon-transfer',
  INVESTIMENTOS: 'tx-icon-invest',
}[l.tipo_lancamento] ?? '')

const tipoAmountClass = (l: Lancamento) => ({
  RECEITAS:      'text-success',
  DESPESAS:      'text-danger',
  TRANSFERENCIA: 'text-info',
  INVESTIMENTOS: 'text-purple',
}[l.tipo_lancamento] ?? '')

const tipoSign = (l: Lancamento) =>
  l.tipo_lancamento === 'RECEITAS' ? '+' : '-'

const confirmDelete = (l: Lancamento) => { deleting.value = l; showDelete.value = true }

const doDelete = async () => {
  if (!deleting.value) return
  await store.deleteLancamento(deleting.value.id)
  showDelete.value = false
}

onMounted(async () => {
  await Promise.all([cartaoStore.fetchCartoes(), contaStore.fetchContas(), categoriaStore.fetchCategorias(), categoriaStore.fetchSubcategorias()])
  const cartaoId = route.query.cartao_id as string | undefined
  if (cartaoId) {
    selectedCartaoId.value = cartaoId
    store.filtroCartaoId   = cartaoId
    activeChip.value       = 'DESPESAS'
    store.filtroTipo       = 'DESPESAS'
  }
  const contaId = route.query.conta_id as string | undefined
  if (contaId) {
    selectedContaId.value = contaId
    store.filtroContaId   = contaId
  }
  store.fetchLancamentos()
})
</script>

<style scoped lang="scss">
.tx-page { display: flex; flex-direction: column; min-height: calc(100vh - 70px); }

.month-carousel { scrollbar-width: none; &::-webkit-scrollbar { display: none; } }

.month-chip {
  flex-shrink: 0; text-align: center; padding: 0.4rem 0.75rem;
  border-radius: 0.75rem; cursor: pointer;
  color: var(--bs-secondary-color, #6c757d); background: none;
  transition: all .15s;
  .month-name { font-size: .85rem; font-weight: 500; }
  .month-year { font-size: .65rem; }
  &.active { background: #22c55e; color: white; }
}

.filter-chip {
  flex-shrink: 0; padding: 0.3rem 0.875rem;
  border-radius: 999px; border: 1px solid var(--bs-border-color);
  background: none; font-size: .85rem; color: var(--bs-body-color);
  cursor: pointer;
  &.active { background: #22c55e; color: white; border-color: #22c55e; }
}

.date-header {
  font-size: .75rem; color: var(--bs-secondary-color, #6c757d);
  font-weight: 600; margin: 0.75rem 0 0.35rem;
  text-transform: capitalize;
}

.tx-item { padding: 0.75rem 1rem; }

.tx-meta {
  font-size: .75rem; color: var(--bs-secondary-color, #6c757d);
  margin-top: 0.1rem;
}
.tx-desc {
  font-size: .72rem; color: var(--bs-secondary-color, #6c757d);
  font-style: italic; margin-top: 0.1rem;
}

.tx-icon {
  width: 40px; height: 40px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center; font-size: 1.1rem;
  flex-shrink: 0;
  &.tx-icon-income   { background: rgba(34,197,94,.15);  color: #22c55e; }
  &.tx-icon-expense  { background: rgba(239,68,68,.15);  color: #ef4444; }
  &.tx-icon-transfer { background: rgba(59,130,246,.15); color: #3b82f6; }
  &.tx-icon-invest   { background: rgba(168,85,247,.15); color: #a855f7; }
}

.tx-footer {
  position: sticky; bottom: var(--bottom-nav-height, 70px);
  background: var(--bs-card-bg, #fff);
  border-top: 1px solid var(--bs-border-color);
  padding: 0.875rem 1.25rem;
  z-index: 100;
}

@media (min-width: 768px) {
  .tx-footer { bottom: 0; }
}

.text-purple { color: #a855f7; }

.modal-overlay {
  position: fixed; inset: 0;
  background: rgba(0,0,0,.5);
  display: flex; align-items: flex-end;
  z-index: 2000;
}

.edit-sheet {
  background: var(--bs-card-bg, #fff);
  border-radius: 1.25rem 1.25rem 0 0;
  padding: 1.25rem;
  width: 100%;
  max-height: 90vh;
  overflow-y: auto;
}

.sheet-handle {
  width: 40px; height: 4px;
  background: var(--bs-border-color);
  border-radius: 2px;
  margin: 0 auto 1rem;
}
</style>

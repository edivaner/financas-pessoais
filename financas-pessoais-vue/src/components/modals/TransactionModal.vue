<!-- resources/js/components/modals/TransactionModal.vue -->
<template>
  <!-- Bottom sheet overlay -->
  <div class="modal-overlay" @click.self="emit('close')">
    <!-- Type selection -->
    <div class="type-sheet" v-if="!tipo">
      <div class="sheet-handle"></div>
      <h6 class="text-center mb-4">O que você quer <strong>Adicionar?</strong></h6>
      <button class="type-btn income"   @click="tipo = 'RECEITAS'">
        Receita <i class="bi bi-plus-circle ms-auto"></i>
      </button>
      <button class="type-btn expense"  @click="tipo = 'DESPESAS'">
        Despesa <i class="bi bi-dash-circle ms-auto"></i>
      </button>
      <button class="type-btn transfer" @click="tipo = 'TRANSFERENCIA'">
        Transferência <i class="bi bi-arrow-left-right ms-auto"></i>
      </button>
      <button class="type-btn invest"   @click="tipo = 'INVESTIMENTOS'">
        Investimento <i class="bi bi-graph-up-arrow ms-auto"></i>
      </button>
    </div>

    <!-- Form -->
    <div class="form-sheet" v-else>
      <div class="sheet-handle"></div>
      <div class="d-flex align-items-center mb-3">
        <button class="btn btn-sm me-2" @click="tipo = ''">
          <i class="bi bi-arrow-left"></i>
        </button>
        <h6 class="mb-0">Nova {{ tipoLabel }}</h6>
      </div>

      <form @submit.prevent="submit">
        <div class="mb-3">
          <label class="form-label">Título *</label>
          <input v-model="form.titulo" class="form-control" required placeholder="Ex: Aluguel" maxlength="100" />
        </div>

        <div class="mb-3">
          <label class="form-label">Valor *</label>
          <div class="input-group">
            <span class="input-group-text">{{ uiStore.currencySymbol }}</span>
            <input
              :value="valorFormatado"
              @input="onValorInput"
              @blur="onValorBlur"
              class="form-control"
              inputmode="decimal"
              placeholder="0,00"
              required
            />
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label">Data *</label>
          <input ref="dateInputRef" class="form-control" readonly required placeholder="DD/MM/AAAA" />
        </div>

        <div class="mb-3">
          <label class="form-label">{{ tipo === 'TRANSFERENCIA' ? 'Conta de origem *' : 'Conta *' }}</label>
          <select v-model="form.conta_origem_id" class="form-select" required>
            <option value="">Selecione a conta</option>
            <option v-for="c in contaStore.contas" :key="c.id" :value="c.id">{{ c.nome }}</option>
          </select>
        </div>

        <template v-if="tipo === 'TRANSFERENCIA'">
          <div class="mb-3" v-if="!form.para_saldo_investido">
            <label class="form-label">Conta de destino</label>
            <select v-model="form.conta_destino_id" class="form-select">
              <option value="">Selecione a conta</option>
              <option v-for="c in contaStore.contas" :key="c.id" :value="c.id">{{ c.nome }}</option>
            </select>
          </div>
          <div class="mb-3">
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" role="switch" v-model="form.para_saldo_investido" id="chkInvestido" />
              <label class="form-check-label" for="chkInvestido">Enviar para saldo investido</label>
            </div>
          </div>
        </template>

        <template v-if="tipo === 'DESPESAS'">
          <div class="mb-3">
            <label class="form-label">Cartão (opcional)</label>
            <select v-model="form.cartao_id" class="form-select">
              <option value="">Sem cartão</option>
              <option v-for="c in cartaoStore.cartoes" :key="c.id" :value="c.id">{{ c.nome }}</option>
            </select>
          </div>
          <div v-if="form.cartao_id" class="mb-3">
            <label class="form-label">Tipo de pagamento</label>
            <select v-model="form.tipo_cartao" class="form-select">
              <option value="CREDITO">Crédito</option>
              <option value="DEBITO">Débito</option>
            </select>
          </div>
          <div v-if="!form.cartao_id || form.tipo_cartao === 'CREDITO'" class="mb-3">
            <label class="form-label">Parcelamento</label>
            <select v-model.number="form.parcela_total" class="form-select">
              <option :value="1">À vista</option>
              <option v-for="n in [2,3,4,5,6,7,8,9,10,11,12,18,24,36,48,60]" :key="n" :value="n">{{ n }}x</option>
            </select>
          </div>
        </template>

        <template v-if="tipo === 'INVESTIMENTOS'">
          <div class="mb-3">
            <label class="form-label">Operação *</label>
            <select v-model="form.tipo_cartao" class="form-select" required>
              <option value="INVESTIR">Investir</option>
              <option value="RESGATAR">Resgatar</option>
            </select>
          </div>
        </template>

        <div class="mb-3" v-if="tipo !== 'TRANSFERENCIA'">
          <label class="form-label">Categoria</label>
          <select v-model="form.categoria_id" class="form-select">
            <option value="">Sem categoria</option>
            <option v-for="c in categoriaStore.categorias.filter(c => c.ativo)" :key="c.id" :value="c.id">{{ c.nome }}</option>
          </select>
        </div>

        <div class="mb-3" v-if="subcategoriasDaCategoria.length > 0">
          <label class="form-label">Subcategoria</label>
          <select v-model="form.subcategoria_id" class="form-select">
            <option value="">Sem subcategoria</option>
            <option v-for="s in subcategoriasDaCategoria" :key="s.id" :value="s.id">{{ s.nome }}</option>
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label">Descrição</label>
          <input v-model="form.descricao" class="form-control" placeholder="Observação opcional" maxlength="255" />
        </div>

        <div v-if="error" class="alert alert-danger py-2 mb-3">{{ error }}</div>

        <div class="d-flex gap-2">
          <button type="button" class="btn btn-outline-secondary flex-fill" @click="emit('close')">Cancelar</button>
          <button type="submit" class="btn btn-success flex-fill" :disabled="saving">
            <span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>
            Salvar
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted, watch, nextTick } from 'vue'
import flatpickr from 'flatpickr'
import { Portuguese } from 'flatpickr/dist/l10n/pt'
import 'flatpickr/dist/flatpickr.min.css'
import { useContaStore } from '@/stores/conta'
import { useCartaoStore } from '@/stores/cartao'
import { useCategoriaStore } from '@/stores/categoria'
import { useTransactionStore } from '@/stores/transaction'
import { useUiStore } from '@/stores/ui'

const emit = defineEmits<{ (e: 'close'): void; (e: 'created'): void }>()

const contaStore     = useContaStore()
const cartaoStore    = useCartaoStore()
const categoriaStore = useCategoriaStore()
const txStore        = useTransactionStore()
const uiStore        = useUiStore()

const tipo        = ref('')
const saving      = ref(false)
const error       = ref('')
const dateInputRef = ref<HTMLInputElement | null>(null)

const tipoLabel = computed(() => ({
  RECEITAS: 'Receita', DESPESAS: 'Despesa',
  TRANSFERENCIA: 'Transferência', INVESTIMENTOS: 'Investimento'
}[tipo.value] ?? ''))

const form = reactive({
  titulo: '', valor: 0, data: (() => { const d = new Date(); return `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}` })(),
  conta_origem_id: '', conta_destino_id: '', cartao_id: '', tipo_cartao: 'CREDITO',
  categoria_id: '', subcategoria_id: '', descricao: '', parcela_total: 1, para_saldo_investido: false,
})

// currency mask
const valorDisplay = ref('')

const valorFormatado = computed(() => valorDisplay.value)

const onValorInput = (e: Event) => {
  const raw = (e.target as HTMLInputElement).value.replace(/\D/g, '')
  const cents = parseInt(raw || '0', 10)
  form.valor = cents / 100
  valorDisplay.value = (cents / 100).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

const onValorBlur = () => {
  valorDisplay.value = form.valor > 0
    ? form.valor.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
    : ''
}

// subcategories filtered by selected categoria
const subcategoriasDaCategoria = computed(() =>
  form.categoria_id
    ? categoriaStore.subcategorias.filter((s: { categoria_id: string; ativo: boolean }) => s.categoria_id === form.categoria_id && s.ativo)
    : []
)

watch(() => form.categoria_id, () => { form.subcategoria_id = '' })

const submit = async () => {
  try {
    saving.value = true
    error.value  = ''

    const payload: Record<string, any> = {
      titulo:          form.titulo,
      valor:           form.valor,
      data:            form.data,
      tipo_lancamento: tipo.value,
      conta_origem_id: form.conta_origem_id,
      descricao:        form.descricao || undefined,
      categoria_id:     form.categoria_id || undefined,
      subcategoria_id:  form.subcategoria_id || undefined,
    }

    if (tipo.value === 'DESPESAS') {
      if (form.parcela_total > 1) {
        payload.parcela_total = form.parcela_total
      }
      if (form.cartao_id) {
        payload.cartao_id   = form.cartao_id
        payload.tipo_cartao = form.tipo_cartao
      }
    }

    if (tipo.value === 'TRANSFERENCIA') {
      payload.para_saldo_investido = form.para_saldo_investido
      if (!form.para_saldo_investido) {
        payload.conta_destino_id = form.conta_destino_id
      }
    }

    if (tipo.value === 'INVESTIMENTOS') {
      payload.tipo_cartao = form.tipo_cartao
    }

    await txStore.createLancamento(payload)
    emit('created')
    emit('close')
  } catch (e: any) {
    error.value = e.response?.data?.message ?? 'Erro ao salvar'
  } finally {
    saving.value = false
  }
}

const today = () => {
  const d = new Date()
  return `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`
}

const resetForm = () => {
  form.titulo             = ''
  form.valor              = 0
  form.data               = today()
  form.conta_origem_id    = ''
  form.conta_destino_id   = ''
  form.cartao_id          = ''
  form.tipo_cartao        = 'CREDITO'
  form.categoria_id       = ''
  form.subcategoria_id    = ''
  form.descricao          = ''
  form.parcela_total      = 1
  form.para_saldo_investido = false
  valorDisplay.value      = ''
  error.value             = ''
}

onMounted(async () => {
  await Promise.all([
    contaStore.fetchContas(),
    cartaoStore.fetchCartoes(),
    categoriaStore.fetchCategorias(),
    categoriaStore.fetchSubcategorias(),
  ])
})

const initDatepicker = () => {
  nextTick(() => {
    if (!dateInputRef.value) return
    flatpickr(dateInputRef.value, {
      locale: Portuguese,
      dateFormat: 'd/m/Y',
      defaultDate: new Date(form.data + 'T12:00:00'),
      onChange: ([date]: Date[]) => {
        if (date) {
          const y = date.getFullYear()
          const m = String(date.getMonth() + 1).padStart(2, '0')
          const d = String(date.getDate()).padStart(2, '0')
          form.data = `${y}-${m}-${d}`
        }
      },
    })
  })
}

watch(tipo, (val: string) => {
  if (val) initDatepicker()
  else resetForm()
})
</script>

<style scoped lang="scss">
.modal-overlay {
  position: fixed; inset: 0;
  background: rgba(0,0,0,.5);
  display: flex; align-items: flex-end;
  z-index: 2000;
}

.type-sheet, .form-sheet {
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

.type-btn {
  display: flex; align-items: center;
  width: 100%; padding: 1rem 1.25rem;
  border: 2px solid var(--bs-border-color);
  border-radius: 0.75rem;
  background: none;
  font-weight: 500;
  margin-bottom: 0.75rem;
  font-size: 1rem;
  color: var(--bs-body-color);
  transition: border-color .15s;

  &.income   { border-color: #22c55e; color: #22c55e; }
  &.expense  { border-color: #ef4444; color: #ef4444; }
  &.transfer { border-color: #3b82f6; color: #3b82f6; }
  &.invest   { border-color: #a855f7; color: #a855f7; }
}
</style>

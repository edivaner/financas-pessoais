<template>
  <form @submit.prevent="submit">
    <!-- Logo picker -->
    <div class="mb-3">
      <label class="form-label">Logo do banco</label>
      <div class="logo-grid">
        <button
          v-for="bank in banks"
          :key="bank.slug"
          type="button"
          class="logo-btn"
          :class="{ selected: form.logo_path === bank.path }"
          :title="bank.nome"
          :disabled="!!modelValue"
          @click="selectBank(bank)"
        >
          <img :src="bank.path" :alt="bank.nome" />
        </button>
        <button
          type="button"
          class="logo-btn clear-btn"
          :class="{ selected: !form.logo_path }"
          title="Sem logo"
          :disabled="!!modelValue"
          @click="selectLogo('')"
        >
          <i class="bi bi-credit-card"></i>
        </button>
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label">Conta de pagamento *</label>
      <select v-model="form.conta_id" class="form-select" :disabled="!!modelValue" required>
        <option value="">Selecione a conta</option>
        <option v-for="c in contaStore.contas" :key="c.id" :value="c.id">{{ c.nome }}</option>
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">Nome do cartão *</label>
      <input v-model="form.nome" class="form-control" placeholder="Ex: Nubank" maxlength="100" required :readonly="!modelValue && !!form.logo_path" />
    </div>

    <div class="mb-3">
      <label class="form-label">Tipo *</label>
      <select v-model="form.tipo" class="form-select" required>
        <option value="CREDITO">Crédito</option>
        <option value="DEBITO">Débito</option>
        <option value="MULTIPLO">Múltiplo (Crédito e Débito)</option>
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">Limite total</label>
      <CurrencyInput v-model="form.limite_total" placeholder="0,00" />
    </div>

    <div class="mb-3">
      <label class="form-label">Dia de vencimento</label>
      <input v-model.number="form.dia_vencimento" class="form-control" type="number" min="1" max="31" placeholder="Ex: 10" />
    </div>

    <button type="button" class="btn btn-link btn-sm px-0 mb-3 text-secondary" @click="showMore = !showMore">
      <i class="bi" :class="showMore ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
      {{ showMore ? 'Menos opções' : 'Mais opções' }}
    </button>

    <div v-if="showMore" class="mb-3">
      <label class="form-label">Dias entre vencimento e fechamento</label>
      <input v-model.number="form.dias_antes_fechamento" class="form-control" type="number" min="1" max="28" placeholder="Ex: 5" />
      <div class="form-text">Quantos dias antes do vencimento a fatura fecha.</div>
    </div>

    <div v-if="error" class="alert alert-danger py-2 mb-3">{{ error }}</div>

    <div class="d-flex gap-2 justify-content-end">
      <button type="button" class="btn btn-outline-secondary" @click="emit('cancel')">Cancelar</button>
      <button type="submit" class="btn btn-success" :disabled="loading">
        <span v-if="loading" class="spinner-border spinner-border-sm me-1"></span>
        {{ modelValue ? 'Salvar' : 'Criar' }}
      </button>
    </div>
  </form>
</template>

<script setup lang="ts">
import { reactive, watch, ref, onMounted } from 'vue'
import axios from 'axios'
import { useContaStore } from '@/stores/conta'
import { useCartaoStore, type Cartao } from '@/stores/cartao'
import CurrencyInput from '@/components/ui/CurrencyInput.vue'

const props  = defineProps<{ modelValue?: Cartao | null }>()
const emit   = defineEmits<{ (e: 'cancel'): void; (e: 'saved'): void }>()
const cartaoStore = useCartaoStore()
const contaStore  = useContaStore()
const loading  = ref(false)
const error    = ref('')
const showMore = ref(false)

interface BankLogo { slug: string; nome: string; path: string }
const banks = ref<BankLogo[]>([])

onMounted(async () => {
  const { data } = await axios.get('/api/imagens/bank-logos')
  banks.value = data.data
})

const form = reactive({
  conta_id: '',
  nome: '',
  tipo: 'CREDITO' as 'CREDITO' | 'DEBITO' | 'MULTIPLO',
  limite_total: 0,
  dia_vencimento: null as number | null,
  dias_antes_fechamento: 5,
  logo_path: null as string | null,
})

const selectBank = (bank: BankLogo) => {
  form.logo_path = bank.path || null
  if (!props.modelValue) form.nome = bank.nome
}

const selectLogo = (path: string) => {
  form.logo_path = path || null
  if (!props.modelValue) form.nome = ''
}

watch(() => props.modelValue, (v) => {
  if (v) {
    form.conta_id              = v.conta_id
    form.nome                  = v.nome
    form.tipo                  = v.tipo
    form.limite_total          = Number(v.limite_total)
    form.dia_vencimento        = v.dia_vencimento ?? null
    form.dias_antes_fechamento = v.dias_antes_fechamento ?? 5
    form.logo_path             = v.logo_path ?? null
  } else {
    Object.assign(form, { conta_id: '', nome: '', tipo: 'CREDITO', limite_total: 0, dia_vencimento: null, dias_antes_fechamento: 5, logo_path: null })
  }
}, { immediate: true })

const submit = async () => {
  try {
    loading.value = true
    error.value = ''
    const logoPayload = { logo_path: form.logo_path || null, imagem_id: null }
    if (props.modelValue) {
      await cartaoStore.updateCartao(props.modelValue.id, {
        nome:                  form.nome,
        tipo:                  form.tipo,
        limite_total:          form.limite_total,
        dia_vencimento:        form.dia_vencimento,
        dias_antes_fechamento: form.dias_antes_fechamento,
        ...logoPayload,
      })
    } else {
      await cartaoStore.createCartao({ ...form, ...logoPayload })
    }
    emit('saved')
  } catch (e: any) {
    error.value = e.response?.data?.message ?? 'Erro ao salvar cartão'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped lang="scss">
.logo-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(56px, 1fr));
  gap: 0.5rem;
}

.logo-btn {
  width: 56px;
  height: 56px;
  border-radius: 0.5rem;
  border: 2px solid var(--bs-border-color);
  background: var(--bs-secondary-bg);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 6px;
  cursor: pointer;
  transition: border-color .15s, box-shadow .15s;

  img {
    width: 100%;
    height: 100%;
    object-fit: contain;
  }

  &:hover {
    border-color: var(--bs-success);
  }

  &.selected {
    border-color: var(--bs-success);
    box-shadow: 0 0 0 3px rgba(34,197,94,.25);
  }

  &.clear-btn {
    font-size: 1.4rem;
    color: var(--bs-secondary-color);
  }
}

:root[data-bs-theme="dark"] .logo-btn:not(.clear-btn) { background: #fff; }
</style>

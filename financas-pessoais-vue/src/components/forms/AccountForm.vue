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
          <i class="bi bi-bank2"></i>
        </button>
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label">Nome da conta *</label>
      <input v-model="form.nome" class="form-control" placeholder="Ex: Nubank" maxlength="100" required :readonly="!modelValue && !!form.logo_path" />
    </div>

    <div class="mb-3">
      <label class="form-label">Saldo inicial</label>
      <CurrencyInput v-model="form.saldo" placeholder="0,00" :disabled="!!modelValue" />
      <div v-if="modelValue" class="form-text text-warning">
        <i class="bi bi-info-circle me-1"></i>Para alterar o saldo, faça um lançamento.
      </div>
    </div>

    <div class="mb-3 d-flex gap-3 flex-wrap">
      <div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" role="switch" v-model="form.carteira" id="chkCarteira" />
        <label class="form-check-label" for="chkCarteira">Dinheiro físico (carteira)</label>
      </div>
      <div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" role="switch" v-model="form.somar_tela_inicial" id="chkSomar" />
        <label class="form-check-label" for="chkSomar">Mostrar na tela inicial</label>
      </div>
    </div>

    <template v-if="!form.carteira">
      <button type="button" class="btn btn-link btn-sm px-0 mb-3 text-secondary" @click="showMore = !showMore">
        <i class="bi" :class="showMore ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
        {{ showMore ? 'Menos opções' : 'Mais opções' }}
      </button>

      <div v-if="showMore" class="mb-3">
        <label class="form-label">Saldo investido</label>
        <CurrencyInput v-model="form.saldo_investido" placeholder="0,00" :disabled="!!modelValue" />
        <div v-if="modelValue" class="form-text text-warning">
          <i class="bi bi-info-circle me-1"></i>Para alterar o saldo investido, faça um lançamento de investimento.
        </div>
      </div>
    </template>

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
import { useContaStore, type Conta } from '@/stores/conta'
import CurrencyInput from '@/components/ui/CurrencyInput.vue'

const props  = defineProps<{ modelValue?: Conta | null }>()
const emit   = defineEmits<{ (e: 'cancel'): void; (e: 'saved'): void }>()
const store  = useContaStore()
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
  nome: '',
  saldo: 0,
  saldo_investido: 0,
  carteira: false,
  somar_tela_inicial: true,
  logo_path: '' as string | null,
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
    form.nome = v.nome
    form.saldo = Number(v.saldo)
    form.saldo_investido = Number(v.saldo_investido)
    form.carteira = v.carteira
    form.somar_tela_inicial = v.somar_tela_inicial
    form.logo_path = v.logo_path ?? null
  } else {
    Object.assign(form, { nome: '', saldo: 0, saldo_investido: 0, carteira: false, somar_tela_inicial: true, logo_path: null })
  }
}, { immediate: true })

const submit = async () => {
  try {
    loading.value = true
    error.value = ''
    const logoPayload = { logo_path: form.logo_path || null, imagem_id: null }
    if (props.modelValue) {
      await store.updateConta(props.modelValue.id, {
        nome: form.nome,
        carteira: form.carteira,
        somar_tela_inicial: form.somar_tela_inicial,
        saldo_investido: form.saldo_investido,
        ...logoPayload,
      })
    } else {
      await store.createConta({ ...form, ...logoPayload } as any)
    }
    emit('saved')
  } catch (e: any) {
    error.value = e.response?.data?.message ?? 'Erro ao salvar conta'
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

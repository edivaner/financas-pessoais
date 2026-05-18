<template>
  <AppLayout>
    <div class="container-fluid px-3 py-3">
      <div class="d-flex align-items-center mb-3">
        <router-link to="/profile" class="btn btn-sm me-2"><i class="bi bi-arrow-left"></i></router-link>
        <h5 class="mb-0 fw-bold">Minhas Contas</h5>
        <button class="btn btn-sm btn-success ms-auto" @click="openCreate">
          <i class="bi bi-plus-lg me-1"></i> Adicionar
        </button>
      </div>

      <div v-if="store.loading" class="text-center py-4">
        <div class="spinner-border text-success"></div>
      </div>

      <div v-else>
        <div
          v-for="conta in store.contas"
          :key="conta.id"
          class="card-modern mb-2 d-flex align-items-center px-3 py-3"
        >
          <div class="account-logo me-3">
            <img v-if="conta.imagem || conta.logo_path" :src="conta.imagem ? conta.imagem.caminho : conta.logo_path" :alt="conta.nome" />
            <i v-else class="bi bi-bank2 text-success fs-4"></i>
          </div>
          <div class="flex-grow-1">
            <div class="fw-semibold">{{ conta.nome }}</div>
            <small class="text-secondary d-flex flex-wrap gap-2">
              <span>Saldo: <span class="text-success fw-semibold">{{ uiStore.formatCurrency(conta.saldo) }}</span></span>
              <span v-if="!conta.carteira">Investido: <span class="text-info fw-semibold">{{ uiStore.formatCurrency(conta.saldo_investido) }}</span></span>
            </small>
          </div>
          <div class="d-flex gap-2">
            <button class="btn btn-sm btn-outline-primary" @click="openEdit(conta)"><i class="bi bi-pencil"></i></button>
            <button class="btn btn-sm btn-outline-danger" @click="confirmDelete(conta)"><i class="bi bi-trash"></i></button>
          </div>
        </div>

        <p v-if="!store.contas.length" class="text-center text-secondary py-4">Nenhuma conta cadastrada.</p>
      </div>
    </div>

    <!-- Offcanvas form -->
    <div class="offcanvas offcanvas-bottom" :class="{ show: showForm }" tabindex="-1" style="height:auto; max-height:90vh; border-radius:1.25rem 1.25rem 0 0;">
      <div class="offcanvas-header">
        <h6 class="offcanvas-title">{{ editing ? 'Editar Conta' : 'Nova Conta' }}</h6>
        <button type="button" class="btn-close" @click="showForm = false"></button>
      </div>
      <div class="offcanvas-body pt-0">
        <AccountForm :model-value="editing" @cancel="showForm = false" @saved="onSaved" />
      </div>
    </div>
    <div v-if="showForm" class="offcanvas-backdrop fade show" @click="showForm = false"></div>

    <!-- Delete confirm modal -->
    <div class="modal fade" :class="{ show: showDelete }" tabindex="-1" :style="showDelete ? 'display:block' : ''">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Confirmar exclusão</h5>
            <button type="button" class="btn-close" @click="showDelete = false"></button>
          </div>
          <div class="modal-body">
            <p>Excluir <strong>{{ deleting?.nome }}</strong>? Todos os lançamentos vinculados serão removidos.</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" @click="showDelete = false">Cancelar</button>
            <button type="button" class="btn btn-danger" @click="doDelete">Excluir</button>
          </div>
        </div>
      </div>
    </div>
    <div v-if="showDelete" class="modal-backdrop fade show"></div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import AppLayout from '@/components/layouts/AppLayout.vue'
import AccountForm from '@/components/forms/AccountForm.vue'
import { useContaStore, type Conta } from '@/stores/conta'
import { useUiStore } from '@/stores/ui'

const store    = useContaStore()
const uiStore  = useUiStore()
const showForm   = ref(false)
const showDelete = ref(false)
const editing    = ref<Conta | null>(null)
const deleting   = ref<Conta | null>(null)

const openCreate    = () => { editing.value = null; showForm.value = true }
const openEdit      = (c: Conta) => { editing.value = c; showForm.value = true }
const confirmDelete = (c: Conta) => { deleting.value = c; showDelete.value = true }
const onSaved       = () => { showForm.value = false; store.fetchContas() }

const doDelete = async () => {
  if (!deleting.value) return
  await store.deleteConta(deleting.value.id)
  showDelete.value = false
}

onMounted(() => store.fetchContas())
</script>

<style scoped lang="scss">
.account-logo {
  width: 44px; height: 44px; border-radius: 0.5rem;
  background: var(--bs-secondary-bg, #f1f5f9);
  display: flex; align-items: center; justify-content: center; overflow: hidden;
  img { width: 100%; height: 100%; object-fit: contain; padding: 4px; }
}

:root[data-bs-theme="dark"] .account-logo { background: #fff; }
</style>

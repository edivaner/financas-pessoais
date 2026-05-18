<template>
  <div class="modal-overlay" @click="close">
    <div class="modal-container" @click.stop>
      <div class="modal-header">
        <h5>Gerenciar Contas</h5>
        <button type="button" class="btn-close" @click="close"></button>
      </div>
      
      <div class="modal-body">
        <!-- Botão de Adicionar -->
        <div class="mb-3">
          <button class="btn btn-primary" @click="showAddForm = true">
            <i class="bi bi-plus"></i>
            Adicionar Conta
          </button>
        </div>

        <!-- Lista de Contas -->
        <div class="accounts-list">
          <div v-if="isLoading" class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Carregando...</span>
            </div>
          </div>
          
          <div v-else-if="contas.length === 0" class="text-center py-4">
            <i class="bi bi-bank display-1 text-muted"></i>
            <p class="text-muted mt-3">Nenhuma conta cadastrada</p>
          </div>
          
          <div v-else class="accounts-items">
            <div
              v-for="conta in contas"
              :key="conta.id"
              class="account-item"
            >
              <div class="account-image">
                <img
                  v-if="conta.imagem || conta.logo_path"
                  :src="conta.imagem ? conta.imagem.caminho : conta.logo_path"
                  :alt="conta.nome"
                >
                <div v-else class="account-placeholder">
                  <i class="bi bi-bank"></i>
                </div>
              </div>
              
              <div class="account-info">
                <h6>{{ conta.nome }}</h6>
                <div class="account-balance">
                  <span class="balance-item">
                    <span class="label">Saldo:</span>
                    <span class="value text-success">
                      {{ formatCurrency(conta.saldo) }}
                    </span>
                  </span>
                  <span class="balance-item">
                    <span class="label">Investido:</span>
                    <span class="value text-info">
                      {{ formatCurrency(conta.saldo_investido) }}
                    </span>
                  </span>
                </div>
                <div class="account-meta">
                  <span v-if="conta.carteira" class="badge bg-warning">Carteira</span>
                  <span v-if="conta.somar_tela_inicial" class="badge bg-success">Soma na Tela Inicial</span>
                </div>
              </div>
              
              <div class="account-actions">
                <button
                  class="btn btn-sm btn-outline-primary"
                  @click="editAccount(conta)"
                >
                  <i class="bi bi-pencil"></i>
                </button>
                <button
                  class="btn btn-sm btn-outline-danger"
                  @click="deleteAccount(conta)"
                >
                  <i class="bi bi-trash"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useDashboardStore } from '@/stores/dashboard'
import { useUiStore } from '@/stores/ui'

const emit = defineEmits<{
  close: []
}>()

const dashboardStore = useDashboardStore()
const showAddForm = ref(false)

const contas = computed(() => dashboardStore.contas)
const isLoading = computed(() => dashboardStore.isLoading)

const { formatCurrency } = useUiStore()

const editAccount = (conta: any) => {
  // TODO: Implementar edição de conta
  console.log('Editar conta:', conta)
}

const deleteAccount = async (conta: any) => {
  if (confirm(`Tem certeza que deseja excluir a conta "${conta.nome}"?`)) {
    const result = await dashboardStore.deleteConta(conta.id)
    if (!result.success) {
      alert(result.message || 'Erro ao excluir conta')
    }
  }
}

const close = () => {
  emit('close')
}

onMounted(() => {
  dashboardStore.fetchContas()
})
</script>

<style scoped lang="scss">
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1050;
  padding: 1rem;
}

.modal-container {
  background: var(--bs-card-bg, #fff);
  border-radius: 1.25rem;
  width: 100%;
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 16px 40px rgba(0, 0, 0, 0.25);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.75rem 2rem 1rem;
  border-bottom: 1px solid var(--bs-border-color);
  margin-bottom: 0;

  h5 {
    margin: 0;
    font-weight: 600;
  }

  .btn-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
  }
}

.modal-body {
  padding: 1.5rem 2rem 2rem;
}

.accounts-list {
  .accounts-items {
    .account-item {
      display: flex;
      align-items: center;
      padding: 1.25rem 1.5rem;
      border: 1px solid var(--bs-border-color);
      border-radius: 1rem;
      margin-bottom: 1rem;
      
      .account-image {
        width: 50px;
        height: 50px;
        margin-right: 1rem;
        
        img {
          width: 100%;
          height: 100%;
          object-fit: contain;
          border-radius: 0.5rem;
          padding: 4px;
        }
        
        .account-placeholder {
          width: 100%;
          height: 100%;
          background: var(--bs-light);
          border-radius: 0.5rem;
          display: flex;
          align-items: center;
          justify-content: center;
          font-size: 1.5rem;
          color: var(--bs-secondary);
        }
      }
      
      .account-info {
        flex: 1;
        
        h6 {
          margin-bottom: 0.5rem;
          font-weight: 600;
        }
        
        .account-balance {
          display: flex;
          flex-direction: column;
          gap: 0.25rem;
          margin-bottom: 0.5rem;
          
          .balance-item {
            display: flex;
            justify-content: space-between;
            font-size: 0.9rem;
            
            .label {
              color: var(--bs-secondary);
            }
            
            .value {
              font-weight: 600;
            }
          }
        }
        
        .account-meta {
          display: flex;
          gap: 0.5rem;
          flex-wrap: wrap;
        }
      }
      
      .account-actions {
        display: flex;
        gap: 0.5rem;
      }
    }
  }
}

:root[data-bs-theme="dark"] .account-image { background: #fff; border-radius: 0.5rem; }
</style>

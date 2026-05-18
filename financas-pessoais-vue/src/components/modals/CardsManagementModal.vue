<template>
  <div class="modal-overlay" @click="close">
    <div class="modal-container" @click.stop>
      <div class="modal-header">
        <h5>Gerenciar Cartões</h5>
        <button type="button" class="btn-close" @click="close"></button>
      </div>
      
      <div class="modal-body">
        <!-- Botão de Adicionar -->
        <div class="mb-3">
          <button class="btn btn-primary" @click="showAddForm = true">
            <i class="bi bi-plus"></i>
            Adicionar Cartão
          </button>
        </div>

        <!-- Lista de Cartões -->
        <div class="cards-list">
          <div v-if="isLoading" class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Carregando...</span>
            </div>
          </div>
          
          <div v-else-if="cartoes.length === 0" class="text-center py-4">
            <i class="bi bi-credit-card display-1 text-muted"></i>
            <p class="text-muted mt-3">Nenhum cartão cadastrado</p>
          </div>
          
          <div v-else class="cards-items">
            <div
              v-for="cartao in cartoes"
              :key="cartao.id"
              class="card-item"
            >
              <div class="card-image">
                <img
                  v-if="cartao.imagem || cartao.logo_path"
                  :src="cartao.imagem ? cartao.imagem.caminho : cartao.logo_path"
                  :alt="cartao.nome"
                >
                <div v-else class="card-placeholder">
                  <i class="bi bi-credit-card"></i>
                </div>
              </div>
              
              <div class="card-info">
                <h6>{{ cartao.nome }}</h6>
                <div class="card-balance">
                  <span class="balance-item">
                    <span class="label">Fatura:</span>
                    <span class="value text-danger">
                      {{ formatCurrency(cartao.fatura_total) }}
                    </span>
                  </span>
                  <span class="balance-item">
                    <span class="label">Limite:</span>
                    <span class="value text-muted">
                      {{ formatCurrency(cartao.limite_total) }}
                    </span>
                  </span>
                </div>
                <div class="card-meta">
                  <span class="badge" :class="getCardTypeClass(cartao.tipo)">
                    {{ getCardTypeLabel(cartao.tipo) }}
                  </span>
                  <span class="badge bg-info">
                    Fecha dia {{ cartao.data_fechamento }}
                  </span>
                </div>
                <div class="card-usage">
                  <div class="progress">
                    <div 
                      class="progress-bar" 
                      :class="getUsageColor(cartao.fatura_total, cartao.limite_total)"
                      :style="{ width: getUsagePercentage(cartao.fatura_total, cartao.limite_total) + '%' }"
                    ></div>
                  </div>
                  <small class="usage-text">
                    {{ getUsagePercentage(cartao.fatura_total, cartao.limite_total) }}% usado
                  </small>
                </div>
              </div>
              
              <div class="card-actions">
                <button
                  class="btn btn-sm btn-outline-primary"
                  @click="editCard(cartao)"
                >
                  <i class="bi bi-pencil"></i>
                </button>
                <button
                  class="btn btn-sm btn-outline-danger"
                  @click="deleteCard(cartao)"
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

const cartoes = computed(() => dashboardStore.cartoes)
const isLoading = computed(() => dashboardStore.isLoading)

const { formatCurrency } = useUiStore()

const getCardTypeClass = (tipo: string) => {
  const classes = {
    DEBITO: 'bg-success',
    CREDITO: 'bg-primary',
    MULTIPLO: 'bg-warning'
  }
  return classes[tipo] || 'bg-secondary'
}

const getCardTypeLabel = (tipo: string) => {
  const labels = {
    DEBITO: 'Débito',
    CREDITO: 'Crédito',
    MULTIPLO: 'Múltiplo'
  }
  return labels[tipo] || tipo
}

const getUsageColor = (fatura: number, limite: number) => {
  const percentage = (fatura / limite) * 100
  if (percentage >= 100) return 'bg-danger'
  if (percentage >= 70) return 'bg-warning'
  return 'bg-success'
}

const getUsagePercentage = (fatura: number, limite: number) => {
  return Math.min(Math.round((fatura / limite) * 100), 100)
}

const editCard = (cartao: any) => {
  // TODO: Implementar edição de cartão
  console.log('Editar cartão:', cartao)
}

const deleteCard = async (cartao: any) => {
  if (confirm(`Tem certeza que deseja excluir o cartão "${cartao.nome}"?`)) {
    const result = await dashboardStore.deleteCartao(cartao.id)
    if (!result.success) {
      alert(result.message || 'Erro ao excluir cartão')
    }
  }
}

const close = () => {
  emit('close')
}

onMounted(() => {
  dashboardStore.fetchCartoes()
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
  background: white;
  border-radius: 0.75rem;
  width: 100%;
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem 1.5rem 0;
  border-bottom: 1px solid var(--bs-border-color);
  margin-bottom: 1.5rem;
  
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
  padding: 0 1.5rem 1.5rem;
}

.cards-list {
  .cards-items {
    .card-item {
      display: flex;
      align-items: center;
      padding: 1rem;
      border: 1px solid var(--bs-border-color);
      border-radius: 0.5rem;
      margin-bottom: 1rem;
      
      .card-image {
        width: 60px;
        height: 40px;
        margin-right: 1rem;
        
        img {
          width: 100%;
          height: 100%;
          object-fit: contain;
          border-radius: 0.5rem;
          padding: 4px;
        }
        
        .card-placeholder {
          width: 100%;
          height: 100%;
          background: var(--bs-light);
          border-radius: 0.5rem;
          display: flex;
          align-items: center;
          justify-content: center;
          font-size: 1.2rem;
          color: var(--bs-secondary);
        }
      }
      
      .card-info {
        flex: 1;
        
        h6 {
          margin-bottom: 0.5rem;
          font-weight: 600;
        }
        
        .card-balance {
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
        
        .card-meta {
          display: flex;
          gap: 0.5rem;
          flex-wrap: wrap;
          margin-bottom: 0.5rem;
        }
        
        .card-usage {
          .progress {
            height: 6px;
            margin-bottom: 0.25rem;
          }
          
          .usage-text {
            font-size: 0.75rem;
            color: var(--bs-secondary);
          }
        }
      }
      
      .card-actions {
        display: flex;
        gap: 0.5rem;
      }
    }
  }
}

:root[data-bs-theme="dark"] .card-image { background: #fff; border-radius: 0.5rem; }
</style>

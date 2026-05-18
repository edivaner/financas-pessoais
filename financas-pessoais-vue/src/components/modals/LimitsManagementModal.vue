<template>
  <div class="modal-overlay" @click="close">
    <div class="modal-container" @click.stop>
      <div class="modal-header">
        <h5>Gerenciar Limites</h5>
        <button type="button" class="btn-close" @click="close"></button>
      </div>
      
      <div class="modal-body">
        <!-- Botão de Adicionar -->
        <div class="mb-3">
          <button class="btn btn-primary" @click="showAddForm = true">
            <i class="bi bi-plus"></i>
            Adicionar Limite
          </button>
        </div>

        <!-- Lista de Limites -->
        <div class="limits-list">
          <div v-if="isLoading" class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Carregando...</span>
            </div>
          </div>
          
          <div v-else-if="limites.length === 0" class="text-center py-4">
            <i class="bi bi-graph-up display-1 text-muted"></i>
            <p class="text-muted mt-3">Nenhum limite cadastrado</p>
          </div>
          
          <div v-else class="limits-items">
            <div
              v-for="limite in limites"
              :key="limite.id"
              class="limit-item"
            >
              <div class="limit-icon">
                <i :class="limite.categoria?.icone || 'bi bi-tag'" :style="{ color: limite.categoria?.cor || '#6c757d' }"></i>
              </div>
              
              <div class="limit-info">
                <h6>{{ limite.categoria?.nome }}</h6>
                <p class="limit-description">{{ limite.descricao }}</p>
                <div class="limit-meta">
                  <span class="badge" :class="getPeriodicityClass(limite.periodicidade)">
                    {{ getPeriodicityLabel(limite.periodicidade) }}
                  </span>
                  <span class="badge bg-info">
                    Reset: {{ formatDate(limite.ultimo_reset) }}
                  </span>
                </div>
              </div>
              
              <div class="limit-progress">
                <div class="progress">
                  <div
                    class="progress-bar"
                    :class="getLimitColor(limite.valor_gasto_atual, limite.valor_limite)"
                    :style="{ width: getLimitPercentage(limite.valor_gasto_atual, limite.valor_limite) + '%' }"
                  ></div>
                </div>
                <div class="limit-values">
                  <span class="spent">
                    {{ formatCurrency(limite.valor_gasto_atual) }}
                  </span>
                  <span class="limit">
                    de {{ formatCurrency(limite.valor_limite) }}
                  </span>
                </div>
                <div class="limit-percentage">
                  {{ getLimitPercentage(limite.valor_gasto_atual, limite.valor_limite) }}% usado
                </div>
              </div>
              
              <div class="limit-actions">
                <button
                  class="btn btn-sm btn-outline-primary"
                  @click="editLimit(limite)"
                >
                  <i class="bi bi-pencil"></i>
                </button>
                <button
                  class="btn btn-sm btn-outline-warning"
                  @click="resetLimit(limite)"
                >
                  <i class="bi bi-arrow-clockwise"></i>
                </button>
                <button
                  class="btn btn-sm btn-outline-danger"
                  @click="deleteLimit(limite)"
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
import { useBalanceStore } from '@/stores/balance'
import { useUiStore } from '@/stores/ui'

const emit = defineEmits<{
  close: []
}>()

const balanceStore = useBalanceStore()
const showAddForm = ref(false)

const limites = computed(() => balanceStore.limites)
const isLoading = computed(() => balanceStore.isLoading)

const { formatCurrency } = useUiStore()

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('pt-BR')
}

const getPeriodicityClass = (periodicidade: string) => {
  const classes = {
    MENSAL: 'bg-primary',
    SEMANAL: 'bg-info',
    DIARIO: 'bg-success',
    ANUAL: 'bg-warning'
  }
  return classes[periodicidade] || 'bg-secondary'
}

const getPeriodicityLabel = (periodicidade: string) => {
  const labels = {
    MENSAL: 'Mensal',
    SEMANAL: 'Semanal',
    DIARIO: 'Diário',
    ANUAL: 'Anual'
  }
  return labels[periodicidade] || periodicidade
}

const getLimitColor = (gasto: number, limite: number) => {
  const percentage = (gasto / limite) * 100
  if (percentage >= 100) return 'bg-danger'
  if (percentage >= 70) return 'bg-warning'
  return 'bg-success'
}

const getLimitPercentage = (gasto: number, limite: number) => {
  return Math.min(Math.round((gasto / limite) * 100), 100)
}

const editLimit = (limite: any) => {
  // TODO: Implementar edição de limite
  console.log('Editar limite:', limite)
}

const resetLimit = async (limite: any) => {
  if (confirm(`Tem certeza que deseja resetar o limite de "${limite.categoria?.nome}"?`)) {
    // TODO: Implementar reset de limite
    console.log('Resetar limite:', limite)
  }
}

const deleteLimit = async (limite: any) => {
  if (confirm(`Tem certeza que deseja excluir o limite de "${limite.categoria?.nome}"?`)) {
    // TODO: Implementar exclusão de limite
    console.log('Excluir limite:', limite)
  }
}

const close = () => {
  emit('close')
}

onMounted(() => {
  balanceStore.fetchLimites()
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
  max-width: 700px;
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

.limits-list {
  .limits-items {
    .limit-item {
      display: flex;
      align-items: center;
      padding: 1.5rem;
      border: 1px solid var(--bs-border-color);
      border-radius: 0.75rem;
      margin-bottom: 1rem;
      background: white;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      
      .limit-icon {
        width: 60px;
        height: 60px;
        background: var(--bs-light);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 1.5rem;
        font-size: 1.8rem;
      }
      
      .limit-info {
        flex: 1;
        margin-right: 1.5rem;
        
        h6 {
          margin-bottom: 0.5rem;
          font-weight: 600;
          font-size: 1.1rem;
        }
        
        .limit-description {
          margin-bottom: 0.75rem;
          color: var(--bs-secondary);
          font-size: 0.9rem;
        }
        
        .limit-meta {
          display: flex;
          gap: 0.5rem;
          flex-wrap: wrap;
        }
      }
      
      .limit-progress {
        flex: 1;
        margin-right: 1.5rem;
        
        .progress {
          height: 10px;
          margin-bottom: 0.75rem;
          border-radius: 5px;
        }
        
        .limit-values {
          display: flex;
          justify-content: space-between;
          margin-bottom: 0.5rem;
          font-size: 0.9rem;
          
          .spent {
            font-weight: 600;
            color: var(--bs-primary);
          }
          
          .limit {
            color: var(--bs-secondary);
          }
        }
        
        .limit-percentage {
          font-size: 0.8rem;
          color: var(--bs-secondary);
          text-align: center;
        }
      }
      
      .limit-actions {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
      }
    }
  }
}

@media (max-width: 768px) {
  .limit-item {
    flex-direction: column;
    align-items: flex-start !important;
    
    .limit-info {
      margin-right: 0 !important;
      margin-bottom: 1rem;
    }
    
    .limit-progress {
      margin-right: 0 !important;
      margin-bottom: 1rem;
      width: 100%;
    }
    
    .limit-actions {
      flex-direction: row !important;
      width: 100%;
      justify-content: center;
    }
  }
}
</style>


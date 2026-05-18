<template>
  <div class="modal-overlay" @click="close">
    <div class="modal-container" @click.stop>
      <div class="modal-header">
        <h5>Gerenciar Categorias</h5>
        <button type="button" class="btn-close" @click="close"></button>
      </div>
      
      <div class="modal-body">
        <!-- Abas -->
        <ul class="nav nav-tabs mb-3" role="tablist">
          <li class="nav-item" role="presentation">
            <button 
              class="nav-link" 
              :class="{ active: activeTab === 'categorias' }"
              @click="activeTab = 'categorias'"
            >
              Categorias
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button 
              class="nav-link" 
              :class="{ active: activeTab === 'subcategorias' }"
              @click="activeTab = 'subcategorias'"
            >
              Subcategorias
            </button>
          </li>
        </ul>

        <!-- Conteúdo das Abas -->
        <div class="tab-content">
          <!-- Categorias -->
          <div v-if="activeTab === 'categorias'" class="tab-pane">
            <div class="mb-3">
              <button class="btn btn-primary" @click="showAddCategoryForm = true">
                <i class="bi bi-plus"></i>
                Adicionar Categoria
              </button>
            </div>

            <div class="categories-list">
              <div v-if="isLoading" class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                  <span class="visually-hidden">Carregando...</span>
                </div>
              </div>
              
              <div v-else-if="categorias.length === 0" class="text-center py-4">
                <i class="bi bi-tags display-1 text-muted"></i>
                <p class="text-muted mt-3">Nenhuma categoria cadastrada</p>
              </div>
              
              <div v-else class="categories-items">
                <div
                  v-for="categoria in categorias"
                  :key="categoria.id"
                  class="category-item"
                >
                  <div class="category-icon">
                    <i :class="categoria.icone" :style="{ color: categoria.cor }"></i>
                  </div>
                  
                  <div class="category-info">
                    <h6>{{ categoria.nome }}</h6>
                    <div class="category-meta">
                      <span class="badge" :class="getCategoryTypeClass(categoria.tipo)">
                        {{ getCategoryTypeLabel(categoria.tipo) }}
                      </span>
                      <span v-if="categoria.essencial" class="badge bg-warning">Essencial</span>
                      <span v-if="!categoria.ativo" class="badge bg-secondary">Inativa</span>
                    </div>
                  </div>
                  
                  <div class="category-actions">
                    <button
                      class="btn btn-sm btn-outline-primary"
                      @click="editCategory(categoria)"
                    >
                      <i class="bi bi-pencil"></i>
                    </button>
                    <button
                      v-if="!categoria.essencial"
                      class="btn btn-sm btn-outline-danger"
                      @click="deleteCategory(categoria)"
                    >
                      <i class="bi bi-trash"></i>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Subcategorias -->
          <div v-if="activeTab === 'subcategorias'" class="tab-pane">
            <div class="mb-3">
              <button class="btn btn-primary" @click="showAddSubcategoryForm = true">
                <i class="bi bi-plus"></i>
                Adicionar Subcategoria
              </button>
            </div>

            <div class="subcategories-list">
              <div v-if="isLoading" class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                  <span class="visually-hidden">Carregando...</span>
                </div>
              </div>
              
              <div v-else-if="subcategorias.length === 0" class="text-center py-4">
                <i class="bi bi-tag display-1 text-muted"></i>
                <p class="text-muted mt-3">Nenhuma subcategoria cadastrada</p>
              </div>
              
              <div v-else class="subcategories-items">
                <div
                  v-for="subcategoria in subcategorias"
                  :key="subcategoria.id"
                  class="subcategory-item"
                >
                  <div class="subcategory-icon">
                    <i :class="subcategoria.icone" :style="{ color: subcategoria.cor }"></i>
                  </div>
                  
                  <div class="subcategory-info">
                    <h6>{{ subcategoria.nome }}</h6>
                    <div class="subcategory-meta">
                      <span class="badge bg-info">
                        {{ subcategoria.categoria?.nome }}
                      </span>
                      <span v-if="subcategoria.essencial" class="badge bg-warning">Essencial</span>
                      <span v-if="!subcategoria.ativo" class="badge bg-secondary">Inativa</span>
                    </div>
                  </div>
                  
                  <div class="subcategory-actions">
                    <button
                      class="btn btn-sm btn-outline-primary"
                      @click="editSubcategory(subcategoria)"
                    >
                      <i class="bi bi-pencil"></i>
                    </button>
                    <button
                      v-if="!subcategoria.essencial"
                      class="btn btn-sm btn-outline-danger"
                      @click="deleteSubcategory(subcategoria)"
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
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useTransactionStore } from '@/stores/transaction'

const emit = defineEmits<{
  close: []
}>()

const transactionStore = useTransactionStore()
const activeTab = ref('categorias')
const showAddCategoryForm = ref(false)
const showAddSubcategoryForm = ref(false)

const categorias = computed(() => transactionStore.categorias)
const subcategorias = computed(() => transactionStore.subcategorias)
const isLoading = computed(() => transactionStore.isLoading)

const getCategoryTypeClass = (tipo: string) => {
  const classes = {
    DESPESA: 'bg-danger',
    RECEITA: 'bg-success',
    INVESTIMENTO: 'bg-primary'
  }
  return classes[tipo] || 'bg-secondary'
}

const getCategoryTypeLabel = (tipo: string) => {
  const labels = {
    DESPESA: 'Despesa',
    RECEITA: 'Receita',
    INVESTIMENTO: 'Investimento'
  }
  return labels[tipo] || tipo
}

const editCategory = (categoria: any) => {
  // TODO: Implementar edição de categoria
  console.log('Editar categoria:', categoria)
}

const deleteCategory = async (categoria: any) => {
  if (confirm(`Tem certeza que deseja excluir a categoria "${categoria.nome}"?`)) {
    const result = await transactionStore.deleteCategoria(categoria.id)
    if (!result.success) {
      alert(result.message || 'Erro ao excluir categoria')
    }
  }
}

const editSubcategory = (subcategoria: any) => {
  // TODO: Implementar edição de subcategoria
  console.log('Editar subcategoria:', subcategoria)
}

const deleteSubcategory = async (subcategoria: any) => {
  if (confirm(`Tem certeza que deseja excluir a subcategoria "${subcategoria.nome}"?`)) {
    const result = await transactionStore.deleteSubcategoria(subcategoria.id)
    if (!result.success) {
      alert(result.message || 'Erro ao excluir subcategoria')
    }
  }
}

const close = () => {
  emit('close')
}

onMounted(() => {
  Promise.all([
    transactionStore.fetchCategorias(),
    transactionStore.fetchSubcategorias()
  ])
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

.nav-tabs {
  border-bottom: 1px solid var(--bs-border-color);
  
  .nav-link {
    border: none;
    color: var(--bs-secondary);
    
    &.active {
      color: var(--bs-primary);
      border-bottom: 2px solid var(--bs-primary);
    }
  }
}

.categories-list, .subcategories-list {
  .categories-items, .subcategories-items {
    .category-item, .subcategory-item {
      display: flex;
      align-items: center;
      padding: 1rem;
      border: 1px solid var(--bs-border-color);
      border-radius: 0.5rem;
      margin-bottom: 1rem;
      
      .category-icon, .subcategory-icon {
        width: 50px;
        height: 50px;
        background: var(--bs-light);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 1rem;
        font-size: 1.5rem;
      }
      
      .category-info, .subcategory-info {
        flex: 1;
        
        h6 {
          margin-bottom: 0.5rem;
          font-weight: 600;
        }
        
        .category-meta, .subcategory-meta {
          display: flex;
          gap: 0.5rem;
          flex-wrap: wrap;
        }
      }
      
      .category-actions, .subcategory-actions {
        display: flex;
        gap: 0.5rem;
      }
    }
  }
}
</style>

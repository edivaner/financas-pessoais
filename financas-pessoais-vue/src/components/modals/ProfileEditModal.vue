<template>
  <div class="modal-overlay" @click="close">
    <div class="modal-container" @click.stop>
      <div class="modal-header">
        <h5>Editar Perfil</h5>
        <button type="button" class="btn-close" @click="close"></button>
      </div>
      
      <div class="modal-body">
        <form @submit.prevent="submitForm">
          <!-- Foto do Perfil -->
          <div class="mb-3 text-center">
            <div class="profile-image-container">
              <img 
                v-if="form.imagem_url" 
                :src="form.imagem_url" 
                :alt="form.nome"
                class="profile-image"
              >
              <div v-else class="profile-image-placeholder">
                <i class="bi bi-person"></i>
              </div>
              <button 
                type="button"
                class="btn btn-sm btn-outline-primary image-edit-btn"
                @click="selectImage"
              >
                <i class="bi bi-camera"></i>
              </button>
            </div>
            <input 
              type="file" 
              ref="imageInput" 
              @change="handleImageChange" 
              accept="image/*" 
              style="display: none"
            >
          </div>

          <!-- Nome -->
          <div class="mb-3">
            <label for="nome" class="form-label">Nome *</label>
            <input
              type="text"
              class="form-control"
              id="nome"
              v-model="form.nome"
              required
              placeholder="Seu nome"
            >
          </div>

          <!-- Sobrenome -->
          <div class="mb-3">
            <label for="sobrenome" class="form-label">Sobrenome *</label>
            <input
              type="text"
              class="form-control"
              id="sobrenome"
              v-model="form.sobrenome"
              required
              placeholder="Seu sobrenome"
            >
          </div>

          <!-- Email -->
          <div class="mb-3">
            <label for="email" class="form-label">Email *</label>
            <input
              type="email"
              class="form-control"
              id="email"
              v-model="form.email"
              required
              placeholder="seu@email.com"
            >
          </div>

          <!-- Telefone -->
          <div class="mb-3">
            <label for="telefone" class="form-label">Telefone</label>
            <input
              type="tel"
              class="form-control"
              id="telefone"
              v-model="form.telefone"
              placeholder="(11) 99999-9999"
            >
          </div>

          <!-- Profissão -->
          <div class="mb-3">
            <label for="profissao" class="form-label">Profissão</label>
            <input
              type="text"
              class="form-control"
              id="profissao"
              v-model="form.profissao"
              placeholder="Sua profissão"
            >
          </div>

          <!-- Botões -->
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="close">
              Cancelar
            </button>
            <button type="submit" class="btn btn-primary" :disabled="isLoading">
              <span v-if="isLoading" class="spinner-border spinner-border-sm me-2"></span>
              {{ isLoading ? 'Salvando...' : 'Salvar' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'

const props = defineProps<{
  user: any
}>()

const emit = defineEmits<{
  close: []
  updated: []
}>()

const authStore = useAuthStore()
const imageInput = ref<HTMLInputElement>()

const isLoading = ref(false)

const form = ref({
  nome: '',
  sobrenome: '',
  email: '',
  telefone: '',
  profissao: '',
  imagem_url: ''
})

const selectImage = () => {
  imageInput.value?.click()
}

const handleImageChange = (event: Event) => {
  const file = (event.target as HTMLInputElement).files?.[0]
  if (file) {
    const reader = new FileReader()
    reader.onload = (e) => {
      form.value.imagem_url = e.target?.result as string
    }
    reader.readAsDataURL(file)
  }
}

const submitForm = async () => {
  try {
    isLoading.value = true
    
    // TODO: Implementar atualização do perfil
    console.log('Atualizando perfil:', form.value)
    
    // Simular delay
    await new Promise(resolve => setTimeout(resolve, 1000))
    
    emit('updated')
  } catch (error) {
    console.error('Erro ao atualizar perfil:', error)
    alert('Erro ao atualizar perfil')
  } finally {
    isLoading.value = false
  }
}

const close = () => {
  emit('close')
}

onMounted(() => {
  if (props.user) {
    form.value = {
      nome: props.user.nome || '',
      sobrenome: props.user.sobrenome || '',
      email: props.user.email || '',
      telefone: props.user.telefone || '',
      profissao: props.user.profissao || '',
      imagem_url: props.user.imagem?.caminho || ''
    }
  }
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
  max-width: 500px;
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

.profile-image-container {
  position: relative;
  display: inline-block;
  margin-bottom: 1rem;
  
  .profile-image {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid var(--bs-light);
  }
  
  .profile-image-placeholder {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background: var(--bs-light);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    color: var(--bs-secondary);
    border: 4px solid var(--bs-light);
  }
  
  .image-edit-btn {
    position: absolute;
    bottom: 0;
    right: 0;
    width: 35px;
    height: 35px;
    border-radius: 50%;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
  }
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  padding: 1rem 1.5rem;
  border-top: 1px solid var(--bs-border-color);
  margin: 1.5rem -1.5rem 0;
}
</style>

<template>
  <div class="modal-overlay" @click="close">
    <div class="modal-container" @click.stop>
      <div class="modal-header">
        <h5>Alterar Senha</h5>
        <button type="button" class="btn-close" @click="close"></button>
      </div>
      
      <div class="modal-body">
        <form @submit.prevent="submitForm">
          <!-- Senha Atual -->
          <div class="mb-3">
            <label for="current_password" class="form-label">Senha Atual *</label>
            <div class="input-group">
              <input
                :type="showCurrentPassword ? 'text' : 'password'"
                class="form-control"
                id="current_password"
                v-model="form.current_password"
                required
                placeholder="Digite sua senha atual"
              >
              <button
                type="button"
                class="btn btn-outline-secondary"
                @click="showCurrentPassword = !showCurrentPassword"
              >
                <i :class="showCurrentPassword ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
              </button>
            </div>
          </div>

          <!-- Nova Senha -->
          <div class="mb-3">
            <label for="new_password" class="form-label">Nova Senha *</label>
            <div class="input-group">
              <input
                :type="showNewPassword ? 'text' : 'password'"
                class="form-control"
                id="new_password"
                v-model="form.new_password"
                required
                placeholder="Digite sua nova senha"
                minlength="6"
              >
              <button
                type="button"
                class="btn btn-outline-secondary"
                @click="showNewPassword = !showNewPassword"
              >
                <i :class="showNewPassword ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
              </button>
            </div>
            <div class="form-text">
              A senha deve ter pelo menos 6 caracteres
            </div>
          </div>

          <!-- Confirmar Nova Senha -->
          <div class="mb-3">
            <label for="confirm_password" class="form-label">Confirmar Nova Senha *</label>
            <div class="input-group">
              <input
                :type="showConfirmPassword ? 'text' : 'password'"
                class="form-control"
                id="confirm_password"
                v-model="form.confirm_password"
                required
                placeholder="Confirme sua nova senha"
                minlength="6"
              >
              <button
                type="button"
                class="btn btn-outline-secondary"
                @click="showConfirmPassword = !showConfirmPassword"
              >
                <i :class="showConfirmPassword ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
              </button>
            </div>
            <div v-if="form.confirm_password && form.new_password !== form.confirm_password" class="form-text text-danger">
              As senhas não coincidem
            </div>
          </div>

          <!-- Botões -->
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="close">
              Cancelar
            </button>
            <button 
              type="submit" 
              class="btn btn-primary" 
              :disabled="isLoading || !isFormValid"
            >
              <span v-if="isLoading" class="spinner-border spinner-border-sm me-2"></span>
              {{ isLoading ? 'Alterando...' : 'Alterar Senha' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'

const emit = defineEmits<{
  close: []
}>()

const isLoading = ref(false)
const showCurrentPassword = ref(false)
const showNewPassword = ref(false)
const showConfirmPassword = ref(false)

const form = ref({
  current_password: '',
  new_password: '',
  confirm_password: ''
})

const isFormValid = computed(() => {
  return form.value.current_password &&
         form.value.new_password &&
         form.value.confirm_password &&
         form.value.new_password === form.value.confirm_password &&
         form.value.new_password.length >= 6
})

const submitForm = async () => {
  try {
    isLoading.value = true
    
    // TODO: Implementar alteração de senha
    console.log('Alterando senha...')
    
    // Simular delay
    await new Promise(resolve => setTimeout(resolve, 1000))
    
    alert('Senha alterada com sucesso!')
    close()
  } catch (error) {
    console.error('Erro ao alterar senha:', error)
    alert('Erro ao alterar senha')
  } finally {
    isLoading.value = false
  }
}

const close = () => {
  emit('close')
}
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

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  padding: 1rem 1.5rem;
  border-top: 1px solid var(--bs-border-color);
  margin: 1.5rem -1.5rem 0;
}
</style>

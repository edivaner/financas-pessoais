<!-- resources/js/pages/auth/Register.vue -->
<template>
  <div class="auth-page">
    <div class="auth-card">
      <div class="text-center mb-4">
        <i class="bi bi-currency-dollar text-success" style="font-size:3rem"></i>
        <h4 class="fw-bold mt-2">Criar conta</h4>
        <p class="text-secondary">Comece a controlar suas finanças</p>
      </div>

      <form @submit.prevent="submit">
        <div class="row g-2 mb-3">
          <div class="col-6">
            <label class="form-label">Nome *</label>
            <input v-model="form.nome" class="form-control" required placeholder="João" />
          </div>
          <div class="col-6">
            <label class="form-label">Sobrenome</label>
            <input v-model="form.sobrenome" class="form-control" placeholder="Silva" />
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label">E-mail *</label>
          <input v-model="form.email" type="email" class="form-control" required placeholder="seu@email.com" />
        </div>
        <div class="mb-3">
          <label class="form-label">Senha *</label>
          <div class="input-group">
            <input v-model="form.password" :type="showPassword ? 'text' : 'password'" class="form-control" required placeholder="Mínimo 8 caracteres" />
            <button type="button" class="btn btn-outline-secondary" @click="showPassword = !showPassword" tabindex="-1">
              <i :class="showPassword ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
            </button>
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label">Confirmar senha *</label>
          <div class="input-group">
            <input v-model="form.password_confirmation" :type="showPasswordConfirm ? 'text' : 'password'" class="form-control" required placeholder="Repita a senha" />
            <button type="button" class="btn btn-outline-secondary" @click="showPasswordConfirm = !showPasswordConfirm" tabindex="-1">
              <i :class="showPasswordConfirm ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
            </button>
          </div>
        </div>

        <div v-if="error" class="alert alert-danger py-2 mb-3">{{ error }}</div>

        <button type="submit" class="btn btn-success w-100" :disabled="loading">
          <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
          Criar conta
        </button>
      </form>

      <div class="text-center mt-3">
        <small class="text-secondary">
          Já tem conta?
          <router-link to="/login" class="text-success fw-semibold">Entrar</router-link>
        </small>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const store   = useAuthStore()
const router  = useRouter()
const error   = ref('')
const loading = ref(false)

const form = reactive({
  nome: '', sobrenome: '', email: '', password: '', password_confirmation: ''
})
const showPassword = ref(false)
const showPasswordConfirm = ref(false)

const submit = async () => {
  error.value = ''
  if (form.password !== form.password_confirmation) {
    error.value = 'As senhas não conferem'
    return
  }
  loading.value = true
  const result = await store.register(form)
  loading.value = false
  if (result.success) {
    router.push('/')
  } else {
    error.value = result.message ?? 'Erro ao criar conta'
  }
}
</script>

<style scoped lang="scss">
.auth-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--bs-body-bg);
  padding: 1rem;
}

.auth-card {
  width: 100%;
  max-width: 420px;
  background: var(--bs-card-bg, #fff);
  border-radius: 1.25rem;
  border: 1px solid var(--bs-border-color);
  padding: 2rem;
}
</style>

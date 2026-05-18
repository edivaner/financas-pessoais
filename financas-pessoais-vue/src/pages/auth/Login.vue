<!-- resources/js/pages/auth/Login.vue -->
<template>
  <div class="auth-page">
    <div class="auth-card">
      <div class="text-center mb-4">
        <i class="bi bi-currency-dollar text-success" style="font-size:3rem"></i>
        <h4 class="fw-bold mt-2">Finanças Pessoais</h4>
        <p class="text-secondary">Entre na sua conta</p>
      </div>

      <form @submit.prevent="submit">
        <div class="mb-3">
          <label class="form-label">E-mail</label>
          <input v-model="form.email" type="email" class="form-control" required autocomplete="email" placeholder="seu@email.com" />
        </div>
        <div class="mb-3">
          <label class="form-label">Senha</label>
          <input v-model="form.password" type="password" class="form-control" required autocomplete="current-password" placeholder="••••••••" />
        </div>

        <div v-if="error" class="alert alert-danger py-2 mb-3">{{ error }}</div>

        <button type="submit" class="btn btn-success w-100" :disabled="loading">
          <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
          Entrar
        </button>
      </form>

      <div class="text-center mt-3">
        <small class="text-secondary">
          Não tem conta?
          <router-link to="/register" class="text-success fw-semibold">Cadastre-se</router-link>
        </small>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const store  = useAuthStore()
const router = useRouter()
const error  = ref('')
const loading = ref(false)

const form = reactive({ email: '', password: '' })

const submit = async () => {
  error.value = ''
  loading.value = true
  const result = await store.login(form.email, form.password)
  loading.value = false
  if (result.success) {
    router.push('/')
  } else {
    error.value = result.message ?? 'Credenciais inválidas'
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
  max-width: 400px;
  background: var(--bs-card-bg, #fff);
  border-radius: 1.25rem;
  border: 1px solid var(--bs-border-color);
  padding: 2rem;
}
</style>

<template>
  <div class="min-vh-100 d-flex align-items-center justify-content-center bg-light">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
          <div class="card shadow">
            <div class="card-body p-4">
              <div class="text-center mb-4">
                <h2 class="fw-bold text-primary">Verificação</h2>
                <p class="text-muted">
                  Digite o código enviado para<br>
                  <strong>{{ email }}</strong>
                </p>
              </div>

              <form @submit.prevent="handleVerify">
                <div class="mb-3">
                  <label for="code" class="form-label">Código de Verificação</label>
                  <input
                    type="text"
                    class="form-control text-center"
                    id="code"
                    v-model="form.code"
                    maxlength="6"
                    required
                  />
                </div>

                <button
                  type="submit"
                  class="btn btn-primary w-100 mb-3"
                  :disabled="loading"
                >
                  <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                  Verificar
                </button>
              </form>

              <div class="text-center">
                <p class="mb-2">
                  Não recebeu o código?
                  <button
                    type="button"
                    class="btn btn-link p-0"
                    @click="resendCode"
                    :disabled="resendLoading || resendCooldown > 0"
                  >
                    Reenviar
                    <span v-if="resendCooldown > 0">({{ resendCooldown }}s)</span>
                  </button>
                </p>
                <router-link to="/login" class="text-muted text-decoration-none">
                  ← Voltar ao login
                </router-link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import axios from 'axios';

const router = useRouter();
const route = useRoute();
const loading = ref(false);
const resendLoading = ref(false);
const resendCooldown = ref(0);

const email = ref(route.query.email as string);
const purpose = ref(route.query.purpose as string);

const form = ref({
  code: '',
});

let cooldownInterval: number | null = null;

const handleVerify = async () => {
  loading.value = true;
  
  try {
    const response = await axios.post('/api/auth/otp/verify', {
      email: email.value,
      code: form.value.code,
      purpose: purpose.value,
    });
    
    if (response.data.success) {
      // Redirecionar para dashboard
      router.push('/');
    } else {
      alert(response.data.message || 'Código inválido');
    }
  } catch (error: any) {
    alert(error.response?.data?.message || 'Erro ao verificar código');
  } finally {
    loading.value = false;
  }
};

const resendCode = async () => {
  resendLoading.value = true;
  
  try {
    const response = await axios.post('/api/auth/otp/send', {
      email: email.value,
      purpose: purpose.value,
    });
    
    if (response.data.success) {
      startCooldown();
      alert('Código reenviado com sucesso!');
    } else {
      alert(response.data.message || 'Erro ao reenviar código');
    }
  } catch (error: any) {
    alert(error.response?.data?.message || 'Erro ao reenviar código');
  } finally {
    resendLoading.value = false;
  }
};

const startCooldown = () => {
  resendCooldown.value = 60;
  cooldownInterval = setInterval(() => {
    resendCooldown.value--;
    if (resendCooldown.value <= 0) {
      clearInterval(cooldownInterval!);
      cooldownInterval = null;
    }
  }, 1000);
};

onMounted(() => {
  if (!email.value || !purpose.value) {
    router.push('/login');
  }
});

onUnmounted(() => {
  if (cooldownInterval) {
    clearInterval(cooldownInterval);
  }
});
</script>

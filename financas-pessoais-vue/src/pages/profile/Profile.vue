<!-- resources/js/pages/profile/Profile.vue -->
<template>
  <AppLayout>
    <div class="container-fluid px-3 py-3">
      <!-- User header -->
      <div class="d-flex flex-column align-items-center py-4">
        <div class="profile-avatar mb-2 position-relative" style="cursor:pointer" @click="fileInput?.click()">
          <img v-if="user?.imagem" :src="user.imagem.caminho" :alt="user?.nome" />
          <i v-else class="bi bi-person-circle text-secondary" style="font-size:3.5rem"></i>
          <div class="avatar-overlay">
            <i class="bi bi-camera-fill"></i>
          </div>
          <div v-if="avatarLoading" class="avatar-spinner">
            <div class="spinner-border spinner-border-sm text-light"></div>
          </div>
        </div>
        <input ref="fileInput" type="file" accept=".jpg,.jpeg,.png" class="d-none" @change="onAvatarChange" />
        <small class="text-muted" style="font-size:0.72rem">JPG ou PNG · máx. 10 MB</small>
        <p v-if="avatarError" class="text-danger small mb-1">{{ avatarError }}</p>
        <template v-if="!editingName">
          <h5 class="mb-0 fw-bold">{{ user?.nome }} {{ user?.sobrenome }}</h5>
          <small class="text-secondary">{{ user?.email }}</small>
          <button class="btn btn-link btn-sm p-0 mt-1 text-secondary" @click="openEditName">
            <i class="bi bi-pencil-fill me-1"></i>Editar nome
          </button>
        </template>
        <template v-else>
          <div class="d-flex flex-column align-items-center gap-2 mt-1" style="width:100%;max-width:280px">
            <input v-model="nameForm.nome" class="form-control form-control-sm text-center" placeholder="Nome" maxlength="100" required />
            <input v-model="nameForm.sobrenome" class="form-control form-control-sm text-center" placeholder="Sobrenome" maxlength="100" />
            <p v-if="nameError" class="text-danger small mb-0">{{ nameError }}</p>
            <div class="d-flex gap-2">
              <button class="btn btn-primary btn-sm" :disabled="nameLoading" @click="saveName">
                <span v-if="nameLoading" class="spinner-border spinner-border-sm me-1"></span>Salvar
              </button>
              <button class="btn btn-outline-secondary btn-sm" @click="editingName = false">Cancelar</button>
            </div>
          </div>
        </template>
      </div>

      <!-- Navigation links -->
      <div class="profile-menu">
        <router-link v-for="item in menuItems" :key="item.to" :to="item.to" class="profile-link">
          <div class="d-flex align-items-center gap-3">
            <div class="link-icon" :style="{ background: item.color + '22', color: item.color }">
              <i :class="item.icon"></i>
            </div>
            <span>{{ item.label }}</span>
          </div>
          <i class="bi bi-chevron-right text-secondary"></i>
        </router-link>
      </div>

      <!-- Dark mode + currency -->
      <div class="card-modern mt-3 p-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <span><i class="bi bi-moon-stars me-2"></i> Modo escuro</span>
          <div class="form-check form-switch mb-0">
            <input class="form-check-input" type="checkbox" role="switch" :checked="uiStore.darkMode" @change="uiStore.toggleDarkMode()" style="cursor:pointer; width:2.5rem; height:1.25rem" />
          </div>
        </div>
        <div class="d-flex justify-content-between align-items-center">
          <span><i class="bi bi-currency-exchange me-2"></i> Moeda</span>
          <select class="form-select" :value="uiStore.currency" style="width:auto" @change="onCurrencyChange($event)">
            <option value="BRL">R$ (Real)</option>
            <option value="USD">$ (Dólar)</option>
          </select>
        </div>
      </div>

      <!-- Logout -->
      <button class="btn btn-outline-danger w-100 mt-3" @click="handleLogout">
        <i class="bi bi-box-arrow-right me-2"></i> Sair da conta
      </button>

      <!-- Zona de perigo (colapsável) -->
      <div class="mt-4 mb-2">
        <button class="btn btn-link btn-sm text-danger p-0" @click="showDangerZone = !showDangerZone">
          <i class="bi bi-chevron-down me-1" :style="showDangerZone ? 'transform:rotate(180deg)' : ''" style="transition:.2s"></i>
          Zona de perigo
        </button>
      </div>
      <div v-if="showDangerZone" class="card-modern p-3" style="border-color: #ef444455 !important; border: 1px solid;">
        <p class="fw-semibold text-danger mb-1"><i class="bi bi-exclamation-triangle me-2"></i>Zona de Perigo</p>
        <p class="text-secondary small mb-3">Apaga todos os seus lançamentos, contas, cartões, categorias, subcategorias e limites. Irreversível.</p>
        <button class="btn btn-outline-danger btn-sm w-100" @click="showResetModal = true">
          Zerar minha conta
        </button>
      </div>
    </div>

    <!-- Reset confirmation modal -->
    <Teleport to="body">
      <div v-if="showResetModal" class="reset-overlay" @click.self="showResetModal = false">
        <div class="reset-sheet">
          <div class="sheet-handle"></div>

          <div class="text-center mb-4">
            <div class="danger-icon mb-3">
              <i class="bi bi-trash3-fill"></i>
            </div>
            <h5 class="fw-bold mb-1">Zerar minha conta</h5>
            <p class="text-secondary small mb-0">
              Essa ação apagará <strong>permanentemente</strong> todos os seus dados:
            </p>
          </div>

          <ul class="reset-list mb-4">
            <li><i class="bi bi-x-circle-fill text-danger me-2"></i>Todos os lançamentos</li>
            <li><i class="bi bi-x-circle-fill text-danger me-2"></i>Todas as contas e cartões</li>
            <li><i class="bi bi-x-circle-fill text-danger me-2"></i>Todas as categorias e subcategorias</li>
            <li><i class="bi bi-x-circle-fill text-danger me-2"></i>Todos os limites</li>
          </ul>

          <p class="text-secondary small text-center mb-3">
            Para confirmar, digite <strong>ZERAR</strong> abaixo:
          </p>
          <input
            v-model="resetConfirmText"
            class="form-control text-center mb-3"
            placeholder="ZERAR"
            @keydown.enter="doReset"
          />

          <p v-if="resetError" class="text-danger small text-center mb-2">{{ resetError }}</p>

          <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary flex-fill" @click="showResetModal = false">Cancelar</button>
            <button
              class="btn btn-danger flex-fill"
              :disabled="resetConfirmText !== 'ZERAR' || resetLoading"
              @click="doReset"
            >
              <span v-if="resetLoading" class="spinner-border spinner-border-sm me-1"></span>
              Confirmar
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </AppLayout>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import AppLayout from '@/components/layouts/AppLayout.vue'
import { useAuthStore } from '@/stores/auth'
import { useUiStore } from '@/stores/ui'

const authStore    = useAuthStore()
const uiStore      = useUiStore()
const router       = useRouter()
const user         = computed(() => authStore.user)
const onCurrencyChange = (e: Event) => uiStore.setCurrency((e.target as HTMLSelectElement).value)
const fileInput    = ref<HTMLInputElement | null>(null)
const avatarLoading = ref(false)
const avatarError   = ref('')

const onAvatarChange = async (e: Event) => {
  const file = (e.target as HTMLInputElement).files?.[0]
  if (!file) return
  avatarError.value = ''
  const validTypes = ['image/jpeg', 'image/jpg', 'image/png']
  if (!validTypes.includes(file.type)) {
    avatarError.value = 'Use apenas arquivos JPG ou PNG.'
    return
  }
  if (file.size > 10 * 1024 * 1024) {
    avatarError.value = 'Arquivo deve ter no máximo 10 MB.'
    return
  }
  try {
    avatarLoading.value = true
    await authStore.uploadAvatar(file)
  } catch (err: any) {
const errors = err?.response?.data?.errors
    if (errors) {
      const msgs = Object.values(errors).flat() as string[]
      avatarError.value = msgs[0] ?? 'Erro ao enviar foto.'
    } else if (err?.response?.data?.message) {
      avatarError.value = err.response.data.message
    } else {
      avatarError.value = 'Erro ao enviar foto.'
    }
  } finally {
    avatarLoading.value = false
    if (fileInput.value) fileInput.value.value = ''
  }
}

const editingName  = ref(false)
const nameLoading  = ref(false)
const nameError    = ref('')
const nameForm     = ref({ nome: '', sobrenome: '' })

const openEditName = () => {
  nameForm.value = { nome: user.value?.nome ?? '', sobrenome: user.value?.sobrenome ?? '' }
  nameError.value = ''
  editingName.value = true
}

const saveName = async () => {
  if (!nameForm.value.nome.trim()) { nameError.value = 'Nome é obrigatório.'; return }
  try {
    nameLoading.value = true
    nameError.value = ''
    await authStore.updateUser(nameForm.value)
    editingName.value = false
  } catch (err: any) {
    const errors = err?.response?.data?.errors
    if (errors) {
      nameError.value = (Object.values(errors).flat() as string[])[0]
    } else {
      nameError.value = err?.response?.data?.message ?? 'Erro ao salvar.'
    }
  } finally {
    nameLoading.value = false
  }
}

const menuItems = [
  { to: '/profile/accounts',      icon: 'bi bi-bank2',              color: '#3b82f6', label: 'Minhas Contas' },
  { to: '/profile/cards',         icon: 'bi bi-credit-card',        color: '#8b5cf6', label: 'Meus Cartões' },
  { to: '/profile/categories',    icon: 'bi bi-tag',                color: '#f97316', label: 'Categorias' },
  { to: '/profile/subcategories', icon: 'bi bi-tags',               color: '#ec4899', label: 'Subcategorias' },
  { to: '/profile/limits',        icon: 'bi bi-exclamation-circle', color: '#ef4444', label: 'Meus Limites' },
]

const handleLogout = async () => {
  await authStore.logout()
  window.location.href = '/login'
}

const showDangerZone    = ref(false)
const showResetModal    = ref(false)
const resetConfirmText  = ref('')
const resetLoading      = ref(false)
const resetError        = ref('')

const doReset = async () => {
  if (resetConfirmText.value !== 'ZERAR') return
  try {
    resetLoading.value = true
    resetError.value   = ''
    await axios.delete('/api/account/reset')
    showResetModal.value   = false
    resetConfirmText.value = ''
    window.location.href = '/'
  } catch (e: any) {
    resetError.value = e.response?.data?.message ?? 'Erro ao zerar conta.'
  } finally {
    resetLoading.value = false
  }
}
</script>

<style scoped lang="scss">
.profile-avatar {
  width: 80px; height: 80px; border-radius: 50%; overflow: hidden;
  img { width: 100%; height: 100%; object-fit: cover; }

  .avatar-overlay {
    position: absolute; inset: 0; border-radius: 50%;
    background: rgba(0,0,0,0.45);
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: 1.25rem;
    opacity: 0; transition: opacity 0.2s;
  }
  &:hover .avatar-overlay { opacity: 1; }

  .avatar-spinner {
    position: absolute; inset: 0; border-radius: 50%;
    background: rgba(0,0,0,0.5);
    display: flex; align-items: center; justify-content: center;
  }
}

.profile-menu { display: flex; flex-direction: column; gap: 0.25rem; }

.profile-link {
  display: flex; align-items: center; justify-content: space-between;
  padding: 0.875rem 1rem;
  background: var(--bs-card-bg, #fff);
  border: 1px solid var(--bs-border-color);
  border-radius: 0.75rem;
  text-decoration: none;
  color: var(--bs-body-color);
  transition: background .15s;

  &:hover { background: var(--bs-secondary-bg, #f1f5f9); }
}

.link-icon {
  width: 36px; height: 36px; border-radius: 0.5rem;
  display: flex; align-items: center; justify-content: center;
}

.reset-overlay {
  position: fixed; inset: 0;
  background: rgba(0,0,0,.5);
  display: flex; align-items: flex-end;
  z-index: 2000;
}

.reset-sheet {
  background: var(--bs-card-bg, #fff);
  border-radius: 1.25rem 1.25rem 0 0;
  padding: 1.25rem;
  width: 100%;
  max-height: 90vh;
  overflow-y: auto;

  .sheet-handle {
    width: 40px; height: 4px;
    background: var(--bs-border-color);
    border-radius: 2px;
    margin: 0 auto 1.25rem;
  }
}

.danger-icon {
  width: 64px; height: 64px;
  background: #fee2e2;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  margin: 0 auto;
  font-size: 1.75rem;
  color: #ef4444;
}

.reset-list {
  list-style: none;
  padding: 0;
  margin: 0;
  li {
    padding: 0.35rem 0;
    font-size: 0.9rem;
  }
}
</style>

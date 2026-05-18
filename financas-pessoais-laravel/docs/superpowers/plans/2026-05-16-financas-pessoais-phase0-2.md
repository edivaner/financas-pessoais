# Finanças Pessoais — Implementation Plan (Phases 0–2)

> **For agentic workers:** REQUIRED SUB-SKILL: Use `superpowers:executing-plans` to implement this plan task-by-task.

**Goal:** Build the personal finance SaaS system — Phases 0 (Foundation), 1 (Auth), 2 (Contas/Cartões).

**Architecture:** Laravel 11 Service/Repository backend + Vue 3 + Bootstrap 5 frontend, full-stack per module, Docker environment.

**Tech Stack:** Laravel 11, Sanctum, Pest, Vue 3, Pinia, Bootstrap 5, bootstrap-vue-next, Vite, MySQL.

---

## File Map

### Create
- `app/Repositories/ContaRepository.php`
- `app/Repositories/CartaoRepository.php`
- `app/Services/ContaService.php`
- `app/Services/CartaoService.php`
- `app/Http/Controllers/ContaController.php`
- `app/Http/Controllers/CartaoController.php`
- `app/Http/Requests/StoreContaRequest.php`
- `app/Http/Requests/UpdateContaRequest.php`
- `app/Http/Requests/StoreCartaoRequest.php`
- `app/Http/Requests/UpdateCartaoRequest.php`
- `database/factories/ContaFactory.php`
- `database/factories/CartaoFactory.php`
- `tests/Feature/AuthTest.php`
- `tests/Feature/ContaTest.php`
- `tests/Feature/CartaoTest.php`
- `resources/js/components/ui/BottomNav.vue`
- `resources/js/components/ui/StatCard.vue`
- `resources/js/components/ui/SectionCard.vue`
- `resources/js/components/ui/LimitBar.vue`
- `resources/js/components/forms/AccountForm.vue`
- `resources/js/components/forms/CardForm.vue`
- `resources/js/pages/profile/Accounts.vue`
- `resources/js/pages/profile/Cards.vue`
- `resources/js/stores/conta.ts`
- `resources/js/stores/cartao.ts`

### Modify
- `app/Http/Controllers/AuthController.php` — rewrite, remove OTP
- `app/Domain/Common/Enums.php` — fix TipoInvestimento
- `routes/api.php` — add ContaController, CartaoController, remove OTP routes
- `vite.config.ts` — add resolve alias, remove tailwind
- `package.json` — remove tailwind deps
- `resources/js/app.ts` — bootstrap-vue-next + axios interceptors
- `resources/js/router/index.ts` — fix broken paths
- `resources/js/theme.scss` — complete design tokens
- `resources/js/App.vue` — dark mode via data-bs-theme
- `resources/js/components/layouts/AppLayout.vue` — integrate BottomNav + sidebar
- `resources/js/stores/auth.ts` — remove OTP methods
- `resources/js/pages/auth/Login.vue` — connect to real auth
- `resources/js/pages/auth/Register.vue` — connect to real auth
- `resources/js/components/modals/AccountsManagementModal.vue`
- `resources/js/components/modals/CardsManagementModal.vue`

---

## Phase 0: Foundation

### Task 1: Fix vite.config.ts + remove Tailwind + fix package.json

**Files:**
- Modify: `vite.config.ts`
- Modify: `package.json`

- [ ] **Step 1: Update vite.config.ts**

```typescript
// vite.config.ts
import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import { VitePWA } from 'vite-plugin-pwa';
import laravel from 'laravel-vite-plugin';
import { resolve } from 'path';

export default defineConfig({
  plugins: [
    laravel({
      input: ['resources/js/app.ts'],
      refresh: true,
    }),
    vue(),
    VitePWA({
      registerType: 'autoUpdate',
      includeAssets: ['favicon.svg', 'favicon.ico', 'robots.txt'],
      manifest: {
        name: 'Finanças Pessoais',
        short_name: 'Finanças',
        theme_color: '#22c55e',
        background_color: '#0f172a',
        display: 'standalone',
        start_url: '/',
        icons: [
          { src: 'pwa-192x192.png', sizes: '192x192', type: 'image/png' },
          { src: 'pwa-512x512.png', sizes: '512x512', type: 'image/png' },
        ],
      },
      workbox: { globPatterns: ['**/*.{js,css,html,png,svg}'] },
    }),
  ],
  resolve: {
    alias: { '@': resolve(__dirname, 'resources/js') },
  },
});
```

- [ ] **Step 2: Remove Tailwind from package.json**

```json
{
  "$schema": "https://json.schemastore.org/package.json",
  "private": true,
  "type": "module",
  "scripts": {
    "build": "vite build",
    "dev": "vite"
  },
  "devDependencies": {
    "@vitejs/plugin-vue": "^6.0.1",
    "autoprefixer": "^10.4.21",
    "axios": "^1.11.0",
    "concurrently": "^9.0.1",
    "laravel-vite-plugin": "^2.0.0",
    "postcss": "^8.5.6",
    "sass": "^1.92.1",
    "vite": "^7.0.4",
    "vite-plugin-pwa": "^1.0.3"
  },
  "dependencies": {
    "bootstrap": "^5.3.8",
    "bootstrap-icons": "^1.13.1",
    "bootstrap-vue-next": "^0.40.1",
    "pinia": "^3.0.3",
    "vue": "^3.5.21",
    "vue-router": "^4.5.1",
    "vue3-apexcharts": "^1.4.4",
    "apexcharts": "^3.54.0"
  }
}
```

- [ ] **Step 3: Install dependencies inside Docker npm container**

```bash
docker compose exec npm npm install
```

Expected: packages installed, no errors.

- [ ] **Step 4: Commit**

```bash
git add vite.config.ts package.json package-lock.json
git commit -m "build: fix vite config alias, remove tailwind, add apexcharts"
```

---

### Task 2: Fix Enums + app.ts bootstrap

**Files:**
- Modify: `app/Domain/Common/Enums.php`
- Modify: `resources/js/app.ts`

- [ ] **Step 1: Fix TipoInvestimento enum**

```php
// app/Domain/Common/Enums.php — replace TipoInvestimento
enum TipoInvestimento:string {
    case INVESTIR='INVESTIR';
    case RESGATAR='RESGATAR';
}
```

Full file after edit:

```php
<?php
namespace App\Domain\Common;

enum TipoCartao:string {
    case MULTIPLO='MULTIPLO';
    case CREDITO='CREDITO';
    case DEBITO='DEBITO';
}

enum TipoCategoria:string {
    case DESPESAS='DESPESAS';
    case CREDITO='CREDITO';
    case INVESTIMENTOS='INVESTIMENTOS';
}

enum TipoLancamento:string {
    case RECEITAS='RECEITAS';
    case DESPESAS='DESPESAS';
    case TRANSFERENCIA='TRANSFERENCIA';
    case INVESTIMENTOS='INVESTIMENTOS';
}

enum TipoImagem:string {
    case LOGO='LOGO';
    case LANCAMENTO='LANCAMENTO';
}

enum Cargo:string {
    case ADM='ADM';
    case SUPORTE='SUPORTE';
    case CLIENTE='CLIENTE';
}

enum TipoInvestimento:string {
    case INVESTIR='INVESTIR';
    case RESGATAR='RESGATAR';
}

enum PeriodicidadeLimite:string {
    case MENSAL='MENSAL';
}

enum PurposeOtp:string {
    case SIGNUP='signup';
    case LOGIN='login';
    case RESET='reset';
}
```

- [ ] **Step 2: Rewrite app.ts with bootstrap-vue-next + axios interceptors**

```typescript
// resources/js/app.ts
import { createApp } from 'vue'
import { createPinia } from 'pinia'
import { createBootstrap } from 'bootstrap-vue-next'
import router from './router'
import App from './App.vue'
import axios from 'axios'

import 'bootstrap/dist/css/bootstrap.min.css'
import 'bootstrap-icons/font/bootstrap-icons.css'
import 'bootstrap-vue-next/dist/bootstrap-vue-next.css'
import './theme.scss'

axios.defaults.baseURL = '/';
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.withCredentials = true;

const app = createApp(App)
const pinia = createPinia()

app.use(pinia)
app.use(router)
app.use(createBootstrap())

// Interceptor: inject token
axios.interceptors.request.use((config) => {
  const token = localStorage.getItem('token')
  if (token) config.headers.Authorization = `Bearer ${token}`
  return config
})

// Interceptor: 401 → redirect login
axios.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('token')
      router.push('/login')
    }
    return Promise.reject(error)
  }
)

app.mount('#app')
```

- [ ] **Step 3: Commit**

```bash
git add app/Domain/Common/Enums.php resources/js/app.ts
git commit -m "fix: correct TipoInvestimento enum, bootstrap-vue-next setup with axios interceptors"
```

---

### Task 3: theme.scss + App.vue dark mode

**Files:**
- Modify: `resources/js/theme.scss`
- Modify: `resources/js/App.vue`

- [ ] **Step 1: Write theme.scss**

```scss
// resources/js/theme.scss

// Bootstrap variable overrides (must be before bootstrap import)
$primary: #22c55e;
$secondary: #64748b;
$success: #22c55e;
$danger: #ef4444;
$warning: #f59e0b;
$info: #3b82f6;
$dark: #0f172a;
$light: #f8fafc;

$border-radius: 0.75rem;
$border-radius-lg: 1rem;
$border-radius-pill: 50rem;

$font-family-sans-serif: 'Inter', system-ui, -apple-system, sans-serif;

$card-border-radius: $border-radius;
$card-box-shadow: 0 1px 3px rgba(0,0,0,.08), 0 1px 2px rgba(0,0,0,.06);

// Global styles
:root {
  --color-income: #22c55e;
  --color-expense: #ef4444;
  --color-transfer: #3b82f6;
  --color-investment: #a855f7;
  --color-pending: #f59e0b;
  --bottom-nav-height: 70px;
}

body {
  font-family: $font-family-sans-serif;
  background-color: var(--bs-body-bg);
  color: var(--bs-body-color);
}

// Dark mode token overrides
[data-bs-theme="dark"] {
  --bs-body-bg: #0f172a;
  --bs-body-color: #e2e8f0;
  --bs-card-bg: #1e293b;
  --bs-border-color: #334155;
  --bs-secondary-bg: #1e293b;
}

// Cards
.card-modern {
  background: var(--bs-card-bg, #fff);
  border-radius: $border-radius;
  box-shadow: $card-box-shadow;
  border: 1px solid var(--bs-border-color);
  padding: 1rem;
}

// Floating action button
.fab {
  width: 56px;
  height: 56px;
  border-radius: 50%;
  background: $primary;
  color: white;
  border: none;
  box-shadow: 0 4px 12px rgba(34,197,94,.4);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  cursor: pointer;
  transition: transform .2s, box-shadow .2s;

  &:hover {
    transform: scale(1.05);
    box-shadow: 0 6px 16px rgba(34,197,94,.5);
  }
}

// Amount colors
.text-income  { color: var(--color-income) !important; }
.text-expense { color: var(--color-expense) !important; }
.text-invest  { color: var(--color-investment) !important; }
.text-pending { color: var(--color-pending) !important; }

// Bottom nav spacing
.main-content {
  padding-bottom: calc(var(--bottom-nav-height) + 1rem);
}

@media (min-width: 768px) {
  .main-content {
    padding-bottom: 1rem;
    margin-left: 240px;
  }
}

// Inputs
.form-control, .form-select {
  border-radius: $border-radius;
}

// Offcanvas / Modal forms
.form-label {
  font-size: .85rem;
  font-weight: 500;
  color: var(--bs-secondary);
}
```

- [ ] **Step 2: Update App.vue for dark mode**

```vue
<!-- resources/js/App.vue -->
<template>
  <router-view />
</template>

<script setup lang="ts">
import { watch, onMounted } from 'vue'
import { useUiStore } from '@/stores/ui'

const uiStore = useUiStore()

const applyTheme = (dark: boolean) => {
  document.documentElement.setAttribute('data-bs-theme', dark ? 'dark' : 'light')
}

onMounted(() => applyTheme(uiStore.darkMode))
watch(() => uiStore.darkMode, applyTheme)
</script>
```

- [ ] **Step 3: Commit**

```bash
git add resources/js/theme.scss resources/js/App.vue
git commit -m "feat: complete theme.scss design tokens, dark mode via data-bs-theme"
```

---

### Task 4: Fix router + create BottomNav

**Files:**
- Modify: `resources/js/router/index.ts`
- Create: `resources/js/components/ui/BottomNav.vue`
- Modify: `resources/js/components/layouts/AppLayout.vue`

- [ ] **Step 1: Fix router paths**

```typescript
// resources/js/router/index.ts
import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/login',    component: () => import('@/pages/auth/Login.vue'),    meta: { guest: true } },
    { path: '/register', component: () => import('@/pages/auth/Register.vue'), meta: { guest: true } },
    { path: '/',         component: () => import('@/pages/home/Dashboard.vue'),        meta: { auth: true } },
    { path: '/transactions', component: () => import('@/pages/transactions/Transactions.vue'), meta: { auth: true } },
    { path: '/balance',  component: () => import('@/pages/balance/Balance.vue'),       meta: { auth: true } },
    { path: '/profile',  component: () => import('@/pages/profile/Profile.vue'),       meta: { auth: true } },
    { path: '/profile/accounts',      component: () => import('@/pages/profile/Accounts.vue'),      meta: { auth: true } },
    { path: '/profile/cards',         component: () => import('@/pages/profile/Cards.vue'),         meta: { auth: true } },
    { path: '/profile/categories',    component: () => import('@/pages/profile/Categories.vue'),    meta: { auth: true } },
    { path: '/profile/subcategories', component: () => import('@/pages/profile/Subcategories.vue'), meta: { auth: true } },
    { path: '/profile/limits',        component: () => import('@/pages/profile/Limits.vue'),        meta: { auth: true } },
  ],
})

router.beforeEach((to) => {
  const token = localStorage.getItem('token')
  if (to.meta.auth && !token) return '/login'
  if (to.meta.guest && token) return '/'
})

export default router
```

- [ ] **Step 2: Create BottomNav.vue**

```vue
<!-- resources/js/components/ui/BottomNav.vue -->
<template>
  <nav class="bottom-nav d-md-none">
    <router-link to="/" class="nav-item" :class="{ active: route.path === '/' }">
      <i class="bi bi-house-door-fill"></i>
      <span>Início</span>
    </router-link>

    <router-link to="/transactions" class="nav-item" :class="{ active: route.path === '/transactions' }">
      <i class="bi bi-list-ul"></i>
      <span>Lançamentos</span>
    </router-link>

    <button class="nav-fab" @click="emit('add')">
      <i class="bi bi-plus"></i>
    </button>

    <router-link to="/balance" class="nav-item" :class="{ active: route.path === '/balance' }">
      <i class="bi bi-bar-chart-fill"></i>
      <span>Balanço</span>
    </router-link>

    <router-link to="/profile" class="nav-item" :class="{ active: route.path.startsWith('/profile') }">
      <i class="bi bi-person-fill"></i>
      <span>Perfil</span>
    </router-link>
  </nav>

  <!-- Desktop sidebar -->
  <aside class="sidebar d-none d-md-flex">
    <div class="sidebar-brand">
      <i class="bi bi-currency-dollar text-success fs-4"></i>
      <span class="fw-bold">Finanças</span>
    </div>
    <nav class="sidebar-nav">
      <router-link to="/" class="sidebar-link">
        <i class="bi bi-house-door"></i> Início
      </router-link>
      <router-link to="/transactions" class="sidebar-link">
        <i class="bi bi-list-ul"></i> Lançamentos
      </router-link>
      <router-link to="/balance" class="sidebar-link">
        <i class="bi bi-bar-chart"></i> Balanço
      </router-link>
      <router-link to="/profile" class="sidebar-link">
        <i class="bi bi-person"></i> Perfil
      </router-link>
    </nav>
    <button class="fab mt-auto mx-auto" @click="emit('add')">
      <i class="bi bi-plus"></i>
    </button>
  </aside>
</template>

<script setup lang="ts">
import { useRoute } from 'vue-router'
const route = useRoute()
const emit = defineEmits<{ (e: 'add'): void }>()
</script>

<style scoped lang="scss">
.bottom-nav {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  height: var(--bottom-nav-height, 70px);
  background: var(--bs-card-bg, #fff);
  border-top: 1px solid var(--bs-border-color);
  display: flex;
  align-items: center;
  justify-content: space-around;
  z-index: 1000;
  padding: 0 0.5rem;
}

.nav-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 2px;
  color: var(--bs-secondary);
  text-decoration: none;
  font-size: .65rem;
  flex: 1;
  padding: 0.5rem 0;

  i { font-size: 1.3rem; }

  &.active { color: #22c55e; }
}

.nav-fab {
  width: 52px;
  height: 52px;
  border-radius: 50%;
  background: #22c55e;
  color: white;
  border: none;
  font-size: 1.6rem;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 12px rgba(34,197,94,.4);
  flex-shrink: 0;
}

.sidebar {
  position: fixed;
  top: 0;
  left: 0;
  width: 240px;
  height: 100vh;
  background: var(--bs-card-bg, #fff);
  border-right: 1px solid var(--bs-border-color);
  flex-direction: column;
  padding: 1.5rem 1rem;
  z-index: 1000;
  gap: 0.5rem;
}

.sidebar-brand {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 2rem;
  font-size: 1.1rem;
}

.sidebar-nav { display: flex; flex-direction: column; gap: 0.25rem; }

.sidebar-link {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.65rem 0.75rem;
  border-radius: 0.75rem;
  color: var(--bs-body-color);
  text-decoration: none;
  transition: background .15s;

  &:hover, &.router-link-active {
    background: rgba(34,197,94,.12);
    color: #22c55e;
  }
}
</style>
```

- [ ] **Step 3: Update AppLayout to use BottomNav**

```vue
<!-- resources/js/components/layouts/AppLayout.vue -->
<template>
  <div class="app-layout">
    <BottomNav @add="showAddModal = true" />

    <main class="main-content">
      <slot />
    </main>

    <TransactionTypeModal
      v-if="showAddModal"
      @close="showAddModal = false"
    />
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import BottomNav from '@/components/ui/BottomNav.vue'
import TransactionTypeModal from '@/components/modals/TransactionModal.vue'

const showAddModal = ref(false)
</script>

<style scoped lang="scss">
.app-layout { min-height: 100vh; }
</style>
```

- [ ] **Step 4: Commit**

```bash
git add resources/js/router/index.ts resources/js/components/ui/BottomNav.vue resources/js/components/layouts/AppLayout.vue
git commit -m "feat: fix router paths, add BottomNav with FAB, update AppLayout"
```

---

### Task 5: StatCard + SectionCard + LimitBar

**Files:**
- Create: `resources/js/components/ui/StatCard.vue`
- Create: `resources/js/components/ui/SectionCard.vue`
- Create: `resources/js/components/ui/LimitBar.vue`

- [ ] **Step 1: Create StatCard.vue**

```vue
<!-- resources/js/components/ui/StatCard.vue -->
<template>
  <div class="stat-card">
    <div class="stat-icon" :style="{ background: iconBg }">
      <i :class="icon"></i>
    </div>
    <div class="stat-body">
      <span class="stat-label">{{ label }}</span>
      <span class="stat-value" :class="valueClass">{{ formatted }}</span>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
  label: string
  value: number
  icon: string
  iconBg?: string
  color?: 'success' | 'danger' | 'info' | 'warning' | 'primary'
  currency?: string
}>()

const valueClass = computed(() => `text-${props.color ?? 'body'}`)

const formatted = computed(() =>
  new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: props.currency ?? 'BRL',
  }).format(props.value ?? 0)
)
</script>

<style scoped lang="scss">
.stat-card {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.875rem 1rem;
  background: var(--bs-card-bg, #fff);
  border-radius: 0.75rem;
  border: 1px solid var(--bs-border-color);
  box-shadow: 0 1px 3px rgba(0,0,0,.06);
}

.stat-icon {
  width: 40px;
  height: 40px;
  border-radius: 0.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1.1rem;
  flex-shrink: 0;
}

.stat-body {
  display: flex;
  flex-direction: column;
  min-width: 0;
}

.stat-label {
  font-size: .7rem;
  color: var(--bs-secondary);
  white-space: nowrap;
}

.stat-value {
  font-size: .95rem;
  font-weight: 700;
  white-space: nowrap;
}
</style>
```

- [ ] **Step 2: Create SectionCard.vue**

```vue
<!-- resources/js/components/ui/SectionCard.vue -->
<template>
  <div class="section-card">
    <div class="section-header">
      <h6 class="section-title mb-0">{{ title }}</h6>
      <slot name="action" />
    </div>
    <div class="section-body">
      <slot />
    </div>
  </div>
</template>

<script setup lang="ts">
defineProps<{ title: string }>()
</script>

<style scoped lang="scss">
.section-card {
  background: var(--bs-card-bg, #fff);
  border-radius: 0.75rem;
  border: 1px solid var(--bs-border-color);
  overflow: hidden;
  margin-bottom: 1rem;
}

.section-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.875rem 1rem;
  border-bottom: 1px solid var(--bs-border-color);
}

.section-title { font-size: .9rem; font-weight: 600; }

.section-body { padding: 0.75rem 1rem; }
</style>
```

- [ ] **Step 3: Create LimitBar.vue**

```vue
<!-- resources/js/components/ui/LimitBar.vue -->
<template>
  <div class="limit-bar-wrap">
    <div class="limit-bar-header">
      <div class="limit-icon">
        <i :class="icon || 'bi bi-tag-fill'" :style="{ color: iconColor || '#22c55e' }"></i>
      </div>
      <div class="limit-info">
        <span class="limit-name">{{ name }}</span>
        <span class="limit-values">{{ formattedGasto }} / {{ formattedLimite }}</span>
      </div>
      <span class="limit-pct" :class="pctClass">{{ pct }}%</span>
    </div>
    <div class="progress mt-1" style="height:6px">
      <div
        class="progress-bar"
        :class="barClass"
        :style="{ width: Math.min(pct, 100) + '%' }"
      ></div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
  name: string
  valorGasto: number
  valorLimite: number
  icon?: string
  iconColor?: string
}>()

const pct = computed(() =>
  props.valorLimite > 0 ? Math.round((props.valorGasto / props.valorLimite) * 100) : 0
)

const barClass = computed(() => {
  if (pct.value >= 100) return 'bg-danger'
  if (pct.value >= 70)  return 'bg-warning'
  return 'bg-success'
})

const pctClass = computed(() => {
  if (pct.value >= 100) return 'text-danger'
  if (pct.value >= 70)  return 'text-warning'
  return 'text-success'
})

const fmt = (v: number) =>
  new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(v ?? 0)

const formattedGasto  = computed(() => fmt(props.valorGasto))
const formattedLimite = computed(() => fmt(props.valorLimite))
</script>

<style scoped lang="scss">
.limit-bar-wrap { padding: 0.5rem 0; }

.limit-bar-header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.limit-icon {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: var(--bs-secondary-bg, #f1f5f9);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: .9rem;
  flex-shrink: 0;
}

.limit-info {
  flex: 1;
  display: flex;
  flex-direction: column;
  min-width: 0;
}

.limit-name   { font-size: .85rem; font-weight: 500; }
.limit-values { font-size: .7rem; color: var(--bs-secondary); }
.limit-pct    { font-size: .8rem; font-weight: 700; }
</style>
```

- [ ] **Step 4: Commit**

```bash
git add resources/js/components/ui/StatCard.vue resources/js/components/ui/SectionCard.vue resources/js/components/ui/LimitBar.vue
git commit -m "feat: add StatCard, SectionCard, LimitBar UI components"
```

---

## Phase 1: Auth

### Task 6: Rewrite AuthController + auth store + update routes

**Files:**
- Modify: `app/Http/Controllers/AuthController.php`
- Modify: `resources/js/stores/auth.ts`
- Modify: `routes/api.php`

- [ ] **Step 1: Write failing Pest test**

```php
<?php
// tests/Feature/AuthTest.php
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can register', function () {
    $response = $this->postJson('/api/auth/register', [
        'nome'                  => 'Edivaner',
        'sobrenome'             => 'Fernandes',
        'email'                 => 'test@example.com',
        'password'              => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertCreated()
        ->assertJsonStructure(['data' => ['token', 'user']]);
});

test('user can login', function () {
    $user = User::factory()->create(['password' => bcrypt('password123')]);

    $response = $this->postJson('/api/auth/login', [
        'email'    => $user->email,
        'password' => 'password123',
    ]);

    $response->assertOk()
        ->assertJsonStructure(['data' => ['token', 'user']]);
});

test('login fails with wrong password', function () {
    $user = User::factory()->create(['password' => bcrypt('correct')]);

    $this->postJson('/api/auth/login', [
        'email'    => $user->email,
        'password' => 'wrong',
    ])->assertUnauthorized();
});

test('authenticated user can get own data', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/auth/user')
        ->assertOk()
        ->assertJsonPath('data.email', $user->email);
});

test('authenticated user can logout', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/auth/logout')
        ->assertOk();
});
```

- [ ] **Step 2: Run test — expect FAIL**

```bash
docker compose exec laravel-app php artisan test tests/Feature/AuthTest.php
```

Expected: FAIL — controllers not wired correctly yet.

- [ ] **Step 3: Rewrite AuthController**

```php
<?php
// app/Http/Controllers/AuthController.php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nome'                  => 'required|string|max:100',
            'sobrenome'             => 'nullable|string|max:100',
            'email'                 => 'required|email|unique:users,email',
            'password'              => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'id'       => Str::uuid(),
            'nome'     => $data['nome'],
            'sobrenome'=> $data['sobrenome'] ?? '',
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'data'    => ['token' => $token, 'user' => $user],
            'message' => 'Usuário criado com sucesso.',
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Credenciais inválidas.'], 401);
        }

        $user  = Auth::user();
        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'data'    => ['token' => $token, 'user' => $user],
            'message' => 'Login realizado.',
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logout realizado.']);
    }

    public function user(Request $request): JsonResponse
    {
        return response()->json(['data' => $request->user()->load('imagem')]);
    }
}
```

- [ ] **Step 4: Update routes/api.php**

```php
<?php
// routes/api.php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ContaController;
use App\Http\Controllers\CartaoController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\SubcategoriaController;
use App\Http\Controllers\LimiteController;
use App\Http\Controllers\LancamentoController;
use App\Http\Controllers\ImagemController;

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);
    Route::post('/logout',   [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('/user',      [AuthController::class, 'user'])->middleware('auth:sanctum');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::apiResource('contas',        ContaController::class);
    Route::apiResource('cartoes',       CartaoController::class);
    Route::apiResource('categorias',    CategoriaController::class);
    Route::apiResource('subcategorias', SubcategoriaController::class);
    Route::apiResource('limites',       LimiteController::class);
    Route::apiResource('lancamentos',   LancamentoController::class);
    Route::post('imagens',             [ImagemController::class, 'store']);
});
```

- [ ] **Step 5: Run test — expect PASS**

```bash
docker compose exec laravel-app php artisan test tests/Feature/AuthTest.php
```

Expected: 5 passed.

- [ ] **Step 6: Rewrite auth store (remove OTP)**

```typescript
// resources/js/stores/auth.ts
import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from 'axios'

export interface User {
  id: string
  nome: string
  sobrenome: string
  email: string
  telefone?: string
  profissao?: string
  imagem?: { id: string; caminho: string }
}

export const useAuthStore = defineStore('auth', () => {
  const user  = ref<User | null>(null)
  const token = ref<string | null>(localStorage.getItem('token'))
  const loading = ref(false)
  const error   = ref<string | null>(null)

  const isAuthenticated = computed(() => !!token.value)

  const setToken = (t: string) => {
    token.value = t
    localStorage.setItem('token', t)
  }

  const clearAuth = () => {
    user.value  = null
    token.value = null
    localStorage.removeItem('token')
  }

  const fetchUser = async () => {
    try {
      const { data } = await axios.get('/api/auth/user')
      user.value = data.data
    } catch {
      clearAuth()
    }
  }

  const login = async (email: string, password: string) => {
    try {
      loading.value = true
      error.value   = null
      const { data } = await axios.post('/api/auth/login', { email, password })
      setToken(data.data.token)
      user.value = data.data.user
      return { success: true }
    } catch (err: any) {
      error.value = err.response?.data?.message ?? 'Erro ao fazer login'
      return { success: false, message: error.value }
    } finally {
      loading.value = false
    }
  }

  const register = async (payload: { nome: string; sobrenome?: string; email: string; password: string; password_confirmation: string }) => {
    try {
      loading.value = true
      error.value   = null
      const { data } = await axios.post('/api/auth/register', payload)
      setToken(data.data.token)
      user.value = data.data.user
      return { success: true }
    } catch (err: any) {
      error.value = err.response?.data?.message ?? 'Erro ao registrar'
      return { success: false, message: error.value }
    } finally {
      loading.value = false
    }
  }

  const logout = async () => {
    try { await axios.post('/api/auth/logout') } catch { /* ignore */ }
    finally { clearAuth() }
  }

  if (token.value) fetchUser()

  return { user: computed(() => user.value), token, isAuthenticated, loading, error, login, register, logout, fetchUser, clearAuth }
})
```

- [ ] **Step 7: Commit**

```bash
git add app/Http/Controllers/AuthController.php routes/api.php resources/js/stores/auth.ts tests/Feature/AuthTest.php
git commit -m "feat: rewrite auth to email+password only, remove OTP, update auth store"
```

---

## Phase 2: Contas

### Task 7: ContaRepository + ContaService + ContaFactory

**Files:**
- Create: `app/Repositories/ContaRepository.php`
- Create: `app/Services/ContaService.php`
- Create: `database/factories/ContaFactory.php`

- [ ] **Step 1: Create ContaFactory**

```php
<?php
// database/factories/ContaFactory.php
namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ContaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id'               => Str::uuid(),
            'user_id'          => User::factory(),
            'nome'             => $this->faker->word() . ' Bank',
            'saldo'            => $this->faker->randomFloat(2, 0, 5000),
            'saldo_investido'  => 0,
            'carteira'         => false,
            'somar_tela_inicial' => true,
            'imagem_id'        => null,
        ];
    }
}
```

- [ ] **Step 2: Create ContaRepository**

```php
<?php
// app/Repositories/ContaRepository.php
namespace App\Repositories;

use App\Models\Conta;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class ContaRepository
{
    public function allByUser(string $userId): Collection
    {
        return Conta::with('imagem')
            ->where('user_id', $userId)
            ->get();
    }

    public function findByUser(string $id, string $userId): ?Conta
    {
        return Conta::with('imagem')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->first();
    }

    public function create(array $data): Conta
    {
        $data['id'] = Str::uuid();
        return Conta::create($data);
    }

    public function update(Conta $conta, array $data): Conta
    {
        $conta->update($data);
        return $conta->fresh('imagem');
    }

    public function delete(Conta $conta): void
    {
        $conta->lancamentosOrigem()->delete();
        $conta->lancamentosDestino()->delete();
        $conta->delete();
    }
}
```

- [ ] **Step 3: Create ContaService**

```php
<?php
// app/Services/ContaService.php
namespace App\Services;

use App\Models\Conta;
use App\Models\User;
use App\Repositories\ContaRepository;
use Illuminate\Database\Eloquent\Collection;

class ContaService
{
    public function __construct(private ContaRepository $repo) {}

    public function list(User $user): Collection
    {
        return $this->repo->allByUser($user->id);
    }

    public function create(User $user, array $data): Conta
    {
        return $this->repo->create([...$data, 'user_id' => $user->id]);
    }

    public function update(User $user, string $id, array $data): Conta
    {
        $conta = $this->repo->findByUser($id, $user->id);
        abort_unless($conta, 404, 'Conta não encontrada.');
        return $this->repo->update($conta, $data);
    }

    public function delete(User $user, string $id): void
    {
        $conta = $this->repo->findByUser($id, $user->id);
        abort_unless($conta, 404, 'Conta não encontrada.');
        $this->repo->delete($conta);
    }

    public function find(User $user, string $id): Conta
    {
        $conta = $this->repo->findByUser($id, $user->id);
        abort_unless($conta, 404, 'Conta não encontrada.');
        return $conta;
    }
}
```

- [ ] **Step 4: Commit**

```bash
git add app/Repositories/ContaRepository.php app/Services/ContaService.php database/factories/ContaFactory.php
git commit -m "feat: add ContaRepository, ContaService, ContaFactory"
```

---

### Task 8: ContaController + Requests + Pest test

**Files:**
- Create: `app/Http/Requests/StoreContaRequest.php`
- Create: `app/Http/Requests/UpdateContaRequest.php`
- Create: `app/Http/Controllers/ContaController.php`
- Create: `tests/Feature/ContaTest.php`

- [ ] **Step 1: Write failing Pest test**

```php
<?php
// tests/Feature/ContaTest.php
use App\Models\User;
use App\Models\Conta;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('unauthenticated cannot list contas', function () {
    $this->getJson('/api/contas')->assertUnauthorized();
});

test('user can list own contas', function () {
    $user = User::factory()->create();
    Conta::factory()->count(2)->create(['user_id' => $user->id]);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/contas')
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

test('user cannot see other user contas', function () {
    $user  = User::factory()->create();
    $other = User::factory()->create();
    Conta::factory()->create(['user_id' => $other->id]);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/contas')
        ->assertOk()
        ->assertJsonCount(0, 'data');
});

test('user can create conta', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/contas', [
            'nome'               => 'Nubank',
            'saldo'              => 1500.50,
            'saldo_investido'    => 0,
            'carteira'           => false,
            'somar_tela_inicial' => true,
        ])
        ->assertCreated()
        ->assertJsonPath('data.nome', 'Nubank');
});

test('create conta requires nome', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/contas', ['saldo' => 100])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['nome']);
});

test('user can update own conta', function () {
    $user  = User::factory()->create();
    $conta = Conta::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user, 'sanctum')
        ->putJson("/api/contas/{$conta->id}", ['nome' => 'Updated'])
        ->assertOk()
        ->assertJsonPath('data.nome', 'Updated');
});

test('user cannot update other user conta', function () {
    $user  = User::factory()->create();
    $other = User::factory()->create();
    $conta = Conta::factory()->create(['user_id' => $other->id]);

    $this->actingAs($user, 'sanctum')
        ->putJson("/api/contas/{$conta->id}", ['nome' => 'Hack'])
        ->assertNotFound();
});

test('user can delete own conta', function () {
    $user  = User::factory()->create();
    $conta = Conta::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user, 'sanctum')
        ->deleteJson("/api/contas/{$conta->id}")
        ->assertOk();

    $this->assertSoftDeleted('contas', ['id' => $conta->id]);
});
```

- [ ] **Step 2: Run test — expect FAIL**

```bash
docker compose exec laravel-app php artisan test tests/Feature/ContaTest.php
```

Expected: FAIL — ContaController not found.

- [ ] **Step 3: Create Form Requests**

```php
<?php
// app/Http/Requests/StoreContaRequest.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContaRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nome'               => 'required|string|max:100',
            'saldo'              => 'required|numeric|min:0',
            'saldo_investido'    => 'sometimes|numeric|min:0',
            'carteira'           => 'sometimes|boolean',
            'somar_tela_inicial' => 'sometimes|boolean',
            'imagem_id'          => 'nullable|exists:imagens,id',
        ];
    }
}
```

```php
<?php
// app/Http/Requests/UpdateContaRequest.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateContaRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nome'               => 'sometimes|string|max:100',
            'carteira'           => 'sometimes|boolean',
            'somar_tela_inicial' => 'sometimes|boolean',
            'imagem_id'          => 'nullable|exists:imagens,id',
        ];
    }
}
```

- [ ] **Step 4: Create ContaController**

```php
<?php
// app/Http/Controllers/ContaController.php
namespace App\Http\Controllers;

use App\Http\Requests\StoreContaRequest;
use App\Http\Requests\UpdateContaRequest;
use App\Services\ContaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContaController extends Controller
{
    public function __construct(private ContaService $service) {}

    public function index(Request $request): JsonResponse
    {
        $contas = $this->service->list($request->user());
        return response()->json(['data' => $contas]);
    }

    public function store(StoreContaRequest $request): JsonResponse
    {
        $conta = $this->service->create($request->user(), $request->validated());
        return response()->json(['data' => $conta, 'message' => 'Conta criada.'], 201);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $conta = $this->service->find($request->user(), $id);
        return response()->json(['data' => $conta]);
    }

    public function update(UpdateContaRequest $request, string $id): JsonResponse
    {
        $conta = $this->service->update($request->user(), $id, $request->validated());
        return response()->json(['data' => $conta, 'message' => 'Conta atualizada.']);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $this->service->delete($request->user(), $id);
        return response()->json(['message' => 'Conta excluída.']);
    }
}
```

- [ ] **Step 5: Add Conta model factory reference**

```php
// app/Models/Conta.php — add at class level (Laravel auto-discovers factories by convention)
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Conta extends Model
{
    use HasFactory, SoftDeletes;
    // ... rest unchanged
}
```

- [ ] **Step 6: Run test — expect PASS**

```bash
docker compose exec laravel-app php artisan test tests/Feature/ContaTest.php
```

Expected: 8 passed.

- [ ] **Step 7: Commit**

```bash
git add app/Http/Controllers/ContaController.php app/Http/Requests/StoreContaRequest.php app/Http/Requests/UpdateContaRequest.php app/Models/Conta.php database/factories/ContaFactory.php tests/Feature/ContaTest.php
git commit -m "feat: ContaController + Service/Repository, Pest tests all pass"
```

---

### Task 9: CartaoRepository + CartaoService + CartaoController + tests

**Files:**
- Create: `app/Repositories/CartaoRepository.php`
- Create: `app/Services/CartaoService.php`
- Create: `app/Http/Controllers/CartaoController.php`
- Create: `app/Http/Requests/StoreCartaoRequest.php`
- Create: `app/Http/Requests/UpdateCartaoRequest.php`
- Create: `database/factories/CartaoFactory.php`
- Create: `tests/Feature/CartaoTest.php`

- [ ] **Step 1: Create CartaoFactory**

```php
<?php
// database/factories/CartaoFactory.php
namespace Database\Factories;

use App\Models\Conta;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CartaoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id'              => Str::uuid(),
            'user_id'         => User::factory(),
            'conta_id'        => Conta::factory(),
            'nome'            => $this->faker->word() . ' Card',
            'tipo'            => 'CREDITO',
            'fatura_total'    => 0,
            'limite_total'    => 5000,
            'data_fechamento' => now()->addDays(10),
            'imagem_id'       => null,
        ];
    }
}
```

- [ ] **Step 2: Write failing Pest test**

```php
<?php
// tests/Feature/CartaoTest.php
use App\Models\User;
use App\Models\Conta;
use App\Models\Cartao;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can list own cartoes', function () {
    $user  = User::factory()->create();
    $conta = Conta::factory()->create(['user_id' => $user->id]);
    Cartao::factory()->count(2)->create(['user_id' => $user->id, 'conta_id' => $conta->id]);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/cartoes')
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

test('user can create cartao', function () {
    $user  = User::factory()->create();
    $conta = Conta::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/cartoes', [
            'conta_id'        => $conta->id,
            'nome'            => 'Nubank Black',
            'tipo'            => 'CREDITO',
            'limite_total'    => 8000,
            'data_fechamento' => now()->addDays(15)->toDateString(),
        ])
        ->assertCreated()
        ->assertJsonPath('data.nome', 'Nubank Black');
});

test('user cannot update other user cartao', function () {
    $user   = User::factory()->create();
    $other  = User::factory()->create();
    $conta  = Conta::factory()->create(['user_id' => $other->id]);
    $cartao = Cartao::factory()->create(['user_id' => $other->id, 'conta_id' => $conta->id]);

    $this->actingAs($user, 'sanctum')
        ->putJson("/api/cartoes/{$cartao->id}", ['nome' => 'Hack'])
        ->assertNotFound();
});

test('user can delete own cartao', function () {
    $user   = User::factory()->create();
    $conta  = Conta::factory()->create(['user_id' => $user->id]);
    $cartao = Cartao::factory()->create(['user_id' => $user->id, 'conta_id' => $conta->id]);

    $this->actingAs($user, 'sanctum')
        ->deleteJson("/api/cartoes/{$cartao->id}")
        ->assertOk();

    $this->assertSoftDeleted('cartoes', ['id' => $cartao->id]);
});
```

- [ ] **Step 3: Run test — expect FAIL**

```bash
docker compose exec laravel-app php artisan test tests/Feature/CartaoTest.php
```

- [ ] **Step 4: Create CartaoRepository**

```php
<?php
// app/Repositories/CartaoRepository.php
namespace App\Repositories;

use App\Models\Cartao;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class CartaoRepository
{
    public function allByUser(string $userId): Collection
    {
        return Cartao::with(['imagem', 'conta'])
            ->where('user_id', $userId)
            ->get();
    }

    public function findByUser(string $id, string $userId): ?Cartao
    {
        return Cartao::with(['imagem', 'conta'])
            ->where('id', $id)
            ->where('user_id', $userId)
            ->first();
    }

    public function create(array $data): Cartao
    {
        $data['id'] = Str::uuid();
        return Cartao::create($data);
    }

    public function update(Cartao $cartao, array $data): Cartao
    {
        $cartao->update($data);
        return $cartao->fresh(['imagem', 'conta']);
    }

    public function delete(Cartao $cartao): void
    {
        $cartao->lancamentos()->delete();
        $cartao->delete();
    }
}
```

- [ ] **Step 5: Create CartaoService**

```php
<?php
// app/Services/CartaoService.php
namespace App\Services;

use App\Models\Cartao;
use App\Models\User;
use App\Repositories\CartaoRepository;
use Illuminate\Database\Eloquent\Collection;

class CartaoService
{
    public function __construct(private CartaoRepository $repo) {}

    public function list(User $user): Collection
    {
        return $this->repo->allByUser($user->id);
    }

    public function create(User $user, array $data): Cartao
    {
        return $this->repo->create([...$data, 'user_id' => $user->id, 'fatura_total' => 0]);
    }

    public function update(User $user, string $id, array $data): Cartao
    {
        $cartao = $this->repo->findByUser($id, $user->id);
        abort_unless($cartao, 404, 'Cartão não encontrado.');
        return $this->repo->update($cartao, $data);
    }

    public function delete(User $user, string $id): void
    {
        $cartao = $this->repo->findByUser($id, $user->id);
        abort_unless($cartao, 404, 'Cartão não encontrado.');
        $this->repo->delete($cartao);
    }

    public function find(User $user, string $id): Cartao
    {
        $cartao = $this->repo->findByUser($id, $user->id);
        abort_unless($cartao, 404, 'Cartão não encontrado.');
        return $cartao;
    }
}
```

- [ ] **Step 6: Create Form Requests**

```php
<?php
// app/Http/Requests/StoreCartaoRequest.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCartaoRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'conta_id'        => 'required|exists:contas,id',
            'nome'            => 'required|string|max:100',
            'tipo'            => 'required|in:CREDITO,DEBITO,MULTIPLO',
            'limite_total'    => 'required|numeric|min:0',
            'data_fechamento' => 'required|date',
            'imagem_id'       => 'nullable|exists:imagens,id',
        ];
    }
}
```

```php
<?php
// app/Http/Requests/UpdateCartaoRequest.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCartaoRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nome'            => 'sometimes|string|max:100',
            'limite_total'    => 'sometimes|numeric|min:0',
            'data_fechamento' => 'sometimes|date',
            'imagem_id'       => 'nullable|exists:imagens,id',
        ];
    }
}
```

- [ ] **Step 7: Create CartaoController**

```php
<?php
// app/Http/Controllers/CartaoController.php
namespace App\Http\Controllers;

use App\Http\Requests\StoreCartaoRequest;
use App\Http\Requests\UpdateCartaoRequest;
use App\Services\CartaoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartaoController extends Controller
{
    public function __construct(private CartaoService $service) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->service->list($request->user())]);
    }

    public function store(StoreCartaoRequest $request): JsonResponse
    {
        $cartao = $this->service->create($request->user(), $request->validated());
        return response()->json(['data' => $cartao, 'message' => 'Cartão criado.'], 201);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        return response()->json(['data' => $this->service->find($request->user(), $id)]);
    }

    public function update(UpdateCartaoRequest $request, string $id): JsonResponse
    {
        $cartao = $this->service->update($request->user(), $id, $request->validated());
        return response()->json(['data' => $cartao, 'message' => 'Cartão atualizado.']);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $this->service->delete($request->user(), $id);
        return response()->json(['message' => 'Cartão excluído.']);
    }
}
```

- [ ] **Step 8: Add HasFactory to Cartao model**

In `app/Models/Cartao.php` add `use HasFactory;`:

```php
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cartao extends Model
{
    use HasFactory, SoftDeletes;
    // rest unchanged
}
```

- [ ] **Step 9: Run test — expect PASS**

```bash
docker compose exec laravel-app php artisan test tests/Feature/CartaoTest.php
```

Expected: 4 passed.

- [ ] **Step 10: Commit**

```bash
git add app/Repositories/CartaoRepository.php app/Services/CartaoService.php app/Http/Controllers/CartaoController.php app/Http/Requests/StoreCartaoRequest.php app/Http/Requests/UpdateCartaoRequest.php database/factories/CartaoFactory.php app/Models/Cartao.php tests/Feature/CartaoTest.php
git commit -m "feat: CartaoController + Service/Repository, Pest tests all pass"
```

---

### Task 10: Frontend Contas + Cartões (AccountForm, CardForm, modals, pages)

**Files:**
- Create: `resources/js/components/forms/AccountForm.vue`
- Create: `resources/js/components/forms/CardForm.vue`
- Create: `resources/js/stores/conta.ts`
- Create: `resources/js/stores/cartao.ts`
- Create: `resources/js/pages/profile/Accounts.vue`
- Create: `resources/js/pages/profile/Cards.vue`
- Modify: `resources/js/components/modals/AccountsManagementModal.vue`
- Modify: `resources/js/components/modals/CardsManagementModal.vue`

- [ ] **Step 1: Create conta store**

```typescript
// resources/js/stores/conta.ts
import { defineStore } from 'pinia'
import { ref } from 'vue'
import axios from 'axios'

export interface Conta {
  id: string
  nome: string
  saldo: number
  saldo_investido: number
  carteira: boolean
  somar_tela_inicial: boolean
  imagem?: { id: string; caminho: string } | null
}

export const useContaStore = defineStore('conta', () => {
  const contas  = ref<Conta[]>([])
  const loading = ref(false)
  const error   = ref<string | null>(null)

  const fetchContas = async () => {
    try {
      loading.value = true
      const { data } = await axios.get('/api/contas')
      contas.value = data.data
    } catch (e: any) {
      error.value = e.response?.data?.message ?? 'Erro ao buscar contas'
    } finally {
      loading.value = false
    }
  }

  const createConta = async (payload: Omit<Conta, 'id'>) => {
    const { data } = await axios.post('/api/contas', payload)
    contas.value.push(data.data)
    return data.data
  }

  const updateConta = async (id: string, payload: Partial<Conta>) => {
    const { data } = await axios.put(`/api/contas/${id}`, payload)
    const idx = contas.value.findIndex(c => c.id === id)
    if (idx !== -1) contas.value[idx] = data.data
    return data.data
  }

  const deleteConta = async (id: string) => {
    await axios.delete(`/api/contas/${id}`)
    contas.value = contas.value.filter(c => c.id !== id)
  }

  return { contas, loading, error, fetchContas, createConta, updateConta, deleteConta }
})
```

- [ ] **Step 2: Create cartao store**

```typescript
// resources/js/stores/cartao.ts
import { defineStore } from 'pinia'
import { ref } from 'vue'
import axios from 'axios'

export interface Cartao {
  id: string
  conta_id: string
  nome: string
  tipo: 'CREDITO' | 'DEBITO' | 'MULTIPLO'
  fatura_total: number
  limite_total: number
  data_fechamento: string
  imagem?: { id: string; caminho: string } | null
  conta?: { id: string; nome: string }
}

export const useCartaoStore = defineStore('cartao', () => {
  const cartoes = ref<Cartao[]>([])
  const loading = ref(false)

  const fetchCartoes = async () => {
    try {
      loading.value = true
      const { data } = await axios.get('/api/cartoes')
      cartoes.value = data.data
    } finally {
      loading.value = false
    }
  }

  const createCartao = async (payload: object) => {
    const { data } = await axios.post('/api/cartoes', payload)
    cartoes.value.push(data.data)
    return data.data
  }

  const updateCartao = async (id: string, payload: object) => {
    const { data } = await axios.put(`/api/cartoes/${id}`, payload)
    const idx = cartoes.value.findIndex(c => c.id === id)
    if (idx !== -1) cartoes.value[idx] = data.data
    return data.data
  }

  const deleteCartao = async (id: string) => {
    await axios.delete(`/api/cartoes/${id}`)
    cartoes.value = cartoes.value.filter(c => c.id !== id)
  }

  return { cartoes, loading, fetchCartoes, createCartao, updateCartao, deleteCartao }
})
```

- [ ] **Step 3: Create AccountForm.vue**

```vue
<!-- resources/js/components/forms/AccountForm.vue -->
<template>
  <BForm @submit.prevent="submit">
    <div class="mb-3">
      <label class="form-label">Nome da conta *</label>
      <BFormInput v-model="form.nome" placeholder="Ex: Nubank" required />
    </div>

    <div class="mb-3">
      <label class="form-label">Saldo inicial</label>
      <div class="input-group">
        <span class="input-group-text">R$</span>
        <BFormInput v-model.number="form.saldo" type="number" step="0.01" min="0" placeholder="0,00" />
      </div>
    </div>

    <div class="mb-3 d-flex gap-3">
      <BFormCheckbox v-model="form.carteira" switch>
        Dinheiro físico (carteira)
      </BFormCheckbox>
      <BFormCheckbox v-model="form.somar_tela_inicial" switch>
        Mostrar na tela inicial
      </BFormCheckbox>
    </div>

    <div v-if="error" class="alert alert-danger py-2 mb-3">{{ error }}</div>

    <div class="d-flex gap-2 justify-content-end">
      <BButton variant="outline-secondary" @click="emit('cancel')">Cancelar</BButton>
      <BButton variant="success" type="submit" :disabled="loading">
        <span v-if="loading" class="spinner-border spinner-border-sm me-1"></span>
        {{ modelValue ? 'Salvar' : 'Criar' }}
      </BButton>
    </div>
  </BForm>
</template>

<script setup lang="ts">
import { reactive, watch, ref } from 'vue'
import { useContaStore, type Conta } from '@/stores/conta'

const props  = defineProps<{ modelValue?: Conta | null }>()
const emit   = defineEmits<{ (e: 'cancel'): void; (e: 'saved'): void }>()
const store  = useContaStore()
const loading = ref(false)
const error   = ref('')

const form = reactive({
  nome: '',
  saldo: 0,
  saldo_investido: 0,
  carteira: false,
  somar_tela_inicial: true,
})

watch(() => props.modelValue, (v) => {
  if (v) {
    form.nome = v.nome
    form.saldo = Number(v.saldo)
    form.saldo_investido = Number(v.saldo_investido)
    form.carteira = v.carteira
    form.somar_tela_inicial = v.somar_tela_inicial
  } else {
    Object.assign(form, { nome: '', saldo: 0, saldo_investido: 0, carteira: false, somar_tela_inicial: true })
  }
}, { immediate: true })

const submit = async () => {
  try {
    loading.value = true
    error.value = ''
    if (props.modelValue) {
      await store.updateConta(props.modelValue.id, { nome: form.nome, carteira: form.carteira, somar_tela_inicial: form.somar_tela_inicial })
    } else {
      await store.createConta({ ...form })
    }
    emit('saved')
  } catch (e: any) {
    error.value = e.response?.data?.message ?? 'Erro ao salvar conta'
  } finally {
    loading.value = false
  }
}
</script>
```

- [ ] **Step 4: Create CardForm.vue**

```vue
<!-- resources/js/components/forms/CardForm.vue -->
<template>
  <BForm @submit.prevent="submit">
    <div class="mb-3">
      <label class="form-label">Conta de pagamento *</label>
      <BFormSelect v-model="form.conta_id" :options="contaOptions" required />
    </div>

    <div class="mb-3">
      <label class="form-label">Nome do cartão *</label>
      <BFormInput v-model="form.nome" placeholder="Ex: Nubank" required />
    </div>

    <div class="mb-3">
      <label class="form-label">Tipo *</label>
      <BFormSelect v-model="form.tipo" :options="tipoOptions" required />
    </div>

    <div class="mb-3">
      <label class="form-label">Limite total</label>
      <div class="input-group">
        <span class="input-group-text">R$</span>
        <BFormInput v-model.number="form.limite_total" type="number" step="0.01" min="0" />
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label">Data de fechamento *</label>
      <BFormInput v-model="form.data_fechamento" type="date" required />
    </div>

    <div v-if="error" class="alert alert-danger py-2 mb-3">{{ error }}</div>

    <div class="d-flex gap-2 justify-content-end">
      <BButton variant="outline-secondary" @click="emit('cancel')">Cancelar</BButton>
      <BButton variant="success" type="submit" :disabled="loading">
        <span v-if="loading" class="spinner-border spinner-border-sm me-1"></span>
        {{ modelValue ? 'Salvar' : 'Criar' }}
      </BButton>
    </div>
  </BForm>
</template>

<script setup lang="ts">
import { reactive, watch, ref, computed } from 'vue'
import { useContaStore } from '@/stores/conta'
import { useCartaoStore, type Cartao } from '@/stores/cartao'

const props  = defineProps<{ modelValue?: Cartao | null }>()
const emit   = defineEmits<{ (e: 'cancel'): void; (e: 'saved'): void }>()
const cartaoStore = useCartaoStore()
const contaStore  = useContaStore()
const loading = ref(false)
const error   = ref('')

const contaOptions = computed(() => [
  { value: '', text: 'Selecione a conta' },
  ...contaStore.contas.map(c => ({ value: c.id, text: c.nome }))
])

const tipoOptions = [
  { value: 'CREDITO',  text: 'Crédito' },
  { value: 'DEBITO',   text: 'Débito' },
  { value: 'MULTIPLO', text: 'Múltiplo (Crédito e Débito)' },
]

const form = reactive({
  conta_id: '',
  nome: '',
  tipo: 'CREDITO' as 'CREDITO' | 'DEBITO' | 'MULTIPLO',
  limite_total: 0,
  data_fechamento: '',
})

watch(() => props.modelValue, (v) => {
  if (v) {
    form.conta_id = v.conta_id
    form.nome = v.nome
    form.tipo = v.tipo
    form.limite_total = Number(v.limite_total)
    form.data_fechamento = v.data_fechamento?.substring(0, 10) ?? ''
  } else {
    Object.assign(form, { conta_id: '', nome: '', tipo: 'CREDITO', limite_total: 0, data_fechamento: '' })
  }
}, { immediate: true })

const submit = async () => {
  try {
    loading.value = true
    error.value = ''
    if (props.modelValue) {
      await cartaoStore.updateCartao(props.modelValue.id, { nome: form.nome, limite_total: form.limite_total, data_fechamento: form.data_fechamento })
    } else {
      await cartaoStore.createCartao({ ...form })
    }
    emit('saved')
  } catch (e: any) {
    error.value = e.response?.data?.message ?? 'Erro ao salvar cartão'
  } finally {
    loading.value = false
  }
}
</script>
```

- [ ] **Step 5: Create Accounts.vue page**

```vue
<!-- resources/js/pages/profile/Accounts.vue -->
<template>
  <AppLayout>
    <div class="container-fluid px-3 py-3">
      <div class="d-flex align-items-center mb-3">
        <router-link to="/profile" class="btn btn-sm btn-ghost me-2">
          <i class="bi bi-arrow-left"></i>
        </router-link>
        <h5 class="mb-0 fw-bold">Minhas Contas</h5>
        <button class="btn btn-sm btn-success ms-auto" @click="openCreate">
          <i class="bi bi-plus-lg me-1"></i> Adicionar
        </button>
      </div>

      <div v-if="store.loading" class="text-center py-4">
        <div class="spinner-border text-success"></div>
      </div>

      <div v-else>
        <div
          v-for="conta in store.contas"
          :key="conta.id"
          class="card-modern mb-2 d-flex align-items-center"
        >
          <div class="account-logo me-3">
            <img v-if="conta.imagem" :src="conta.imagem.caminho" :alt="conta.nome" />
            <i v-else class="bi bi-bank2 text-success fs-4"></i>
          </div>
          <div class="flex-grow-1">
            <div class="fw-semibold">{{ conta.nome }}</div>
            <small class="text-secondary">
              Saldo: {{ fmt(conta.saldo) }}
              <span v-if="Number(conta.saldo_investido) > 0"> · Investido: {{ fmt(conta.saldo_investido) }}</span>
            </small>
          </div>
          <div class="d-flex gap-2">
            <button class="btn btn-sm btn-outline-primary" @click="openEdit(conta)">
              <i class="bi bi-pencil"></i>
            </button>
            <button class="btn btn-sm btn-outline-danger" @click="confirmDelete(conta)">
              <i class="bi bi-trash"></i>
            </button>
          </div>
        </div>

        <p v-if="!store.contas.length" class="text-center text-secondary py-4">
          Nenhuma conta cadastrada.
        </p>
      </div>
    </div>

    <!-- Offcanvas form -->
    <BOffcanvas v-model="showForm" placement="bottom" title="">
      <template #header>
        <h6 class="mb-0">{{ editing ? 'Editar Conta' : 'Nova Conta' }}</h6>
      </template>
      <AccountForm :model-value="editing" @cancel="showForm = false" @saved="onSaved" />
    </BOffcanvas>

    <!-- Confirm delete modal -->
    <BModal v-model="showDelete" title="Confirmar exclusão" @ok="doDelete">
      <p>Excluir <strong>{{ deleting?.nome }}</strong>? Todos os lançamentos vinculados serão removidos.</p>
    </BModal>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import AppLayout from '@/components/layouts/AppLayout.vue'
import AccountForm from '@/components/forms/AccountForm.vue'
import { useContaStore, type Conta } from '@/stores/conta'

const store   = useContaStore()
const showForm   = ref(false)
const showDelete = ref(false)
const editing    = ref<Conta | null>(null)
const deleting   = ref<Conta | null>(null)

const fmt = (v: number) =>
  new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(v ?? 0)

const openCreate = () => { editing.value = null; showForm.value = true }
const openEdit   = (c: Conta) => { editing.value = c; showForm.value = true }
const confirmDelete = (c: Conta) => { deleting.value = c; showDelete.value = true }

const onSaved = () => { showForm.value = false; store.fetchContas() }

const doDelete = async () => {
  if (!deleting.value) return
  await store.deleteConta(deleting.value.id)
  showDelete.value = false
}

onMounted(() => store.fetchContas())
</script>

<style scoped lang="scss">
.account-logo {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  overflow: hidden;
  background: var(--bs-secondary-bg, #f1f5f9);
  display: flex;
  align-items: center;
  justify-content: center;

  img { width: 100%; height: 100%; object-fit: cover; }
}
</style>
```

- [ ] **Step 6: Create Cards.vue page (same pattern as Accounts)**

```vue
<!-- resources/js/pages/profile/Cards.vue -->
<template>
  <AppLayout>
    <div class="container-fluid px-3 py-3">
      <div class="d-flex align-items-center mb-3">
        <router-link to="/profile" class="btn btn-sm btn-ghost me-2">
          <i class="bi bi-arrow-left"></i>
        </router-link>
        <h5 class="mb-0 fw-bold">Meus Cartões</h5>
        <button class="btn btn-sm btn-success ms-auto" @click="openCreate">
          <i class="bi bi-plus-lg me-1"></i> Adicionar
        </button>
      </div>

      <div v-if="store.loading" class="text-center py-4">
        <div class="spinner-border text-success"></div>
      </div>

      <div v-else>
        <div
          v-for="cartao in store.cartoes"
          :key="cartao.id"
          class="card-modern mb-2 d-flex align-items-center"
        >
          <div class="card-logo me-3">
            <img v-if="cartao.imagem" :src="cartao.imagem.caminho" :alt="cartao.nome" />
            <i v-else class="bi bi-credit-card text-success fs-4"></i>
          </div>
          <div class="flex-grow-1">
            <div class="fw-semibold">{{ cartao.nome }}</div>
            <small class="text-secondary">
              {{ cartao.tipo }} · Fatura: {{ fmt(cartao.fatura_total) }} / {{ fmt(cartao.limite_total) }}
            </small>
          </div>
          <div class="d-flex gap-2">
            <button class="btn btn-sm btn-outline-primary" @click="openEdit(cartao)">
              <i class="bi bi-pencil"></i>
            </button>
            <button class="btn btn-sm btn-outline-danger" @click="confirmDelete(cartao)">
              <i class="bi bi-trash"></i>
            </button>
          </div>
        </div>

        <p v-if="!store.cartoes.length" class="text-center text-secondary py-4">
          Nenhum cartão cadastrado.
        </p>
      </div>
    </div>

    <BOffcanvas v-model="showForm" placement="bottom">
      <template #header>
        <h6 class="mb-0">{{ editing ? 'Editar Cartão' : 'Novo Cartão' }}</h6>
      </template>
      <CardForm :model-value="editing" @cancel="showForm = false" @saved="onSaved" />
    </BOffcanvas>

    <BModal v-model="showDelete" title="Confirmar exclusão" @ok="doDelete">
      <p>Excluir <strong>{{ deleting?.nome }}</strong>? Lançamentos vinculados serão removidos.</p>
    </BModal>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import AppLayout from '@/components/layouts/AppLayout.vue'
import CardForm from '@/components/forms/CardForm.vue'
import { useCartaoStore, type Cartao } from '@/stores/cartao'
import { useContaStore } from '@/stores/conta'

const store      = useCartaoStore()
const contaStore = useContaStore()
const showForm   = ref(false)
const showDelete = ref(false)
const editing    = ref<Cartao | null>(null)
const deleting   = ref<Cartao | null>(null)

const fmt = (v: number) =>
  new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(v ?? 0)

const openCreate = () => { editing.value = null; showForm.value = true }
const openEdit   = (c: Cartao) => { editing.value = c; showForm.value = true }
const confirmDelete = (c: Cartao) => { deleting.value = c; showDelete.value = true }
const onSaved = () => { showForm.value = false; store.fetchCartoes() }
const doDelete = async () => {
  if (!deleting.value) return
  await store.deleteCartao(deleting.value.id)
  showDelete.value = false
}

onMounted(async () => {
  await contaStore.fetchContas()
  await store.fetchCartoes()
})
</script>

<style scoped lang="scss">
.card-logo {
  width: 44px; height: 44px; border-radius: 0.5rem; overflow: hidden;
  background: var(--bs-secondary-bg, #f1f5f9);
  display: flex; align-items: center; justify-content: center;
  img { width: 100%; height: 100%; object-fit: cover; }
}
</style>
```

- [ ] **Step 7: Verify in browser**

```bash
docker compose exec npm npm run dev
```

Open `http://localhost` → login → navigate to `/profile/accounts` and `/profile/cards`.  
Verify: list loads, create form opens, items appear after save, delete confirmation modal works.

- [ ] **Step 8: Commit**

```bash
git add resources/js/stores/conta.ts resources/js/stores/cartao.ts resources/js/components/forms/AccountForm.vue resources/js/components/forms/CardForm.vue resources/js/pages/profile/Accounts.vue resources/js/pages/profile/Cards.vue
git commit -m "feat: Contas and Cartoes frontend — stores, forms, pages"
```

---

**End of Phases 0–2.**  
Continue with: `docs/superpowers/plans/2026-05-16-financas-pessoais-phase3-6.md`

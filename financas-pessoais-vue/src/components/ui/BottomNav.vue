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

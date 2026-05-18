<!-- resources/js/pages/home/Dashboard.vue -->
<template>
  <AppLayout>
    <div class="container-fluid px-3 py-3">

      <!-- User header -->
      <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="d-flex align-items-center gap-2">
          <div class="avatar">
            <img v-if="user?.imagem" :src="user.imagem.caminho" />
            <i v-else class="bi bi-person-circle fs-3 text-secondary"></i>
          </div>
          <div>
            <div class="fw-semibold">{{ user?.nome }}</div>
            <small class="text-secondary">{{ monthLabel }}</small>
          </div>
        </div>
        <button class="btn btn-sm btn-outline-secondary" @click="authStore.logout().then(() => router.push('/login'))">
          <i class="bi bi-box-arrow-right"></i>
        </button>
      </div>

      <!-- Loading -->
      <div v-if="store.loading" class="text-center py-5">
        <div class="spinner-border text-success"></div>
      </div>

      <template v-else>
        <!-- StatCards grid -->
        <div class="stats-grid mb-4">
          <StatCard label="Saldo Disponível" :value="t.saldo_disponivel" icon="bi bi-wallet2"          icon-bg="#22c55e" color="success" />
          <StatCard label="Receitas"         :value="t.receitas"         icon="bi bi-arrow-down-circle" icon-bg="#22c55e" color="success" />
          <StatCard label="Despesas"         :value="t.despesas"         icon="bi bi-arrow-up-circle"  icon-bg="#ef4444" color="danger"  />
          <StatCard label="Pago"             :value="t.pago"             icon="bi bi-check-circle"      icon-bg="#3b82f6" color="info"    />
          <StatCard label="Pendente"         :value="t.pendente"         icon="bi bi-clock"             icon-bg="#f59e0b" color="warning" />
          <StatCard label="Investido"        :value="t.saldo_investido"  icon="bi bi-graph-up-arrow"    icon-bg="#a855f7" color="primary" />
        </div>

        <!-- Contas -->
        <SectionCard v-if="contas.length" title="Minhas Contas" class="mb-3">
          <template #action>
            <router-link to="/profile/accounts" class="btn btn-sm btn-outline-success">
              <i class="bi bi-plus"></i>
            </router-link>
          </template>
          <div v-for="conta in contas" :key="conta.id" class="conta-item d-flex align-items-center py-2" style="cursor:pointer" @click="$router.push({ path: '/transactions', query: { conta_id: conta.id } })">
            <div class="conta-logo me-3">
              <img v-if="conta.imagem || conta.logo_path" :src="conta.imagem ? conta.imagem.caminho : conta.logo_path" :alt="conta.nome" />
              <i v-else class="bi bi-bank2 text-success"></i>
            </div>
            <div class="flex-grow-1">
              <div class="fw-semibold">{{ conta.nome }}</div>
              <small class="text-secondary">Saldo</small>
            </div>
            <div class="text-end">
              <div class="fw-bold text-success">{{ fmt(conta.saldo) }}</div>
              <small v-if="!conta.carteira && Number(conta.saldo_investido) > 0" class="text-secondary">
                Investimentos: {{ fmt(conta.saldo_investido) }}
              </small>
            </div>
          </div>
        </SectionCard>

        <!-- Cartões -->
        <SectionCard v-if="cartoes.length" title="Meus Cartões" class="mb-3">
          <template #action>
            <router-link to="/profile/cards" class="btn btn-sm btn-outline-success">
              <i class="bi bi-plus"></i>
            </router-link>
          </template>
          <div v-for="cartao in cartoes" :key="cartao.id" class="cartao-item d-flex align-items-center py-2" style="cursor:pointer" @click="$router.push({ path: '/transactions', query: { cartao_id: cartao.id } })">
            <div class="cartao-logo me-3">
              <img v-if="cartao.imagem || cartao.logo_path" :src="cartao.imagem ? cartao.imagem.caminho : cartao.logo_path" :alt="cartao.nome" />
              <i v-else class="bi bi-credit-card text-success"></i>
            </div>
            <div class="flex-grow-1">
              <div class="fw-semibold">{{ cartao.nome }}</div>
              <small class="text-secondary">Fecha em {{ formatDate(cartao.data_fechamento) }}</small>
            </div>
            <div class="text-end">
              <div class="fw-bold text-danger">{{ fmt(cartao.fatura_total) }}</div>
              <small class="text-secondary">/ {{ fmt(cartao.limite_total) }}</small>
            </div>
          </div>
        </SectionCard>

        <!-- Limites -->
        <SectionCard v-if="limites.length" title="Meus Limites" class="mb-3">
          <LimitBar
            v-for="limite in limites"
            :key="limite.id"
            :name="limite.titulo ?? limite.categoria?.nome"
            :valor-gasto="Number(limite.valor_gasto_atual)"
            :valor-limite="Number(limite.valor_limite)"
            :icon="limite.categoria?.icone"
            :icon-color="limite.categoria?.cor"
            class="mb-2"
          />
        </SectionCard>
      </template>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import AppLayout   from '@/components/layouts/AppLayout.vue'
import StatCard    from '@/components/ui/StatCard.vue'
import SectionCard from '@/components/ui/SectionCard.vue'
import LimitBar    from '@/components/ui/LimitBar.vue'
import { useDashboardStore } from '@/stores/dashboard'
import { useAuthStore }      from '@/stores/auth'
import { useUiStore }        from '@/stores/ui'

const store     = useDashboardStore()
const authStore = useAuthStore()
const uiStore   = useUiStore()
const router    = useRouter()
const user      = computed(() => authStore.user)
const t         = computed(() => store.totais())
const contas    = computed(() => store.contas())
const cartoes   = computed(() => store.cartoes())
const limites   = computed(() => store.limites())

const monthLabel = computed(() => {
  const now = new Date()
  return now.toLocaleDateString('pt-BR', { month: 'long', year: 'numeric' })
})

const fmt = (v: number) => uiStore.formatCurrency(v)

const formatDate = (d: string) =>
  d ? new Date(d).toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit' }) : '—'

onMounted(() => store.fetchDashboard())
</script>

<style scoped lang="scss">
.stats-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0.75rem;
}

.avatar {
  width: 40px; height: 40px; border-radius: 50%; overflow: hidden;
  img { width: 100%; height: 100%; object-fit: cover; }
}

.conta-logo, .cartao-logo {
  width: 40px; height: 40px; border-radius: 50%; overflow: hidden;
  display: flex; align-items: center; justify-content: center;
  background: var(--bs-secondary-bg);
  img { width: 100%; height: 100%; object-fit: contain; padding: 4px; }
}

:root[data-bs-theme="dark"] .conta-logo,
:root[data-bs-theme="dark"] .cartao-logo {
  background: #fff;
}
</style>

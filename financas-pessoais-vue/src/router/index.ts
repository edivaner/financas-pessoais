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

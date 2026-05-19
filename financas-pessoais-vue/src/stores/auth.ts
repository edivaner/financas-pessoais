import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from 'axios'
import { useUiStore } from './ui'

export interface User {
  id: string
  nome: string
  sobrenome: string
  email: string
  telefone?: string
  profissao?: string
  dark_mode: boolean
  currency: string
  imagem?: { id: string; caminho: string }
}

export const useAuthStore = defineStore('auth', () => {
  const user    = ref<User | null>(null)
  const token   = ref<string | null>(localStorage.getItem('token'))
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

  const applyUserPreferences = (u: User) => {
    user.value = u
    const uiStore = useUiStore()
    uiStore.loadFromUser(u)
  }

  const fetchUser = async () => {
    try {
      const { data } = await axios.get('/api/auth/user')
      applyUserPreferences(data.data)
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
      applyUserPreferences(data.data.user)
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
      applyUserPreferences(data.data.user)
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

  const updateUser = async (payload: { nome: string; sobrenome?: string }) => {
    const { data } = await axios.put('/api/auth/user', payload)
    applyUserPreferences(data.data)
  }

  const uploadAvatar = async (file: File) => {
    const form = new FormData()
    form.append('foto', file)
    const { data } = await axios.post('/api/auth/avatar', form)
    applyUserPreferences(data.data)
  }

  if (token.value) fetchUser()

  return { user: computed(() => user.value), token, isAuthenticated, loading, error, login, register, logout, fetchUser, clearAuth, uploadAvatar, updateUser }
})

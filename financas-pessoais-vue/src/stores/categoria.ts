// resources/js/stores/categoria.ts
import { defineStore } from 'pinia'
import { ref } from 'vue'
import axios from 'axios'

export interface Categoria {
  id: string
  user_id: string | null
  nome: string
  cor: string
  icone: string
  tipo: 'DESPESAS' | 'CREDITO' | 'INVESTIMENTOS'
  ativo: boolean
  essencial: boolean
}

export interface Subcategoria {
  id: string
  user_id: string | null
  categoria_id: string
  nome: string
  cor: string
  icone: string
  ativo: boolean
  categoria?: Categoria
}

export interface Limite {
  id: string
  categoria_id: string
  titulo: string
  descricao?: string
  valor_limite: number
  valor_gasto_atual: number
  categoria?: Categoria
}

export const useCategoriaStore = defineStore('categoria', () => {
  const categorias    = ref<Categoria[]>([])
  const subcategorias = ref<Subcategoria[]>([])
  const limites       = ref<Limite[]>([])

  const fetchCategorias    = async () => { const { data } = await axios.get('/api/categorias');    categorias.value = data.data }
  const fetchSubcategorias = async () => { const { data } = await axios.get('/api/subcategorias'); subcategorias.value = data.data }
  const fetchLimites       = async () => { const { data } = await axios.get('/api/limites');       limites.value = data.data }

  const createCategoria = async (payload: object) => {
    const { data } = await axios.post('/api/categorias', payload)
    categorias.value.push(data.data)
    return data.data
  }

  const updateCategoria = async (id: string, payload: object) => {
    const { data } = await axios.put(`/api/categorias/${id}`, payload)
    const idx = categorias.value.findIndex(c => c.id === id)
    if (idx !== -1) categorias.value[idx] = data.data
  }

  const deleteCategoria = async (id: string) => {
    await axios.delete(`/api/categorias/${id}`)
    categorias.value = categorias.value.filter(c => c.id !== id)
  }

  const createSubcategoria = async (payload: object) => {
    const { data } = await axios.post('/api/subcategorias', payload)
    subcategorias.value.push(data.data)
  }

  const updateSubcategoria = async (id: string, payload: object) => {
    const { data } = await axios.put(`/api/subcategorias/${id}`, payload)
    const idx = subcategorias.value.findIndex(s => s.id === id)
    if (idx !== -1) subcategorias.value[idx] = data.data
  }

  const deleteSubcategoria = async (id: string) => {
    await axios.delete(`/api/subcategorias/${id}`)
    subcategorias.value = subcategorias.value.filter(s => s.id !== id)
  }

  const createLimite = async (payload: object) => {
    const { data } = await axios.post('/api/limites', payload)
    limites.value.push(data.data)
  }

  const updateLimite = async (id: string, payload: object) => {
    const { data } = await axios.put(`/api/limites/${id}`, payload)
    const idx = limites.value.findIndex(l => l.id === id)
    if (idx !== -1) limites.value[idx] = data.data
  }

  const deleteLimite = async (id: string) => {
    await axios.delete(`/api/limites/${id}`)
    limites.value = limites.value.filter(l => l.id !== id)
  }

  return {
    categorias, subcategorias, limites,
    fetchCategorias, fetchSubcategorias, fetchLimites,
    createCategoria, updateCategoria, deleteCategoria,
    createSubcategoria, updateSubcategoria, deleteSubcategoria,
    createLimite, updateLimite, deleteLimite,
  }
})

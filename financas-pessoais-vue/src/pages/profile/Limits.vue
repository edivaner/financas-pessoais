<!-- resources/js/pages/profile/Limits.vue -->
<template>
  <AppLayout>
    <div class="container-fluid px-3 py-3">
      <div class="d-flex align-items-center mb-3">
        <router-link to="/profile" class="btn btn-sm me-2"><i class="bi bi-arrow-left"></i></router-link>
        <h5 class="mb-0 fw-bold">Meus Limites</h5>
        <button class="btn btn-sm btn-success ms-auto" @click="openCreate"><i class="bi bi-plus-lg me-1"></i> Adicionar</button>
      </div>

      <div v-for="limite in store.limites" :key="limite.id" class="card-modern mb-3">
        <LimitBar
          :name="limite.titulo"
          :valor-gasto="Number(limite.valor_gasto_atual)"
          :valor-limite="Number(limite.valor_limite)"
          :icon="limite.categoria?.icone"
          :icon-color="limite.categoria?.cor"
        />
        <div class="d-flex align-items-center gap-2 mt-2">
          <span v-if="limite.descricao" class="text-secondary small flex-grow-1">{{ limite.descricao }}</span>
          <div v-else class="flex-grow-1"></div>
          <button class="btn btn-sm btn-outline-primary" @click="openEdit(limite)"><i class="bi bi-pencil"></i></button>
          <button class="btn btn-sm btn-outline-danger" @click="confirmDelete(limite)"><i class="bi bi-trash"></i></button>
        </div>
      </div>

      <p v-if="!store.limites.length" class="text-center text-secondary py-4">Nenhum limite cadastrado.</p>
    </div>

    <!-- Offcanvas form -->
    <div class="offcanvas offcanvas-bottom" :class="{ show: showForm }" tabindex="-1" style="height:auto; max-height:90vh; border-radius:1.25rem 1.25rem 0 0;">
      <div class="offcanvas-header">
        <h6 class="offcanvas-title">{{ editing ? 'Editar' : 'Novo' }} Limite</h6>
        <button type="button" class="btn-close" @click="showForm = false"></button>
      </div>
      <div class="offcanvas-body pt-0">
        <form @submit.prevent="submit">
          <div class="mb-3">
            <label class="form-label">Título *</label>
            <input v-model="form.titulo" class="form-control" maxlength="25" required />
          </div>
          <div class="mb-3">
            <label class="form-label">Categoria *</label>
            <select v-model="form.categoria_id" class="form-select" :disabled="!!editing" required>
              <option value="">Selecione</option>
              <option v-for="c in store.categorias" :key="c.id" :value="c.id">{{ c.nome }}</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Valor limite *</label>
            <CurrencyInput v-model="form.valor_limite" placeholder="0,00" />
          </div>
          <div class="mb-3">
            <label class="form-label">Descrição</label>
            <input v-model="form.descricao" class="form-control" maxlength="255" />
          </div>
          <div class="d-flex gap-2 justify-content-end">
            <button type="button" class="btn btn-outline-secondary" @click="showForm = false">Cancelar</button>
            <button type="submit" class="btn btn-success">Salvar</button>
          </div>
        </form>
      </div>
    </div>
    <div v-if="showForm" class="offcanvas-backdrop fade show" @click="showForm = false"></div>

    <!-- Delete confirm modal -->
    <div class="modal fade" :class="{ show: showDelete }" tabindex="-1" :style="showDelete ? 'display:block' : ''">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Confirmar</h5>
            <button type="button" class="btn-close" @click="showDelete = false"></button>
          </div>
          <div class="modal-body">
            <p>Excluir limite <strong>{{ deleting?.titulo }}</strong>?</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" @click="showDelete = false">Cancelar</button>
            <button type="button" class="btn btn-danger" @click="doDelete">Excluir</button>
          </div>
        </div>
      </div>
    </div>
    <div v-if="showDelete" class="modal-backdrop fade show"></div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import AppLayout from '@/components/layouts/AppLayout.vue'
import LimitBar from '@/components/ui/LimitBar.vue'
import CurrencyInput from '@/components/ui/CurrencyInput.vue'
import { useCategoriaStore, type Limite } from '@/stores/categoria'
import { useUiStore } from '@/stores/ui'

const store      = useCategoriaStore()
const uiStore    = useUiStore()
const showForm   = ref(false)
const showDelete = ref(false)
const editing    = ref<Limite | null>(null)
const deleting   = ref<Limite | null>(null)

const form = reactive({ titulo: '', categoria_id: '', valor_limite: 0, descricao: '' })

const openCreate = () => {
  editing.value = null
  Object.assign(form, { titulo: '', categoria_id: '', valor_limite: 0, descricao: '' })
  showForm.value = true
}

const openEdit = (l: Limite) => {
  editing.value = l
  Object.assign(form, { titulo: l.titulo, categoria_id: l.categoria_id, valor_limite: Number(l.valor_limite), descricao: l.descricao ?? '' })
  showForm.value = true
}

const confirmDelete = (l: Limite) => { deleting.value = l; showDelete.value = true }

const submit = async () => {
  if (editing.value) {
    await store.updateLimite(editing.value.id, { titulo: form.titulo, valor_limite: form.valor_limite, descricao: form.descricao })
  } else {
    await store.createLimite({ ...form })
  }
  showForm.value = false
}

const doDelete = async () => {
  if (!deleting.value) return
  await store.deleteLimite(deleting.value.id)
  showDelete.value = false
}

onMounted(async () => {
  await store.fetchCategorias()
  await store.fetchLimites()
})
</script>

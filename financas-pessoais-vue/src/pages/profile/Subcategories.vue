<!-- resources/js/pages/profile/Subcategories.vue -->
<template>
  <AppLayout>
    <div class="container-fluid px-3 py-3">
      <div class="d-flex align-items-center mb-3">
        <router-link to="/profile" class="btn btn-sm me-2"><i class="bi bi-arrow-left"></i></router-link>
        <h5 class="mb-0 fw-bold">Subcategorias</h5>
        <button class="btn btn-sm btn-success ms-auto" @click="openCreate">
          <i class="bi bi-plus-lg me-1"></i> Adicionar
        </button>
      </div>

      <p v-if="!store.categorias.length" class="text-center text-secondary py-4">Nenhuma categoria cadastrada.</p>

      <div v-for="cat in store.categorias" :key="cat.id" class="mb-4">
        <div class="d-flex align-items-center gap-2 mb-2">
          <div class="cat-dot" :style="{ background: cat.cor }"></div>
          <span class="fw-semibold">{{ cat.nome }}</span>
        </div>

        <div
          v-for="sub in subsByCategoria(cat.id)"
          :key="sub.id"
          class="card-modern mb-2 d-flex align-items-center"
        >
          <div class="sub-icon me-3" :style="{ background: sub.cor + '22', color: sub.cor }">
            <i :class="sub.icone"></i>
          </div>
          <div class="flex-grow-1">
            <div class="fw-semibold">{{ sub.nome }}</div>
            <small class="text-secondary">{{ sub.ativo ? 'Ativo' : 'Inativo' }}</small>
          </div>
          <div class="d-flex gap-2 align-items-center">
            <span v-if="!sub.user_id" class="badge bg-secondary-subtle text-secondary">Sistema</span>
            <template v-else>
              <button class="btn btn-sm btn-outline-warning" @click="toggleAtivo(sub)">
                <i :class="sub.ativo ? 'bi bi-toggle-on' : 'bi bi-toggle-off'"></i>
              </button>
              <button class="btn btn-sm btn-outline-primary" @click="openEdit(sub)"><i class="bi bi-pencil"></i></button>
              <button class="btn btn-sm btn-outline-danger" @click="confirmDelete(sub)"><i class="bi bi-trash"></i></button>
            </template>
          </div>
        </div>

        <p v-if="!subsByCategoria(cat.id).length" class="text-secondary small ms-2">
          Nenhuma subcategoria.
        </p>
      </div>
    </div>

    <!-- Offcanvas form -->
    <div class="offcanvas offcanvas-bottom" :class="{ show: showForm }" tabindex="-1" style="height:auto; max-height:90vh; border-radius:1.25rem 1.25rem 0 0;">
      <div class="offcanvas-header">
        <h6 class="offcanvas-title">{{ editing ? 'Editar' : 'Nova' }} Subcategoria</h6>
        <button type="button" class="btn-close" @click="showForm = false"></button>
      </div>
      <div class="offcanvas-body pt-0">
        <form @submit.prevent="submit">
          <div class="mb-3">
            <label class="form-label">Categoria *</label>
            <select v-model="form.categoria_id" class="form-select" :disabled="!!editing" required>
              <option value="">Selecione</option>
              <option v-for="c in store.categorias" :key="c.id" :value="c.id">{{ c.nome }}</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Nome *</label>
            <input v-model="form.nome" class="form-control" maxlength="100" required />
          </div>
          <div class="mb-3">
            <ColorPicker v-model="form.cor" label="Cor" />
          </div>
          <div class="mb-3">
            <IconPicker v-model="form.icone" label="Ícone" :preview-color="form.cor" />
          </div>
          <div v-if="formError" class="alert alert-danger py-2">{{ formError }}</div>
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
            <h5 class="modal-title">Confirmar exclusão</h5>
            <button type="button" class="btn-close" @click="showDelete = false"></button>
          </div>
          <div class="modal-body">
            <p>Excluir <strong>{{ deleting?.nome }}</strong>?</p>
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
import IconPicker from '@/components/ui/IconPicker.vue'
import ColorPicker from '@/components/ui/ColorPicker.vue'
import { useCategoriaStore, type Subcategoria } from '@/stores/categoria'

const store      = useCategoriaStore()
const showForm   = ref(false)
const showDelete = ref(false)
const editing    = ref<Subcategoria | null>(null)
const deleting   = ref<Subcategoria | null>(null)
const formError  = ref('')

const form = reactive({ categoria_id: '', nome: '', cor: '#27ae60', icone: 'bi bi-tag' })

const subsByCategoria = (catId: string) =>
  store.subcategorias.filter((s: Subcategoria) => s.categoria_id === catId)

const openCreate = () => {
  editing.value = null
  Object.assign(form, { categoria_id: '', nome: '', cor: '#27ae60', icone: 'bi bi-tag' })
  showForm.value = true
}

const openEdit = (sub: Subcategoria) => {
  editing.value = sub
  Object.assign(form, { categoria_id: sub.categoria_id, nome: sub.nome, cor: sub.cor, icone: sub.icone })
  showForm.value = true
}

const toggleAtivo = async (sub: Subcategoria) => {
  await store.updateSubcategoria(sub.id, { ativo: !sub.ativo })
}

const confirmDelete = (sub: Subcategoria) => { deleting.value = sub; showDelete.value = true }

const submit = async () => {
  try {
    formError.value = ''
    if (editing.value) {
      await store.updateSubcategoria(editing.value.id, { nome: form.nome, cor: form.cor, icone: form.icone })
    } else {
      await store.createSubcategoria({ ...form, ativo: true })
    }
    showForm.value = false
  } catch (e: any) {
    formError.value = e.response?.data?.message ?? 'Erro ao salvar'
  }
}

const doDelete = async () => {
  if (!deleting.value) return
  try {
    await store.deleteSubcategoria(deleting.value.id)
  } catch (e: any) {
    alert(e.response?.data?.message ?? 'Erro ao excluir')
  }
  showDelete.value = false
}

onMounted(async () => {
  await store.fetchCategorias()
  await store.fetchSubcategorias()
})
</script>

<style scoped lang="scss">
.cat-dot { width: 12px; height: 12px; border-radius: 50%; }
.sub-icon {
  width: 36px; height: 36px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0;
}
</style>

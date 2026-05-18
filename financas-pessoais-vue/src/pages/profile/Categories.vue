<!-- resources/js/pages/profile/Categories.vue -->
<template>
  <AppLayout>
    <div class="container-fluid px-3 py-3">
      <div class="d-flex align-items-center mb-3">
        <router-link to="/profile" class="btn btn-sm me-2"><i class="bi bi-arrow-left"></i></router-link>
        <h5 class="mb-0 fw-bold">Categorias</h5>
        <button class="btn btn-sm btn-success ms-auto" @click="openCreate">
          <i class="bi bi-plus-lg me-1"></i> Adicionar
        </button>
      </div>

      <p v-if="!store.categorias.length" class="text-center text-secondary py-4">Nenhuma categoria cadastrada.</p>

      <div v-for="cat in store.categorias" :key="cat.id" class="card-modern mb-2 d-flex align-items-center">
        <div class="cat-icon me-3" :style="{ background: cat.cor + '22', color: cat.cor }">
          <i :class="cat.icone"></i>
        </div>
        <div class="flex-grow-1">
          <div class="fw-semibold">{{ cat.nome }}</div>
          <small class="text-secondary">{{ cat.tipo }} · {{ cat.ativo ? 'Ativo' : 'Inativo' }}</small>
        </div>
        <div class="d-flex gap-2 align-items-center">
          <span v-if="!cat.user_id" class="badge bg-secondary-subtle text-secondary">Sistema</span>
          <template v-else>
            <button class="btn btn-sm btn-outline-warning" @click="toggleAtivo(cat)">
              <i :class="cat.ativo ? 'bi bi-toggle-on' : 'bi bi-toggle-off'"></i>
            </button>
            <button class="btn btn-sm btn-outline-primary" @click="openEdit(cat)"><i class="bi bi-pencil"></i></button>
            <button class="btn btn-sm btn-outline-danger"  @click="confirmDelete(cat)"><i class="bi bi-trash"></i></button>
          </template>
        </div>
      </div>
    </div>

    <!-- Offcanvas form -->
    <div class="offcanvas offcanvas-bottom" :class="{ show: showForm }" tabindex="-1" style="height:auto; max-height:90vh; border-radius:1.25rem 1.25rem 0 0;">
      <div class="offcanvas-header">
        <h6 class="offcanvas-title">{{ editing ? 'Editar' : 'Nova' }} Categoria</h6>
        <button type="button" class="btn-close" @click="showForm = false"></button>
      </div>
      <div class="offcanvas-body pt-0">
        <form @submit.prevent="submit">
          <div class="mb-3">
            <label class="form-label">Nome *</label>
            <input v-model="form.nome" class="form-control" maxlength="100" required />
          </div>
          <div class="mb-3">
            <label class="form-label">Tipo *</label>
            <select v-model="form.tipo" class="form-select" required>
              <option value="DESPESAS">Despesas</option>
              <option value="CREDITO">Crédito / Receita</option>
              <option value="INVESTIMENTOS">Investimentos</option>
            </select>
          </div>
          <div class="mb-3">
            <ColorPicker v-model="form.cor" label="Cor *" />
          </div>
          <div class="mb-3">
            <IconPicker v-model="form.icone" label="Ícone" :preview-color="form.cor" />
          </div>
          <div v-if="formError" class="alert alert-danger py-2">{{ formError }}</div>
          <div class="d-flex gap-2 justify-content-end">
            <button type="button" class="btn btn-outline-secondary" @click="showForm = false">Cancelar</button>
            <button type="submit" class="btn btn-success" :disabled="saving">Salvar</button>
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
            <p>Excluir <strong>{{ deleting?.nome }}</strong>? Não é possível excluir se houver lançamentos vinculados.</p>
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
import { useCategoriaStore, type Categoria } from '@/stores/categoria'

const store      = useCategoriaStore()
const showForm   = ref(false)
const showDelete = ref(false)
const editing    = ref<Categoria | null>(null)
const deleting   = ref<Categoria | null>(null)
const saving     = ref(false)
const formError  = ref('')

const form = reactive({ nome: '', tipo: 'DESPESAS' as string, cor: '#22c55e', icone: 'bi bi-tag' })

const openCreate = () => {
  editing.value = null
  Object.assign(form, { nome: '', tipo: 'DESPESAS', cor: '#22c55e', icone: 'bi bi-tag' })
  showForm.value = true
}

const openEdit = (cat: Categoria) => {
  editing.value = cat
  Object.assign(form, { nome: cat.nome, tipo: cat.tipo, cor: cat.cor, icone: cat.icone })
  showForm.value = true
}

const toggleAtivo = async (cat: Categoria) => {
  await store.updateCategoria(cat.id, { ativo: !cat.ativo })
}

const confirmDelete = (cat: Categoria) => { deleting.value = cat; showDelete.value = true }

const submit = async () => {
  try {
    saving.value    = true
    formError.value = ''
    if (editing.value) {
      await store.updateCategoria(editing.value.id, { ...form })
    } else {
      await store.createCategoria({ ...form, ativo: true, essencial: false })
    }
    showForm.value = false
  } catch (e: any) {
    formError.value = e.response?.data?.message ?? 'Erro ao salvar'
  } finally {
    saving.value = false
  }
}

const doDelete = async () => {
  if (!deleting.value) return
  try {
    await store.deleteCategoria(deleting.value.id)
  } catch (e: any) {
    alert(e.response?.data?.message ?? 'Erro ao excluir')
  }
  showDelete.value = false
}

onMounted(() => store.fetchCategorias())
</script>

<style scoped lang="scss">
.cat-icon {
  width: 40px; height: 40px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0;
}
</style>

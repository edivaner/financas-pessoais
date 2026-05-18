# Finanças Pessoais — Implementation Plan (Phases 7–9)

> **For agentic workers:** REQUIRED SUB-SKILL: Use `superpowers:executing-plans`. Prerequisite: Phases 0–6 complete.

**Goal:** Balanço com gráficos, Perfil/Upload, PWA e Scheduler de reset de limites.

---

## Phase 6: Balanço Geral

### Task 18: Balance.vue + balance store + ApexCharts

**Files:**
- Modify: `resources/js/stores/balance.ts`
- Modify: `resources/js/pages/balance/Balance.vue`

- [ ] **Step 1: Rewrite balance store**

```typescript
// resources/js/stores/balance.ts
import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from 'axios'

export interface BalanceData {
  receitas: number
  despesas: number
  investimentos: number
  balanco: number
  por_categoria: { nome: string; cor: string; total: number }[]
  por_subcategoria: { nome: string; total: number }[]
  gastos_cartao: number
}

export const useBalanceStore = defineStore('balance', () => {
  const lancamentos = ref<any[]>([])
  const loading     = ref(false)
  const mes = ref(new Date().getMonth() + 1)
  const ano = ref(new Date().getFullYear())

  const fetchLancamentos = async () => {
    try {
      loading.value = true
      const { data } = await axios.get('/api/lancamentos', {
        params: { mes: mes.value, ano: ano.value }
      })
      lancamentos.value = data.data
    } finally {
      loading.value = false
    }
  }

  const receitas = computed(() =>
    lancamentos.value.filter(l => l.tipo_lancamento === 'RECEITAS').reduce((s, l) => s + Number(l.valor), 0)
  )

  const despesas = computed(() =>
    lancamentos.value.filter(l => l.tipo_lancamento === 'DESPESAS').reduce((s, l) => s + Number(l.valor), 0)
  )

  const investimentos = computed(() =>
    lancamentos.value.filter(l => l.tipo_lancamento === 'INVESTIMENTOS' && l.tipo_cartao === 'INVESTIR')
      .reduce((s, l) => s + Number(l.valor), 0)
  )

  const balanco = computed(() => receitas.value - despesas.value)

  const gastosCartao = computed(() =>
    lancamentos.value.filter(l => l.tipo_lancamento === 'DESPESAS' && l.cartao_id)
      .reduce((s, l) => s + Number(l.valor), 0)
  )

  const porCategoria = computed(() => {
    const map: Record<string, { nome: string; cor: string; total: number }> = {}
    for (const l of lancamentos.value.filter(l => l.tipo_lancamento === 'DESPESAS' && l.categoria)) {
      const id = l.categoria_id
      if (!map[id]) map[id] = { nome: l.categoria.nome, cor: l.categoria.cor, total: 0 }
      map[id].total += Number(l.valor)
    }
    return Object.values(map).sort((a, b) => b.total - a.total)
  })

  return {
    lancamentos, loading, mes, ano,
    fetchLancamentos,
    receitas, despesas, investimentos, balanco, gastosCartao, porCategoria,
  }
})
```

- [ ] **Step 2: Rewrite Balance.vue**

```vue
<!-- resources/js/pages/balance/Balance.vue -->
<template>
  <AppLayout>
    <div class="container-fluid px-3 py-3">
      <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
          <small class="text-secondary">Relatório</small>
          <h5 class="mb-0 fw-bold">Balanço Geral</h5>
        </div>
        <div class="d-flex gap-2">
          <BFormSelect v-model="store.mes" :options="mesesOptions" style="width:auto" @change="store.fetchLancamentos()" />
          <BFormSelect v-model="store.ano" :options="anosOptions"  style="width:auto" @change="store.fetchLancamentos()" />
        </div>
      </div>

      <div v-if="store.loading" class="text-center py-5">
        <div class="spinner-border text-success"></div>
      </div>

      <template v-else>
        <!-- Totals card -->
        <div class="card-modern mb-4">
          <div class="row g-0">
            <div class="col-6 border-end p-3 text-center">
              <div class="text-secondary small">Entradas</div>
              <div class="fw-bold text-success fs-5">{{ fmt(store.receitas) }}</div>
            </div>
            <div class="col-6 p-3 text-center">
              <div class="text-secondary small">Saídas</div>
              <div class="fw-bold text-danger fs-5">{{ fmt(store.despesas) }}</div>
            </div>
          </div>
          <!-- Bar comparison -->
          <div class="balance-bar mx-3 mb-3">
            <div class="bar-income"  :style="{ flex: store.receitas }"></div>
            <div class="bar-expense" :style="{ flex: store.despesas }"></div>
          </div>
          <div class="d-flex justify-content-between px-3 pb-2">
            <span class="text-secondary"><i class="bi bi-scale me-1"></i>Balanço</span>
            <span class="fw-bold" :class="store.balanco >= 0 ? 'text-success' : 'text-danger'">
              {{ fmt(store.balanco) }}
            </span>
          </div>
        </div>

        <!-- Summary list -->
        <div class="card-modern mb-4">
          <div class="p-3 border-bottom fw-semibold">Entradas</div>
          <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
            <div class="d-flex align-items-center gap-2">
              <div class="sum-icon bg-success-subtle text-success"><i class="bi bi-cash-coin"></i></div>
              <span>Receitas</span>
            </div>
            <span class="text-success fw-semibold">{{ fmt(store.receitas) }}</span>
          </div>
          <div class="p-3 border-bottom fw-semibold">Saídas</div>
          <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
            <div class="d-flex align-items-center gap-2">
              <div class="sum-icon bg-danger-subtle text-danger"><i class="bi bi-cash-stack"></i></div>
              <span>Gastos</span>
            </div>
            <span class="text-danger fw-semibold">{{ fmt(store.despesas) }}</span>
          </div>
          <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
            <div class="d-flex align-items-center gap-2">
              <div class="sum-icon bg-warning-subtle text-warning"><i class="bi bi-credit-card"></i></div>
              <span>Gastos cartão</span>
            </div>
            <span class="text-warning fw-semibold">{{ fmt(store.gastosCartao) }}</span>
          </div>
          <div class="d-flex justify-content-between align-items-center p-3">
            <div class="d-flex align-items-center gap-2">
              <div class="sum-icon bg-purple-subtle text-purple"><i class="bi bi-graph-up-arrow"></i></div>
              <span>Investimentos</span>
            </div>
            <span class="text-purple fw-semibold">{{ fmt(store.investimentos) }}</span>
          </div>
        </div>

        <!-- Gastos por categoria donut -->
        <div class="card-modern mb-4" v-if="store.porCategoria.length">
          <div class="p-3 border-bottom fw-semibold">Gastos por Categoria</div>
          <div class="p-3">
            <VueApexCharts
              type="donut"
              height="280"
              :options="donutOptions"
              :series="categorySeries"
            />
          </div>
          <div v-for="cat in store.porCategoria" :key="cat.nome" class="d-flex justify-content-between px-3 pb-2">
            <div class="d-flex align-items-center gap-2">
              <div class="dot" :style="{ background: cat.cor }"></div>
              <span class="small">{{ cat.nome }}</span>
            </div>
            <span class="small fw-semibold">{{ fmt(cat.total) }}</span>
          </div>
        </div>

        <!-- Entrada x Saída bar chart -->
        <div class="card-modern mb-4">
          <div class="p-3 border-bottom fw-semibold">Entradas x Saídas</div>
          <div class="p-3">
            <VueApexCharts
              type="bar"
              height="200"
              :options="barOptions"
              :series="barSeries"
            />
          </div>
        </div>
      </template>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { computed, onMounted } from 'vue'
import VueApexCharts from 'vue3-apexcharts'
import AppLayout from '@/components/layouts/AppLayout.vue'
import { useBalanceStore } from '@/stores/balance'

const store = useBalanceStore()

const fmt = (v: number) =>
  new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(v ?? 0)

const mesesOptions = [
  { value: 1, text: 'Janeiro' }, { value: 2, text: 'Fevereiro' },
  { value: 3, text: 'Março' },   { value: 4, text: 'Abril' },
  { value: 5, text: 'Maio' },    { value: 6, text: 'Junho' },
  { value: 7, text: 'Julho' },   { value: 8, text: 'Agosto' },
  { value: 9, text: 'Setembro' },{ value: 10, text: 'Outubro' },
  { value: 11, text: 'Novembro' },{ value: 12, text: 'Dezembro' },
]

const anosOptions = [2023, 2024, 2025, 2026].map(a => ({ value: a, text: String(a) }))

const categorySeries = computed(() => store.porCategoria.map(c => c.total))

const donutOptions = computed(() => ({
  labels: store.porCategoria.map(c => c.nome),
  colors: store.porCategoria.map(c => c.cor),
  legend: { position: 'bottom' },
  dataLabels: { enabled: false },
  chart: { background: 'transparent' },
  theme: { mode: document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'dark' : 'light' },
}))

const barSeries = computed(() => ([
  { name: 'Entradas', data: [store.receitas] },
  { name: 'Saídas',   data: [store.despesas] },
]))

const barOptions = {
  chart: { toolbar: { show: false }, background: 'transparent' },
  plotOptions: { bar: { horizontal: false, columnWidth: '40%', borderRadius: 6 } },
  colors: ['#22c55e', '#ef4444'],
  xaxis: { categories: ['Mês atual'] },
  dataLabels: { enabled: false },
  legend: { position: 'top' },
}

onMounted(() => store.fetchLancamentos())
</script>

<style scoped lang="scss">
.balance-bar {
  height: 8px; border-radius: 4px; display: flex; overflow: hidden; gap: 2px;
}
.bar-income  { background: #22c55e; border-radius: 4px 0 0 4px; min-width: 4px; }
.bar-expense { background: #ef4444; border-radius: 0 4px 4px 0; min-width: 4px; }

.sum-icon {
  width: 32px; height: 32px; border-radius: 0.5rem;
  display: flex; align-items: center; justify-content: center; font-size: .85rem;
}

.dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }

.text-purple       { color: #a855f7; }
.bg-purple-subtle  { background: rgba(168,85,247,.12); }
</style>
```

- [ ] **Step 3: Verify in browser**

Navigate to `/balance` → select month → verify:
- Summary cards show correct totals
- Donut chart renders (if despesas with categorias exist)
- Bar chart renders
- Month/year selects change data

- [ ] **Step 4: Commit**

```bash
git add resources/js/stores/balance.ts resources/js/pages/balance/Balance.vue
git commit -m "feat: Balance page with ApexCharts donut + bar charts"
```

---

## Phase 7: Imagens + Profile

### Task 19: ImagemService + ImagemController

**Files:**
- Create: `app/Services/ImagemService.php`
- Create: `app/Http/Controllers/ImagemController.php`

- [ ] **Step 1: Create ImagemService**

```php
<?php
// app/Services/ImagemService.php
namespace App\Services;

use App\Domain\Common\TipoImagem;
use App\Models\Imagem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImagemService
{
    public function store(UploadedFile $file, string $tipo): Imagem
    {
        $path = $file->store('imagens/' . strtolower($tipo), 'public');

        return Imagem::create([
            'id'          => Str::uuid(),
            'caminho'     => Storage::url($path),
            'tipo_imagem' => $tipo,
        ]);
    }

    public function delete(string $id): void
    {
        $imagem = Imagem::findOrFail($id);
        // Extract relative path from URL
        $relativePath = str_replace('/storage/', '', $imagem->caminho);
        Storage::disk('public')->delete($relativePath);
        $imagem->delete();
    }
}
```

- [ ] **Step 2: Create ImagemController**

```php
<?php
// app/Http/Controllers/ImagemController.php
namespace App\Http\Controllers;

use App\Services\ImagemService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ImagemController extends Controller
{
    public function __construct(private ImagemService $service) {}

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'imagem'    => 'required|image|max:5120', // 5MB
            'tipo'      => 'required|in:LOGO,LANCAMENTO',
        ]);

        $imagem = $this->service->store($request->file('imagem'), $request->tipo);

        return response()->json(['data' => $imagem, 'message' => 'Imagem enviada.'], 201);
    }
}
```

- [ ] **Step 3: Ensure storage link exists**

```bash
docker compose exec laravel-app php artisan storage:link
```

Expected: symlink created at `public/storage`.

- [ ] **Step 4: Commit**

```bash
git add app/Services/ImagemService.php app/Http/Controllers/ImagemController.php
git commit -m "feat: ImagemService + ImagemController for file upload"
```

---

### Task 20: Auth pages — Login.vue + Register.vue

**Files:**
- Modify: `resources/js/pages/auth/Login.vue`
- Modify: `resources/js/pages/auth/Register.vue`

- [ ] **Step 1: Rewrite Login.vue**

```vue
<!-- resources/js/pages/auth/Login.vue -->
<template>
  <div class="auth-page">
    <div class="auth-card">
      <div class="text-center mb-4">
        <i class="bi bi-currency-dollar text-success" style="font-size:3rem"></i>
        <h4 class="fw-bold mt-2">Finanças Pessoais</h4>
        <p class="text-secondary">Entre na sua conta</p>
      </div>

      <BForm @submit.prevent="submit">
        <div class="mb-3">
          <label class="form-label">E-mail</label>
          <BFormInput v-model="form.email" type="email" required autocomplete="email" placeholder="seu@email.com" />
        </div>
        <div class="mb-3">
          <label class="form-label">Senha</label>
          <BFormInput v-model="form.password" type="password" required autocomplete="current-password" placeholder="••••••••" />
        </div>

        <div v-if="error" class="alert alert-danger py-2 mb-3">{{ error }}</div>

        <BButton variant="success" type="submit" class="w-100" :disabled="store.loading.value">
          <span v-if="store.loading.value" class="spinner-border spinner-border-sm me-2"></span>
          Entrar
        </BButton>
      </BForm>

      <div class="text-center mt-3">
        <small class="text-secondary">
          Não tem conta?
          <router-link to="/register" class="text-success fw-semibold">Cadastre-se</router-link>
        </small>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const store  = useAuthStore()
const router = useRouter()
const error  = ref('')

const form = reactive({ email: '', password: '' })

const submit = async () => {
  error.value = ''
  const result = await store.login(form.email, form.password)
  if (result.success) {
    router.push('/')
  } else {
    error.value = result.message ?? 'Erro ao fazer login'
  }
}
</script>

<style scoped lang="scss">
.auth-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--bs-body-bg);
  padding: 1rem;
}

.auth-card {
  width: 100%;
  max-width: 400px;
  background: var(--bs-card-bg, #fff);
  border-radius: 1.25rem;
  border: 1px solid var(--bs-border-color);
  padding: 2rem;
}
</style>
```

- [ ] **Step 2: Rewrite Register.vue**

```vue
<!-- resources/js/pages/auth/Register.vue -->
<template>
  <div class="auth-page">
    <div class="auth-card">
      <div class="text-center mb-4">
        <i class="bi bi-currency-dollar text-success" style="font-size:3rem"></i>
        <h4 class="fw-bold mt-2">Criar conta</h4>
        <p class="text-secondary">Comece a controlar suas finanças</p>
      </div>

      <BForm @submit.prevent="submit">
        <div class="row g-2 mb-3">
          <div class="col-6">
            <label class="form-label">Nome *</label>
            <BFormInput v-model="form.nome" required placeholder="João" />
          </div>
          <div class="col-6">
            <label class="form-label">Sobrenome</label>
            <BFormInput v-model="form.sobrenome" placeholder="Silva" />
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label">E-mail *</label>
          <BFormInput v-model="form.email" type="email" required placeholder="seu@email.com" />
        </div>
        <div class="mb-3">
          <label class="form-label">Senha *</label>
          <BFormInput v-model="form.password" type="password" required placeholder="Mínimo 8 caracteres" />
        </div>
        <div class="mb-3">
          <label class="form-label">Confirmar senha *</label>
          <BFormInput v-model="form.password_confirmation" type="password" required placeholder="Repita a senha" />
        </div>

        <div v-if="error" class="alert alert-danger py-2 mb-3">{{ error }}</div>

        <BButton variant="success" type="submit" class="w-100" :disabled="store.loading.value">
          <span v-if="store.loading.value" class="spinner-border spinner-border-sm me-2"></span>
          Criar conta
        </BButton>
      </BForm>

      <div class="text-center mt-3">
        <small class="text-secondary">
          Já tem conta?
          <router-link to="/login" class="text-success fw-semibold">Entrar</router-link>
        </small>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const store  = useAuthStore()
const router = useRouter()
const error  = ref('')

const form = reactive({
  nome: '', sobrenome: '', email: '', password: '', password_confirmation: ''
})

const submit = async () => {
  error.value = ''
  if (form.password !== form.password_confirmation) {
    error.value = 'As senhas não conferem'
    return
  }
  const result = await store.register(form)
  if (result.success) {
    router.push('/')
  } else {
    error.value = result.message ?? 'Erro ao criar conta'
  }
}
</script>

<style scoped lang="scss">
.auth-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--bs-body-bg);
  padding: 1rem;
}

.auth-card {
  width: 100%;
  max-width: 420px;
  background: var(--bs-card-bg, #fff);
  border-radius: 1.25rem;
  border: 1px solid var(--bs-border-color);
  padding: 2rem;
}
</style>
```

- [ ] **Step 3: Verify in browser**

Open `http://localhost/register`:
- Fill form → submit → redirected to dashboard
- Open `http://localhost/login`:
- Fill credentials → submit → redirected to dashboard
- Enter wrong password → error message shown

- [ ] **Step 4: Commit**

```bash
git add resources/js/pages/auth/Login.vue resources/js/pages/auth/Register.vue
git commit -m "feat: complete Login + Register pages, no OTP"
```

---

### Task 21: Subcategories.vue

**Files:**
- Create: `resources/js/pages/profile/Subcategories.vue`

- [ ] **Step 1: Create Subcategories.vue**

```vue
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

      <!-- Group by categoria -->
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
          <div class="d-flex gap-2">
            <button class="btn btn-sm btn-outline-warning" @click="toggleAtivo(sub)">
              <i :class="sub.ativo ? 'bi bi-toggle-on' : 'bi bi-toggle-off'"></i>
            </button>
            <button class="btn btn-sm btn-outline-primary" @click="openEdit(sub)"><i class="bi bi-pencil"></i></button>
            <button class="btn btn-sm btn-outline-danger" @click="confirmDelete(sub)"><i class="bi bi-trash"></i></button>
          </div>
        </div>

        <p v-if="!subsByCategoria(cat.id).length" class="text-secondary small ms-2">
          Nenhuma subcategoria.
        </p>
      </div>
    </div>

    <BOffcanvas v-model="showForm" placement="bottom">
      <template #header><h6 class="mb-0">{{ editing ? 'Editar' : 'Nova' }} Subcategoria</h6></template>
      <BForm @submit.prevent="submit" class="p-2">
        <div class="mb-3"><label class="form-label">Categoria *</label>
          <BFormSelect v-model="form.categoria_id" :options="catOptions" required />
        </div>
        <div class="mb-3"><label class="form-label">Nome *</label><BFormInput v-model="form.nome" required /></div>
        <div class="mb-3"><label class="form-label">Cor</label>
          <input type="color" class="form-control form-control-color" v-model="form.cor" />
        </div>
        <div class="mb-3"><label class="form-label">Ícone</label>
          <BFormInput v-model="form.icone" placeholder="bi bi-tag" />
        </div>
        <div v-if="formError" class="alert alert-danger py-2">{{ formError }}</div>
        <div class="d-flex gap-2 justify-content-end">
          <BButton variant="outline-secondary" @click="showForm = false">Cancelar</BButton>
          <BButton variant="success" type="submit">Salvar</BButton>
        </div>
      </BForm>
    </BOffcanvas>

    <BModal v-model="showDelete" title="Confirmar exclusão" @ok="doDelete">
      <p>Excluir <strong>{{ deleting?.nome }}</strong>?</p>
    </BModal>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue'
import AppLayout from '@/components/layouts/AppLayout.vue'
import { useCategoriaStore, type Subcategoria } from '@/stores/categoria'

const store      = useCategoriaStore()
const showForm   = ref(false)
const showDelete = ref(false)
const editing    = ref<Subcategoria | null>(null)
const deleting   = ref<Subcategoria | null>(null)
const formError  = ref('')

const catOptions = computed(() => [
  { value: '', text: 'Selecione' },
  ...store.categorias.map(c => ({ value: c.id, text: c.nome }))
])

const form = reactive({ categoria_id: '', nome: '', cor: '#22c55e', icone: 'bi bi-tag' })

const subsByCategoria = (catId: string) =>
  store.subcategorias.filter(s => s.categoria_id === catId)

const openCreate = () => {
  editing.value = null
  Object.assign(form, { categoria_id: '', nome: '', cor: '#22c55e', icone: 'bi bi-tag' })
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
```

- [ ] **Step 2: Verify in browser**

Navigate to `/profile/subcategories`. Verify: grouped by categoria, create/edit/toggle/delete works.

- [ ] **Step 3: Commit**

```bash
git add resources/js/pages/profile/Subcategories.vue
git commit -m "feat: Subcategories page with grouping by categoria"
```

---

## Phase 8: PWA + Scheduler

### Task 22: ResetMonthlyLimitsCommand + Scheduler

**Files:**
- Create: `app/Console/Commands/ResetMonthlyLimitsCommand.php`
- Modify: `routes/console.php`

- [ ] **Step 1: Create Command**

```php
<?php
// app/Console/Commands/ResetMonthlyLimitsCommand.php
namespace App\Console\Commands;

use App\Services\LimiteService;
use Illuminate\Console\Command;

class ResetMonthlyLimitsCommand extends Command
{
    protected $signature   = 'limits:reset';
    protected $description = 'Reset valor_gasto_atual de todos os limites (executar dia 01)';

    public function __construct(private LimiteService $limiteService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->limiteService->resetMonthly();
        $this->info('Limites resetados com sucesso.');
        return Command::SUCCESS;
    }
}
```

- [ ] **Step 2: Register in Scheduler**

```php
<?php
// routes/console.php
use Illuminate\Support\Facades\Schedule;
use App\Console\Commands\ResetMonthlyLimitsCommand;

Schedule::command(ResetMonthlyLimitsCommand::class)
    ->monthlyOn(1, '00:05') // dia 01 às 00:05
    ->timezone('America/Sao_Paulo');
```

- [ ] **Step 3: Test command manually**

```bash
docker compose exec laravel-app php artisan limits:reset
```

Expected: `Limites resetados com sucesso.`

- [ ] **Step 4: Verify scheduler registration**

```bash
docker compose exec laravel-app php artisan schedule:list
```

Expected: `limits:reset` listed monthly on day 1.

- [ ] **Step 5: Commit**

```bash
git add app/Console/Commands/ResetMonthlyLimitsCommand.php routes/console.php
git commit -m "feat: limits:reset command + monthly scheduler on day 01"
```

---

### Task 23: PWA verification + final polish

**Files:**
- Verify: `vite.config.ts` (PWA manifest already configured)
- Verify: `resources/js/pwa.ts`

- [ ] **Step 1: Verify pwa.ts is imported in app.ts**

In `resources/js/app.ts`, ensure `import './pwa'` is present or add it after bootstrap imports.

```typescript
// Add to resources/js/app.ts (after other imports)
import './pwa'
```

- [ ] **Step 2: Build and check manifest**

```bash
docker compose exec npm npm run build
```

Expected: build succeeds, `public/manifest.webmanifest` generated.

- [ ] **Step 3: Verify service worker in production build**

Open browser devtools → Application tab → Service Workers → verify SW is registered.  
Manifest tab → verify name, theme_color, icons.

- [ ] **Step 4: Run all backend tests**

```bash
docker compose exec laravel-app php artisan test
```

Expected: all tests pass.

- [ ] **Step 5: Final commit**

```bash
git add resources/js/app.ts
git commit -m "feat: PWA service worker registered, all tests passing"
```

---

## Self-Review — Spec Coverage Check

| Spec requirement | Task |
|-----------------|------|
| Auth email+senha, Sanctum token | Task 6 |
| Contas CRUD, saldo, somar_tela_inicial | Tasks 7–8 |
| Cartões CRUD, fatura_total | Tasks 9 |
| Lançamento: Receita (+saldo) | Task 15 |
| Lançamento: Despesa (-saldo / cartão débito / cartão crédito fatura) | Task 15 |
| Lançamento: Transferência (-origem, +destino / para_saldo_investido) | Task 15 |
| Lançamento: Investimento (INVESTIR / RESGATAR) | Task 15 |
| Parcelamento: N lancamentos, valor/N, +30d | Task 15 |
| esta_pago = data <= hoje | Task 15 |
| Reversão na exclusão | Task 15 |
| Reversão na edição | Task 15 |
| Categorias CRUD, ativo, não deletar se tem lancamentos | Tasks 11–12 |
| Subcategorias CRUD, agrupadas por categoria | Tasks 11, 21 |
| Limites: valor_gasto_atual auto-update em despesas | Tasks 11, 15 |
| Limites: cores <70% verde, 70-99% amarelo, >=100% vermelho | LimitBar component |
| Reset limites dia 01 | Task 22 |
| Dashboard: StatCards 6 totais | Task 14 |
| Dashboard: contas somar_tela_inicial=true | Tasks 13–14 |
| Dashboard: cartões com fatura + limite | Tasks 13–14 |
| Dashboard: limites com LimitBar | Tasks 13–14 |
| Transactions: carrossel de meses | Task 17 |
| Transactions: filtros Geral/Cartões/Conta | Task 17 |
| Transactions: badge parcelamento | Task 17 |
| Transactions: rodapé sticky gastos/receitas/balanço | Task 17 |
| Balance: gráfico donut por categoria | Task 18 |
| Balance: gráfico barras entradas x saídas | Task 18 |
| Profile: dark mode | Tasks 3, 12 |
| Profile: moeda (visual only) | Task 12 |
| Profile: links para CRUDs | Task 12 |
| BottomNav: 5 itens + FAB central verde | Task 4 |
| PWA: manifest, service worker | Task 23 |
| Scheduler: limits:reset mensal | Task 22 |
| Imagens upload | Task 19 |
| Docker: laravel-app + mysql + npm/vite | Already exists |

**Gaps found and resolved:**
- `para_saldo_investido` flag: migration added in Task 15 ✓
- `TipoInvestimento` enum bug fixed in Task 2 ✓
- `Otp.vue` and OTP routes removed (never created/used) ✓
- `simulado` field ignored in all logic ✓

---

## Execution Options

Plan complete and saved to three files:
- `docs/superpowers/plans/2026-05-16-financas-pessoais-phase0-2.md` (Tasks 1–10)
- `docs/superpowers/plans/2026-05-16-financas-pessoais-phase3-6.md` (Tasks 11–17)
- `docs/superpowers/plans/2026-05-16-financas-pessoais-phase7-9.md` (Tasks 18–23)

**Two execution options:**

**1. Subagent-Driven (recommended)** — dispatch fresh subagent per task, review between tasks, fast iteration

**2. Inline Execution** — execute tasks in this session using executing-plans, batch execution with checkpoints

Which approach?

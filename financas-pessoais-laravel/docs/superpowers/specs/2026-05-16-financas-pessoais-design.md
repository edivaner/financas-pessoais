# Design — Sistema Finanças Pessoais

**Data:** 2026-05-16  
**Projeto:** Laravel 11 + Vue 3 — SaaS Finanças Pessoais  
**Abordagem:** Full-stack por módulo (cada fase funciona end-to-end)

---

## 1. Visão Geral

Sistema financeiro pessoal monolítico (Laravel 11 backend + Vue 3 frontend no mesmo projeto).  
Funciona como PWA — web responsivo e instalável como app mobile.

**Removido do escopo:**
- Saldo Simulado: campo `simulado` permanece em banco mas é ignorado em toda lógica e UI
- OTP/email: autenticação só com email + senha; OtpCode model e actions não são usados

---

## 2. Arquitetura

### Backend — Service/Repository

```
app/
  Http/
    Controllers/     thin — chama só Service, sem lógica de negócio
    Requests/        Form Requests (validação de entrada)
  Services/          regras de negócio, orquestração
  Repositories/      acesso a dados via Eloquent
  Models/            Eloquent models (já existem)
  Domain/Common/     Enums (já existem)
  Actions/           conteúdo migrado para Services; pasta não usada
```

**Regra:** Controller → Service → Repository → Model. Nenhum Controller toca Repository ou Model diretamente.

### Frontend — Vue 3 + Bootstrap 5

```
resources/js/
  pages/
    auth/          Login.vue, Register.vue  (Otp.vue removida)
    home/          Dashboard.vue
    transactions/  Transactions.vue
    balance/       Balance.vue
    profile/       Profile.vue, Accounts.vue, Cards.vue,
                   Categories.vue, Subcategories.vue, Limits.vue
  components/
    layouts/       AppLayout.vue
    ui/            BottomNav.vue, StatCard.vue, SectionCard.vue,
                   LimitBar.vue, BaseHeader.vue
    modals/        TransactionModal.vue, ProfileEditModal.vue,
                   PasswordChangeModal.vue, AccountsManagementModal.vue,
                   CardsManagementModal.vue, CategoriesManagementModal.vue,
                   SubcategoriesManagementModal.vue, LimitsManagementModal.vue
    forms/         AccountForm.vue, CardForm.vue
  stores/          auth, dashboard, transaction, balance, ui (Pinia)
  router/          index.ts (paths corrigidos)
  theme.scss       cores, dark mode, variáveis globais
```

---

## 3. Banco de Dados

- MySQL com `decimal(19,4)` para valores monetários
- UUIDs (`char(36)`) como chave primária em todas as tabelas
- Multi-tenant: `user_id` em todas as entidades de domínio
- Todas as migrations já existem — apenas rodar `migrate`
- SoftDeletes em Lancamentos

### Entidades e relacionamentos

```
User 1──* Conta
User 1──* Cartao
User 1──* Categoria
User 1──* Subcategoria (pertence a Categoria)
User 1──* Limite (pertence a Categoria)
User 1──* Lancamento
Conta 1──* Cartao (conta vinculada ao cartão)
Lancamento *──1 Conta (origem)
Lancamento *──0..1 Conta (destino)
Lancamento *──0..1 Cartao
Lancamento *──0..1 Categoria
Lancamento *──0..1 Subcategoria
Lancamento *──0..1 Imagem
Conta *──0..1 Imagem (LOGO)
Cartao *──0..1 Imagem (LOGO)
User *──0..1 Imagem (avatar)
```

---

## 4. API — Padrão de resposta

```json
// Sucesso
{ "data": {...}, "message": "ok" }

// Validação (422)
{ "message": "...", "errors": { "campo": ["..."] } }

// Não autorizado (401)
{ "message": "Não autorizado" }
```

Todas as rotas protegidas via `auth:sanctum`, exceto `/auth/register` e `/auth/login`.

### Endpoints

```
POST   /api/auth/register
POST   /api/auth/login
POST   /api/auth/logout
GET    /api/auth/user

GET    /api/dashboard

GET    /api/contas
POST   /api/contas
GET    /api/contas/{id}
PUT    /api/contas/{id}
DELETE /api/contas/{id}

GET    /api/cartoes
POST   /api/cartoes
GET    /api/cartoes/{id}
PUT    /api/cartoes/{id}
DELETE /api/cartoes/{id}

GET    /api/categorias
POST   /api/categorias
PUT    /api/categorias/{id}
DELETE /api/categorias/{id}

GET    /api/subcategorias
POST   /api/subcategorias
PUT    /api/subcategorias/{id}
DELETE /api/subcategorias/{id}

GET    /api/limites
POST   /api/limites
PUT    /api/limites/{id}
DELETE /api/limites/{id}

GET    /api/lancamentos?mes=&ano=&conta_id=&cartao_id=&tipo=
POST   /api/lancamentos
GET    /api/lancamentos/{id}
PUT    /api/lancamentos/{id}
DELETE /api/lancamentos/{id}

POST   /api/imagens
```

---

## 5. Sequência de Módulos (build order)

| Fase | Módulo | Backend | Frontend |
|------|--------|---------|----------|
| 0 | Fundação | — | AppLayout, BottomNav, StatCard, SectionCard, LimitBar, BaseHeader; corrigir router |
| 1 | Auth | AuthController limpo, sem OTP | Login.vue, Register.vue integrados; auth store; interceptor Axios 401 |
| 2 | Contas | ContaRepository, ContaService, ContaController, StoreContaRequest, UpdateContaRequest | AccountForm, AccountsManagementModal, Accounts.vue |
| 3 | Cartões | CartaoRepository, CartaoService, CartaoController, StoreCartaoRequest, UpdateCartaoRequest | CardForm, CardsManagementModal, Cards.vue |
| 4 | Categorias + Subcategorias + Limites | Repository+Service+Controller+Request para cada; seeders | CategoriesManagementModal, SubcategoriesManagementModal, LimitsManagementModal |
| 5 | Dashboard | DashboardService, DashboardController completo | Dashboard.vue: StatCards, seções contas/cartões/limites com dados reais |
| 6 | Lançamentos | LancamentoRepository, LancamentoService (migrar Actions), LancamentoController, StoreLancamentoRequest | TransactionModal completo, Transactions.vue com filtros e rodapé sticky |
| 7 | Balanço | — (dados da API lancamentos) | Balance.vue + ApexCharts (npm i vue3-apexcharts) |
| 8 | Perfil | ImagemService para upload | Profile.vue, ProfileEditModal, dark mode toggle, troca de moeda (visual only) |
| 9 | PWA + polish | Scheduler: limits:reset todo dia 01 | Confirmar manifest, service worker, ícones |

---

## 6. Regras de Negócio Críticas

### Auth
- Register: nome, email, senha — retorna token Sanctum
- Login: email + senha — retorna token Sanctum
- Token armazenado em localStorage via store `auth`

### Contas
- `somar_tela_inicial`: se false, não aparece no Dashboard
- Exclusão: confirmar via modal; excluir lançamentos em cascata
- `carteira`: boolean para dinheiro físico

### Cartões
- `tipo_cartao`: CREDITO, DEBITO, MULTIPLO
- Sempre vinculados a uma Conta
- `fatura_total` atualizado automaticamente pelos lançamentos
- Débito: debita `saldo` da conta vinculada
- Crédito: soma em `fatura_total`

### Lançamentos
- `esta_pago = data <= hoje` (calculado na criação, não editável)
- Parcelamento: gera N registros; `valor / N` cada; datas +30 dias sequenciais; `parcela_atual` de 1 a N
- Editáveis: conta_origem, conta_destino, valor, data, descrição, categoria_id, subcategoria_id (categoria não afeta saldo, apenas classificação)
- NÃO editáveis: tipo_lancamento, parcela_total, parcela_atual, cartao_id, tipo_cartao
- Reversão obrigatória na exclusão e edição (desfaz efeitos de saldo antes de reaplicar)

**Receita:**
- Sem cartão: `conta_origem.saldo += valor`

**Despesa:**
- Sem cartão: `conta_origem.saldo -= valor`
- Cartão débito: `cartao.conta.saldo -= valor`
- Cartão crédito: `cartao.fatura_total += valor`

**Transferência:**
- `conta_origem.saldo -= valor`
- `conta_destino.saldo += valor`
- Transferência para saldo investido (`para_saldo_investido: true` no payload): `conta_origem.saldo -= valor` + `conta_origem.saldo_investido += valor` (não usa conta_destino)

**Investimento:**
- INVESTIR: `conta.saldo_investido += valor` (origem fornece o dinheiro — debitar `conta_origem.saldo`)
- RESGATAR: `conta.saldo_investido -= valor` + `conta.saldo += valor`

### Categorias / Subcategorias
- `ativo = false`: não aparecem em selects de lançamento
- Não deletar se vinculada a lançamentos existentes

### Limites
- Cada despesa com categoria: `limite.valor_gasto_atual += valor`
- Cores: <70% verde, 70–99% amarelo, ≥100% vermelho
- Reset mensal: `valor_gasto_atual = 0` todo dia 01 via Scheduler (sem apagar histórico)

### Imagens
- LOGO: usada em Conta, Cartão, avatar do User
- LANCAMENTO: usada como comprovante no Lançamento

---

## 7. Frontend — Componentes e UX

### AppLayout + BottomNav
- BottomNav fixo no rodapé: Home, Lançamentos, botão `+` (verde, redondo), Balanço, Perfil
- Desktop: sidebar lateral (opcional) ou BottomNav adaptado

### StatCard
- 2 colunas no mobile e desktop
- Cards: Saldo Atual, Receitas, Despesas, Pago, Pendente, Saldo Investido

### LimitBar
- Barra de progresso com cor dinâmica: verde/amarelo/vermelho

### TransactionModal
- Bottom sheet no mobile
- Seleção de tipo: Receita, Despesa, Transferência, Investimento
- Formulário dinâmico por tipo
- Suporte a parcelamento (só Despesa com cartão crédito)
- Upload de imagem comprovante

### Transactions.vue
- Carrossel de meses (passado ↔ futuro, mês atual selecionado)
- Chips de filtro: Geral, Cartões, Conta
- Tipo de saldo: Saldo, Investido (Simulado removido)
- Lista ordenada decrescente por data
- Badge de parcelamento: "Parcelado 2/3"
- Rodapé sticky expansível: Gastos, Pagamentos, Balanço total

### Balance.vue
- Cards resumo: Entradas, Saídas, Balanço
- Gráficos ApexCharts: donut para categoria/subcategoria; barras para entrada/saída/investimento

### Dark Mode
- Controlado por store `ui` (Pinia, persiste em localStorage)
- Aplicado via `data-bs-theme="dark"` no `<html>` em `App.vue`
- Bootstrap 5 nativo — sem CSS extra

### Upload de Imagens
- `POST /api/imagens` multipart/form-data
- Storage em `storage/app/public`
- URL pública via `Storage::url()`

---

## 8. Configuração e Infra

### Docker
`docker-compose.yml` já existe com `laravel-app` (PHP 8.3), `mysql`, `npm/vite`. Sem mudanças.

### Scheduler (Laravel)
```php
// routes/console.php
Schedule::command('limits:reset')->monthly(); // dia 01
```

### Axios — interceptor global
```ts
axios.interceptors.request.use(config => {
  const token = authStore.token
  if (token) config.headers.Authorization = `Bearer ${token}`
  return config
})
axios.interceptors.response.use(null, error => {
  if (error.response?.status === 401) router.push('/login')
  return Promise.reject(error)
})
```

### Packages a instalar
```bash
npm i vue3-apexcharts apexcharts
```

---

## 9. O que NÃO entra neste escopo

- Saldo Simulado (removido por decisão do usuário)
- OTP / confirmação por email (removido por decisão do usuário)
- Multi-perfil / planos premium (prints do app de referência mostram isso, mas não está nos requisitos)
- Notificações push
- Integração com bancos externos

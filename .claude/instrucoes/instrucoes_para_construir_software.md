# 🚀 Prompt

Você agora atuará como um engenheiro de software senior.
Quero que você crie um sistema financeiro SaaS com as seguintes características: Siga exatamente as instruções de arquitetura, nomes de arquivos e conteúdo. Se algum arquivo já existir, atualize sem quebrar o restante.

Esse projeto já está iniciado, quero que você complete o que esta faltando e remova o que não esta nas descrições
e requisitos, e altere o que não esta de acordo com estas especificações

Regras de negócio: Obedecer às especificações enviadas pelo usuário (parcelamento, estaPago, datas futuras, simulado, saldos, limites mensais, cartões crédito/débito, investimentos, transferência entre contas e/ou para saldoInvestido, reversão em edição/exclusão, imagens de LOGO e LANCAMENTO etc.)
Você pode usar como referência os seguintes arquivos:
- Requisitos `.claude\instrucoes\Requisitos\requisitos_software.md`
- Diagrama de classe: `.claude\instrucoes\DiagramaDeClasse\diagrama-financeiro.jpg`

Toda vez que tiver dúvida, durante o desenvolvimento, você deve voltar aqui e ler as instruções, requisitos e o diagrama.

---

## 🔹 Estrutura Geral

- Projeto em **Laravel 11 monolítico** (backend + frontend no mesmo projeto)
- Frontend desenvolvido com **Vue 3**
- Utilizar **BootstrapVue** como base dos componentes
    - UI: Bootstrap 5 + bootstrap-vue-next (Vue 3).


- Estilização adicional com:
  - **Bootstrap 5**
  - `theme.scss` para centralização de:
    - cores
    - fontes
    - estilos globais
    - variáveis de design

- Aplicar arquitetura limpa utilizando padrão **Service/Repository**
- Separar corretamente:
  - regras de negócio
  - persistência
  - apresentação

- O sistema deve funcionar como:
  - aplicação web
  - mobile por responsividade.

- Toda a aplicação deve ser:
  - responsiva
  - baseada nos prints/referências fornecidas na pasta `\.claude\instrucoes\ImagensExemploFront`

- O layout desktop pode possuir aparência:
  - moderna
  - tecnológica
  - sofisticada

Mantendo:
- identidade visual
- paleta de cores
- consistência de UX

---

## 🔹 Banco de Dados

- Banco de dados: **MySQL**
- Ambiente totalmente containerizado com **Docker**
- Banco: MySQL com decimals (19,4) para dinheiro.
- IDs: UUID (char(36)), ordered-uuid quando possível.
- Multi-tenant (fase 1): isolamento por user_id em todas as entidades de domínio.

Criar `docker-compose.yml` contendo:

```yml
services:
  - laravel-app (PHP 8.3)
  - mysql
  - npm/vite
```

---

## 🔹 Frontend
**Referências no arquivos `\.claude\instrucoes\ImagensExemploFront`**

### Tecnologias

- Vue 3
- BootstrapVue
- Bootstrap

---

### Tema Global

Criar o arquivo:

```bash
resources/js/theme.scss
```

Responsável por centralizar:

- cores
- tipografia
- espaçamentos
- sombras
- estilos globais
- componentes reutilizáveis

Importar o `theme.scss` globalmente em toda a aplicação.

---

### Layout e UX

As telas de cadastro devem seguir um padrão visual consistente:

- cadastro de cartão
- cadastro de conta
- cadastro de categorias
- cadastro de movimentações

Todos os formulários devem manter:

- mesma estrutura visual
- mesma experiência de uso
- padronização de componentes

---

### Mobile

O layout mobile deve seguir exatamente os exemplos dos prints fornecidos como referências.

---

### Web

O layout para web pode ser mais moderno e tecnológico, porém mantendo:

- mesmas cores
- identidade visual
- consistência de experiência

---

## 🔹 Backend

### Arquitetura

Utilizar **Laravel 11** com padrão **Service/Repository**.

---

### Repositories

Responsáveis exclusivamente por:

- persistência de dados
- consultas
- manipulação via Eloquent ou Query Builder

---

### Services

Responsáveis por:

- regras de negócio
- validações
- orquestração de processos
- integração entre camadas

---

### Controllers

Os Controllers devem:

- chamar apenas os Services
- não conter lógica de negócio

---

## 🔹 API

Criar API interna para consumo do frontend Vue.

---

## 🔹 Estrutura Laravel

Implementar:

- Form Requests
- Migrations
- Factories
- Seeders

---

## 🔹 PWA

Implementar suporte completo a Progressive Web App.

### Recursos obrigatórios

- `manifest.json`
- `service-worker.js`
- cache offline
- suporte à instalação mobile
- splash screens
- ícones para diferentes dispositivos

O sistema deve funcionar:

- no navegador
- como aplicativo instalado
- parcialmente offline

---

## 🔹 Segurança e Performance

Implementar:

- proteção CSRF
- sanitização de dados
- autenticação protegida

Utilizar:

- Laravel Breeze ou Fortify

Aplicar:

- cache
- otimizações de performance
- lazy loading quando necessário

---

## 🔹 Qualidade de Código

O projeto deve seguir:

- Clean Code
- SOLID
- boas práticas de arquitetura
- código escalável
- separação clara de responsabilidades
- padronização de nomenclaturas
- organização modular


# Mais especificações detalhadas

## 9 Frontend — Layout e Padrões (BootstrapVueNext)

---

### 9.1 Componentes Base

Criar componentes reutilizáveis para garantir consistência visual e de UX entre todas as telas do sistema.

Exemplos:
- cadastro de conta
- cadastro de cartão
- categorias
- subcategorias
- limites

---

## Componentes UI

### BaseHeader

```bash
resources/js/components/ui/BaseHeader.vue
```

Responsável por:
- título da página
- subtítulo opcional
- ações do topo
- padrão visual consistente

---

### BottomNav

```bash
resources/js/components/ui/BottomNav.vue
```

Botão redondo com o icone `+` no canto inferior direito um atalho para criar lançamentos.

- Home
- lista
- botão "+"
- gráficos
- perfil

Utilizar:
- bootstrap-icons
- navegação responsiva
- destaque da rota ativa

---

### StatCard

```bash
resources/js/components/ui/StatCard.vue
```

Exibir:
- saldo
- receitas
- despesas
- investido

Layout:
- 2 colunas no mobile e desktop

---

### SectionCard

```bash
resources/js/components/ui/SectionCard.vue
```

Estrutura reutilizável contendo:
- título
- slot de conteúdo

Usado em:
- Minhas contas
- Meus cartões
- Meus limites

---

### LimitBar

```bash
resources/js/components/ui/LimitBar.vue
```

Barra de progresso para limites financeiros.

Regras:
- `< 70%` → verde
- `>= 70%` → amarelo
- `>= 100%` → vermelho

---

### AddTransactionModal

```bash
resources/js/components/modals/AddTransactionModal.vue
```

Modal responsável por exibir opções:

- Receita
- Despesa
- Transferência
- Investimento

Seguir exatamente os prints fornecidos.

---

## Formulários Reutilizáveis

```bash
resources/js/components/forms/AccountForm.vue
resources/js/components/forms/CardForm.vue
```

Regras:
- mesmo padrão visual
- mesma UX
- reutilização máxima

Utilizar:
- `BForm`
- `BFormInput`
- `BFormSelect`
- `BModal`

---

## 9.2 Páginas (Mobile First)

---

## Dashboard

```bash
pages/home/Dashboard.vue
```

### Estrutura

#### Header
- avatar
- nome do usuário
- botão sair (lado direito)

---

### Informações do período
- mês atual
- ano atual
- botão “Expandir”

---

### Grade de StatCards

Exibir:
- Saldo atual
- Receitas
- Despesas
- Pago
- Pendente
- Saldo investido

---

### Seção “Minhas contas”

Exibir apenas contas onde:

```php
somar_tela_inicial = true
```

Mini cards contendo:
- imagem
- nome
- saldo
- saldoInvestido

---

### Seção “Meus cartões”

Cards contendo:
- logo
- gastos do mês
- limite disponível
- fechamento da fatura

Formato:

```txt
Fecha em [data]
```

---

### Seção “Meus limites”

Lista com:
- nome
- percentual utilizado
- `LimitBar`

---

### Bottom Navigation

Menu inferior fixo utilizando `BottomNav.vue`.

---

# Transactions

```bash
pages/transactions/Index.vue
```

---

## Filtros

Criar:
- carrossel de meses
- navegação passado/futuro
- mês atual selecionado por padrão

---

## Chips/Filtros rápidos

- Geral
- Cartões
- Conta
- Tipo saldo:
  - Saldo
  - Investido
  - Simulado

---

## Lista de lançamentos

Ordenação:
- decrescente por data

Exibir:
- badge de parcelamento

Exemplo:

```txt
Parcelado 2/3
```

Utilizar:
- cores por tipo
- diferenciação visual clara

---

## Rodapé Sticky

Exibir:
- Gastos
- Pagamentos
- Balanço total

Deve ser:
- expansível
- fixo no rodapé

---

# Reports — Balance

```bash
pages/reports/Balance.vue
```

---

## Cards Resumo

- Entradas
- Saídas
- Balanço

---

## Gráficos

Instalar:

```bash
npm i vue3-apexcharts
```

Utilizar:
- donut charts para categoria/subcategoria
- barras para:
  - entrada
  - saída
  - investimento

Pode utilizar:
- ApexCharts
- Chart.js

---

# Profile

```bash
pages/profile/Index.vue
```

---

## Informações do usuário

Exibir:
- avatar
- nome

---

## Ações

Botões:
- Editar perfil
- Configurações
- Meus cartões
- Minhas contas
- Minhas categorias
- Minhas subcategorias
- Meus limites
- Sair

---

## Configurações

Permitir:
- dark mode
- moeda:
  - BRL
  - USD

Apenas formatação visual da moeda.

---

## Modal “Editar Perfil”

Seguir layout dos prints.

Campos:
- nome
- telefone
- profissão

Toggles:
- CLT
- casado
- filhos

---

# Cadastros

Todas as listas devem possuir botão:

```txt
+
```

Ao clicar:
- abrir modal
- utilizar formulário reutilizável

---

## Accounts

```bash
pages/profile/Accounts.vue
```

Utilizar:
```bash
AccountForm.vue
```

---

## Cards

```bash
pages/profile/Cards.vue
```

Utilizar:
```bash
CardForm.vue
```

---

## Categories

```bash
pages/profile/Categories.vue
```

Campos:
- nome
- cor
- ícone
- essencial
- tipo

---

## Subcategories

```bash
pages/profile/Subcategories.vue
```

---

## Limits

```bash
pages/profile/Limits.vue
```

---

## 9.3 Design System Custom (BootstrapVueNext)

---

## Padrões

Todos os cadastros devem reutilizar:
- mesmos componentes
- mesmas estruturas
- mesma identidade visual

---

## Formulários

Utilizar:
- `BOffcanvas`
- `BModal`

Padrões:
- inputs arredondados
- foco moderno
- espaçamento consistente

---

## Botões

Ação principal:

```txt
btn-success
```

Cor:
- verde

---

## Ícones

Utilizar:
- `bootstrap-icons`

---

## Dark Mode

Implementar utilizando:

```html
data-bs-theme="dark"
```

O controle deve utilizar a store global já implementada.

---

# 10 Cálculos e Regras — Detalhes Importantes

---

# Saldo Simulado

Não persistir em banco.

Calcular no backend quando:

```php
simulado = true
```

---

## Contas

Cálculo:

```txt
saldo + soma(lançamentos_simulados_conta)
```

Considerar:
- parcelas futuras vigentes

---

## Cartões

Cálculo:

```txt
fatura_total + soma(simulados_cartao)
```

---

## Exposição

Disponibilizar:
- `/dashboard`


---

# Parcelamento

Ao parcelar:
- gerar N lançamentos mensais
- mesmo valor dividido igualmente

---

# Limites

Ao criar despesas:
- verificar categoria vinculada
- somar em:

```txt
valor_gasto_atual
```

---

## Reset Mensal

No dia:

```txt
01
```

Resetar automaticamente:
- valor_gasto_atual

---

# Exclusão de Conta

Antes de excluir:
- solicitar confirmação

Após confirmação:
- excluir lançamentos em cascata

---

# Categorias/Subcategorias

Se desativadas:
- não aparecer em selects

---

# Uploads de Imagens

Salvar:
- caminho no storage
- registro em tabela `imagens`

---

## Tipos

### LOGO
Exibir:
- cards de contas
- cards de cartões

---

### LANCAMENTO

Exibir:
- detalhe do lançamento
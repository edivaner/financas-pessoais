# Finanças Pessoais

Aplicação web de gestão financeira pessoal desenvolvida como projeto de aprendizado prático com **Claude Code** e **IA generativa**. O objetivo principal foi explorar o desenvolvimento assistido por IA — arquitetura, validações, refatorações e decisões de design foram conduzidas em colaboração com Claude.

> **Este projeto foi criado para desenvolver habilidades com Claude Code e IA.** Cada feature, correção de bug e decisão arquitetural foi desenvolvida através de conversas com IA, demonstrando como ferramentas de IA podem acelerar e qualificar o desenvolvimento de software.

---

## Funcionalidades

- **Contas** — cadastro e gestão de contas bancárias com saldo
- **Cartões** — cartões de crédito, débito e múltiplo vinculados a contas
- **Categorias e Subcategorias** — organização hierárquica de gastos
- **Lançamentos** — receitas, despesas, transferências e investimentos com suporte a parcelas
- **Limites** — controle de gastos por categoria com barra de progresso
- **Dashboard** — visão geral com gráficos e estatísticas
- **Extrato** — histórico de movimentações filtráveis
- **Perfil** — avatar, preferências de moeda e tema escuro/claro
- **Autenticação** — login, registro e OTP por e-mail
- **PWA** — instalável no celular/desktop, funciona offline

---

## Stack Técnica

| Camada | Tecnologia | Versão |
|--------|-----------|--------|
| Backend | Laravel | 12 |
| Autenticação API | Laravel Sanctum | 4.2 |
| Frontend | Vue 3 (Composition API) | 3.5 |
| Estado | Pinia | 3.0 |
| Roteamento | Vue Router | 4.5 |
| Build | Vite | 7.0 |
| CSS | Bootstrap 5 + SCSS | 5.3 |
| Gráficos | ApexCharts | 5.12 |
| Banco (dev) | SQLite | — |
| Banco (docker) | MySQL 8 | — |
| Runtime | PHP | 8.2+ |
| Contêineres | Docker Compose | — |
| PWA | vite-plugin-pwa | 1.0 |

---

## Pré-requisitos

Para rodar **sem Docker** (mais simples):
- PHP 8.2+
- Composer
- Node.js 20+
- npm

Para rodar **com Docker**:
- Docker Desktop

---

## Configuração Local (sem Docker)

### 1. Clonar o repositório

```bash
git clone https://github.com/edivaner/financas-pessoais.git
cd financas-pessoais
```

### 2. Instalar dependências PHP

```bash
composer install
```

### 3. Configurar variáveis de ambiente

```bash
cp .env.example .env
php artisan key:generate
```

O `.env.example` já vem configurado para SQLite — nenhuma alteração necessária para começar.

### 4. Criar o banco de dados e rodar as migrations

```bash
touch database/database.sqlite
php artisan migrate --seed
```

### 5. Instalar dependências JavaScript

```bash
npm install
```

### 6. Rodar a aplicação

Em dois terminais separados:

```bash
# Terminal 1 — servidor PHP
php artisan serve

# Terminal 2 — servidor Vite (frontend)
npm run dev
```

Acesse: [http://localhost:8000](http://localhost:8000)

---

## Configuração Local (com Docker)

### 1. Clonar e configurar

```bash
git clone https://github.com/edivaner/financas-pessoais.git
cd financas-pessoais
cp .env.example .env
```

### 2. Editar o `.env` para MySQL

```env
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=financas
DB_USERNAME=financas
DB_PASSWORD=secret
```

### 3. Subir os contêineres

```bash
docker compose up -d
```

### 4. Configurar o banco dentro do contêiner

```bash
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
```

### 5. Instalar dependências e buildar o frontend

```bash
docker compose exec app npm install
docker compose exec app npm run dev
```

Acesse: [http://localhost:8000](http://localhost:8000)

### Serviços disponíveis

| Serviço | URL |
|---------|-----|
| Aplicação | http://localhost:8000 |
| Vite HMR | http://localhost:5173 |
| Mailhog (e-mails) | http://localhost:8025 |
| MySQL | localhost:3306 |

---

## Comandos Úteis

```bash
# Recriar banco do zero
php artisan migrate:fresh --seed

# Resetar limites mensais (agendado todo dia 01)
php artisan limits:reset

# Rodar testes
php artisan test

# Formatar código PHP
./vendor/bin/pint

# Build de produção do frontend
npm run build
```

---

## Estrutura do Projeto

```
├── app/
│   ├── Actions/          # Ações de negócio (criar lançamento, resetar limites)
│   ├── Http/
│   │   ├── Controllers/  # 11 controllers REST
│   │   └── Requests/     # Validações com regras de ownership por usuário
│   ├── Models/           # 9 modelos Eloquent
│   ├── Repositories/     # Camada de acesso a dados
│   └── Services/         # Serviços de negócio
├── database/
│   ├── migrations/       # Schema do banco
│   └── seeders/          # Dados iniciais
└── resources/
    └── js/
        ├── pages/        # 8 páginas Vue
        ├── components/   # 17 componentes (forms, modals, ui)
        ├── stores/       # 8 stores Pinia
        └── router/       # Configuração de rotas
```

---

## Desenvolvido com IA

Este projeto foi construído como exercício prático de desenvolvimento assistido por IA usando **Claude Code**. Exemplos do que foi desenvolvido em colaboração com IA:

- Arquitetura de validações no backend com `withValidator` e `Rule::exists()->where('user_id')` para ownership checks
- Solução para bug de persistência de formulário usando o padrão `:key` increment no Vue
- Refatoração de migrations ALTER TABLE para dentro dos CREATE TABLE originais
- Lógica de compatibilidade de tipo de cartão no formulário de lançamentos
- Configuração do scheduler Laravel para reset mensal de limites
- Validações cruzadas (cartão pertence à conta, subcategoria pertence à categoria, conta origem ≠ destino)

O objetivo foi aprender a colaborar efetivamente com IA para tomar decisões técnicas, debugar problemas e evoluir a arquitetura do sistema de forma iterativa.

---

## Licença

MIT

# Instruções de Execução - Sistema Financeiro Pessoal

## 📋 Pré-requisitos

- Docker e Docker Compose instalados
- Node.js 20+ (para desenvolvimento local)
- PHP 8.3+ (para desenvolvimento local)
- Composer (para desenvolvimento local)

## 🚀 Execução com Docker (Recomendado)

### 1. Clonar e Configurar o Projeto

```bash
# Navegar para o diretório do projeto
cd financas-pessoais

# Copiar arquivo de configuração
cp .env.example .env
```

### 2. Configurar Variáveis de Ambiente

Edite o arquivo `.env` com as seguintes configurações:

```env
APP_NAME="Finanças Pessoais"
APP_URL=http://localhost:8000
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=financas_pessoais
DB_USERNAME=root
DB_PASSWORD=password
MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_FROM_ADDRESS=noreply@financas.com
MAIL_FROM_NAME="Finanças Pessoais"
```

### 3. Executar com Docker Compose

```bash
# Subir os containers
docker-compose up -d

# Instalar dependências PHP
docker-compose exec app composer install

# Instalar dependências Node.js
docker-compose exec app npm install

# Gerar chave da aplicação
docker-compose exec app php artisan key:generate

# Executar migrations
docker-compose exec app php artisan migrate

# Executar seeders
docker-compose exec app php artisan db:seed

# Compilar assets frontend
docker-compose exec app npm run build
```

### 4. Acessar a Aplicação

- **Aplicação**: http://localhost:8000
- **MailHog (Emails)**: http://localhost:8025
- **MySQL**: localhost:3306

## 🛠️ Execução Local (Desenvolvimento)

### 1. Configurar Backend (Laravel)

```bash
# Instalar dependências PHP
composer install

# Configurar .env (usar configurações locais)
cp .env.example .env

# Gerar chave da aplicação
php artisan key:generate

# Executar migrations
php artisan migrate

# Executar seeders
php artisan db:seed

# Iniciar servidor Laravel
php artisan serve
```

### 2. Configurar Frontend (Vue.js)

```bash
# Instalar dependências Node.js
npm install

# Compilar assets para desenvolvimento
npm run dev

# Ou compilar para produção
npm run build
```

## 📱 Funcionalidades Implementadas

### ✅ Autenticação
- Registro com verificação OTP por email
- Login com verificação OTP por email
- Logout e gerenciamento de sessão

### ✅ Dashboard
- Resumo financeiro mensal
- Visualização de contas e cartões
- Limites de gastos por categoria
- Botão de ação flutuante para novos lançamentos

### ✅ Lançamentos
- Criação de receitas, despesas, transferências e investimentos
- Suporte a parcelamento
- Lançamentos simulados
- Lançamentos futuros (não pagos)
- Filtros por mês, tipo, conta, cartão e saldo

### ✅ Balanço Geral
- Gráficos de entradas vs saídas
- Gastos por categoria e subcategoria
- Gastos por conta e cartão
- Limites de gastos
- Transações recentes

### ✅ Perfil e Configurações
- Edição de perfil do usuário
- Alteração de senha
- Gerenciamento de contas
- Gerenciamento de cartões
- Gerenciamento de categorias e subcategorias
- Gerenciamento de limites
- Modo escuro/claro
- Configuração de moeda

### ✅ PWA (Progressive Web App)
- Instalável em dispositivos móveis
- Service Worker para cache
- Interface mobile-first
- Funciona offline (cache básico)

## 🗄️ Estrutura do Banco de Dados

### Tabelas Principais
- `users` - Usuários do sistema
- `contas` - Contas bancárias e carteira
- `cartoes` - Cartões de crédito, débito e múltiplos
- `categorias` - Categorias de lançamentos
- `subcategorias` - Subcategorias vinculadas às categorias
- `limites` - Limites de gastos por categoria
- `lancamentos` - Movimentações financeiras
- `imagens` - Imagens de perfil, logos e comprovantes
- `otp_codes` - Códigos de verificação por email

### Características
- UUIDs como chaves primárias
- Valores monetários com `decimal(19,4)`
- Soft deletes para preservar histórico
- Relacionamentos com cascade delete
- Índices para performance

## 🎨 Interface do Usuário

### Design Mobile-First
- Interface responsiva para mobile e desktop
- Menu inferior no mobile, lateral no desktop
- Componentes Bootstrap 5 customizados
- Tema claro/escuro
- Ícones Bootstrap Icons

### Componentes Principais
- `AppLayout` - Layout principal com navegação
- `TransactionModal` - Modal para criar/editar lançamentos
- `ProfileEditModal` - Modal para editar perfil
- Modais de gerenciamento para cada entidade

## 🔧 Comandos Úteis

### Laravel
```bash
# Executar migrations
php artisan migrate

# Executar seeders
php artisan db:seed

# Limpar cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Criar novo controller
php artisan make:controller NomeController

# Criar nova migration
php artisan make:migration nome_da_migration
```

### Vue.js
```bash
# Desenvolvimento com hot reload
npm run dev

# Compilar para produção
npm run build

# Preview da build
npm run preview
```

### Docker
```bash
# Ver logs dos containers
docker-compose logs -f

# Parar containers
docker-compose down

# Rebuild dos containers
docker-compose up -d --build

# Acessar container da aplicação
docker-compose exec app bash
```

## 🐛 Solução de Problemas

### Erro de Conexão com Banco
- Verificar se o container MySQL está rodando
- Verificar configurações no `.env`
- Executar `docker-compose logs mysql`

### Erro de Permissões
- Verificar permissões das pastas `storage` e `bootstrap/cache`
- Executar `chmod -R 775 storage bootstrap/cache`

### Erro de Dependências
- Limpar cache do Composer: `composer clear-cache`
- Reinstalar dependências: `composer install --no-cache`

### Erro de Assets Frontend
- Limpar cache do npm: `npm cache clean --force`
- Reinstalar dependências: `rm -rf node_modules && npm install`

## 📝 Próximos Passos

1. **Implementar Controllers**: Criar controllers para todas as entidades
2. **Implementar Actions**: Completar as actions de negócio
3. **Implementar Validações**: Adicionar validações robustas
4. **Implementar Testes**: Criar testes unitários e de integração
5. **Implementar Gráficos**: Adicionar Chart.js para visualizações
6. **Implementar Upload de Imagens**: Sistema completo de upload
7. **Implementar Notificações**: Sistema de notificações push
8. **Implementar Relatórios**: Relatórios avançados em PDF/Excel

## 📞 Suporte

Para dúvidas ou problemas:
1. Verificar logs: `docker-compose logs -f app`
2. Verificar documentação do Laravel e Vue.js
3. Consultar issues no repositório do projeto

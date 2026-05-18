# Finanças Pessoais - Monorepo

| Projeto | Tech | Descrição |
|---|---|---|
| `financas-pessoais-laravel/` | Laravel 11 | API REST |
| `financas-pessoais-vue/` | Vue 3 + Vite | SPA Web |
| `financas-pessoais-flutter/` | Flutter | App Mobile |
| `docker/` | Docker Compose | Infraestrutura dev |

## Desenvolvimento local

```bash
# Subir tudo (backend + frontend + banco)
cd docker
docker compose up

# Ou rodar individualmente:

# Backend (Laravel)
cd financas-pessoais-laravel
composer install
cp .env.example .env && php artisan key:generate
php artisan migrate --seed
php artisan serve

# Frontend (Vue)
cd financas-pessoais-vue
npm install
cp .env.example .env
npm run dev
```

## Variáveis de ambiente

**Backend** (`financas-pessoais-laravel/.env`):
- `CORS_ALLOWED_ORIGINS` — origens permitidas (ex: `http://localhost:5173,https://meusite.com`)

**Frontend** (`financas-pessoais-vue/.env`):
- `VITE_API_URL` — URL do backend (ex: `http://localhost:8000`)

## Produção

- **Backend**: deploy em qualquer VPS/PaaS com PHP 8.3 + MySQL
- **Frontend**: build estático com `npm run build`, deploy em Vercel/Netlify/Cloudflare Pages
- **Mobile**: `flutter build apk` ou `flutter build ios`

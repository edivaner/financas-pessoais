#!/bin/bash
# Script de deploy — rodar no servidor após clonar o repositório
set -e

echo "==> Build do frontend Vue..."
docker compose -f docker-compose.prod.yml --profile build run --rm frontend-build

echo "==> Subindo backend + nginx + postgres..."
docker compose -f docker-compose.prod.yml up -d --build

echo "==> Aguardando backend ficar pronto..."
sleep 10

echo "==> Deploy concluído!"
echo "    Acesse: http://$(curl -s ifconfig.me)"

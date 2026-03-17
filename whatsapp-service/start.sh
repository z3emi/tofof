#!/usr/bin/env bash
set -e

cd "$(dirname "$0")"

if [ ! -f .env ]; then
  cp .env.example .env
  echo "Created .env from .env.example. Update WHATSAPP_SERVICE_KEY before running in production."
fi

npm install
pm2 start ecosystem.config.cjs
pm2 save

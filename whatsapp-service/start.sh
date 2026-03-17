#!/usr/bin/env bash
set -e

cd "$(dirname "$0")"

if [ ! -f .env ]; then
  if [ -f .env.example ]; then
    cp .env.example .env
    echo "Created .env from .env.example. Update WHATSAPP_SERVICE_KEY before running in production."
  else
    cat > .env <<'EOF'
PORT=3001
WHATSAPP_SERVICE_KEY=change_this_secret
EOF
    echo "Created default .env. Update WHATSAPP_SERVICE_KEY before running in production."
  fi
fi

npm install
npm run install:chrome
pm2 start ecosystem.config.cjs
pm2 save

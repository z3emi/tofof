#!/usr/bin/env bash
# Tofof WhatsApp Service Reinstallation Script for VPS (Linux/Ubuntu)
set -e

echo "--- 1. Cleaning up existing processes ---"
pm2 delete whatsapp-service || true
pkill -f node || true
pkill -f chrome || true

echo "--- 2. Deleting old caches and modules ---"
rm -rf node_modules
rm -rf package-lock.json
rm -rf .wwebjs_auth
rm -rf .puppeteer_cache
rm -rf /root/.cache/puppeteer

echo "--- 3. Installing system dependencies ---"
apt update
apt install -y libnss3 libatk-bridge2.0-0t64 libxcomposite1 libxdamage1 libxrandr2 libgbm1 libasound2t64 libpangocairo-1.0-0 libcups2t64 libxshmfence1 libatk1.0-0t64 libdrm2 libxkbcommon0 libxext6 libxfixes3 libpango-1.0-0 libcairo2

echo "--- 4. Performing a clean npm install ---"
npm cache clean --force
npm install

echo "--- 5. Installing Chrome for Puppeteer in the local project directory ---"
# This path is enforced by the .puppeteerrc.cjs file in this folder
npx puppeteer browsers install chrome

echo "--- 6. Verifying installation ---"
ls -la .puppeteer_cache || echo "ERROR: .puppeteer_cache folder not found!"

echo "--- 7. Starting the service with PM2 ---"
pm2 start server.js --name "whatsapp-service" --watch

echo "--------------------------------------------------------"
echo "Reinstallation Complete! Please check your admin dashboard."
echo "If you still see errors, run: pm2 logs whatsapp-service"
echo "--------------------------------------------------------"

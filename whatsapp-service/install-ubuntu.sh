#!/usr/bin/env bash
set -e

apt update
apt install -y curl ca-certificates gnupg

mkdir -p /etc/apt/keyrings
curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg

echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_20.x nodistro main" > /etc/apt/sources.list.d/nodesource.list

apt update
apt install -y nodejs chromium
npm install -g pm2

node -v
npm -v
chromium --version || true
pm2 -v

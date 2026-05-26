#!/bin/sh
set -e

if [ ! -f package.json ]; then
  echo "Error: /var/www/frontend/package.json not found."
  exit 1
fi

if [ ! -x node_modules/.bin/vite ]; then
  npm install
fi

exec npm run dev -- --host 0.0.0.0

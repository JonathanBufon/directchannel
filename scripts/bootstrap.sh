#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/.."
if [[ -f backend/artisan ]]; then
  echo 'Laravel existente: sem sobrescrever.'
else
  echo 'Criando Laravel em backend/ (precisa de Docker e internet)...'
  TEMP_LARAVEL="$(mktemp -d)"
  docker run --rm --user "$(id -u):$(id -g)" -v "$TEMP_LARAVEL:/app" -w /app composer:2 create-project laravel/laravel .
  cp -an "$TEMP_LARAVEL/." backend/
  rm -rf "$TEMP_LARAVEL"
fi
if [[ -f mobile/package.json ]]; then
  echo 'Expo existente: sem sobrescrever.'
else
  echo 'Criando Expo + TypeScript em diretório temporário...'
  TEMP_DIR="$(mktemp -d)"
  trap 'rm -rf "$TEMP_DIR"' EXIT
  npx create-expo-app@latest "$TEMP_DIR/mobile" --template blank-typescript
  # Preserva as pastas src/ e o .env.example do esqueleto
  cp -a "$TEMP_DIR/mobile/." mobile/
fi
printf '\nDependências de navegação (rodar dentro de mobile/):\n'
printf 'npm install @react-navigation/native @react-navigation/native-stack @react-navigation/bottom-tabs\n'
printf 'npx expo install react-native-screens react-native-safe-area-context\n'
printf '\nLeia README.md para configurar a API e o banco.\n'

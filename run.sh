#!/bin/bash

# Script para iniciar app.py no Superdomínios
# Usage: bash run.sh

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$PROJECT_ROOT"

echo "Diretório do projeto: $PROJECT_ROOT"
echo "Iniciando app.py..."

python3 app.py
